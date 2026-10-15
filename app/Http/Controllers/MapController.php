<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CityRoute;
use App\Models\Location;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MapController extends Controller
{
    public function index(Request $request)
    {
        /** @var Player|null $player */
        $player = Auth::user()?->player;

        if ($player) {
            $player->load(['city', 'currentLocation', 'train.locomotive']);
        }

        $cities = City::with([
            'resources.resource',
            'locations.location_resource.resource',
            'outgoingRoutes.toCity',
            'outgoingRoutes.toLocation',
        ])->get();

        $locations = Location::with([
            'city',
            'location_resource.resource',
        ])->get();

        $routes = CityRoute::with([
            'fromCity',
            'toCity',
            'fromLocation',
            'toLocation',
        ])->get();

        // Discover which cities are actually reachable from the root through
        // real city_to_city routes using a BFS walk of the railway graph.
        // The resulting depth per city groups cities into horizontal rows,
        // so the map renders as a plain node-link graph: nodes laid out on
        // a grid of rows/columns and connected with straight edges, which
        // correctly represents any network shape (chain, loop, branching)
        // without ever collapsing into a single straight line.
        $cityMap = $cities->keyBy('id');
        $adjacency = [];
        foreach ($routes->where('type', 'city_to_city') as $route) {
            $adjacency[$route->from_city_id][] = $route->to_city_id;
        }

        // Determine root city (city 1 or lowest ID)
        $rootCityId = $cities->first()?->id ?? 1;

        $visited = [$rootCityId => true];
        $cityDepth = [$rootCityId => 0];
        $visitOrder = [$rootCityId];
        $queue = [$rootCityId];

        while (!empty($queue)) {
            $currentId = array_shift($queue);
            $depth = $cityDepth[$currentId];

            foreach ($adjacency[$currentId] ?? [] as $neighborId) {
                if (!isset($visited[$neighborId]) && $cityMap->has($neighborId)) {
                    $visited[$neighborId] = true;
                    $cityDepth[$neighborId] = $depth + 1;
                    $visitOrder[] = $neighborId;
                    $queue[] = $neighborId;
                }
            }
        }

        // Cities not reachable from the root via an actual city_to_city route
        // are intentionally left out of the map: we must never draw a
        // connection (or imply one through layout) for a route that doesn't exist.

        // Only locations that have an actual city_to_location route should be
        // placed on the map, otherwise we would imply a non-existent route.
        $validLocationsByCity = [];
        foreach ($routes->where('type', 'city_to_location') as $route) {
            $validLocationsByCity[$route->from_city_id][] = $route->to_location_id;
        }

        // Group reachable cities into rows by their BFS depth (graph "level").
        $levels = [];
        foreach ($visitOrder as $cityId) {
            $levels[$cityDepth[$cityId]][] = $cityId;
        }
        ksort($levels);

        $svgWidth = 1100;
        $rowHeight = 230;
        $topMargin = 90;
        $bottomMargin = 160;
        $maxDepth = empty($levels) ? 0 : max(array_keys($levels));
        $svgHeight = $topMargin + ($maxDepth * $rowHeight) + $bottomMargin;

        $mapNodes = [];

        foreach ($levels as $depth => $cityIds) {
            $count = count($cityIds);
            $y = $topMargin + ($depth * $rowHeight);

            foreach ($cityIds as $index => $cityId) {
                $city = $cityMap->get($cityId);
                if (!$city) {
                    continue;
                }

                $x = $count > 1
                    ? (int) round(90 + ($index * (($svgWidth - 180) / ($count - 1))))
                    : (int) round($svgWidth / 2);

                $surplusResources = $city->resources->where('is_surplus', true);
                $deficitResources = $city->resources->where('is_deficit', true);
                $validLocationIds = $validLocationsByCity[$city->id] ?? [];
                $cityLocations = $city->locations->filter(fn ($loc) => in_array($loc->id, $validLocationIds))->values();
                $locationNodes = [];

                $locCount = $cityLocations->count();
                // Position location nodes to the side of the city node rather
                // than directly below it, so they never sit on top of the
                // vertical city-to-city route lines connecting rows. Cities
                // on the left half of the canvas push their locations to the
                // right, and vice versa, keeping the trunk routes clear.
                $locSide = $x > ($svgWidth / 2) ? -1 : 1;
                $locBaseOffsetX = 100;
                foreach ($cityLocations as $locIndex => $loc) {
                    $locOffsetY = $locCount > 1 ? (($locIndex - (($locCount - 1) / 2)) * 55) : 0;
                    $locX = round($x + ($locSide * $locBaseOffsetX));
                    $locY = round($y + 35 + $locOffsetY);

                    $locationNodes[$loc->id] = [
                        'id' => $loc->id,
                        'name' => $loc->name,
                        'slug' => $loc->slug,
                        'type' => $loc->type,
                        'x' => max(35, min($svgWidth - 35, $locX)),
                        'y' => max(25, min($svgHeight - 25, $locY)),
                        'resource' => $loc->location_resource?->resource,
                        'amount' => $loc->location_resource?->current_amount ?? 0,
                        'is_current' => $player && $player->current_location_id === $loc->id,
                    ];
                }

                $mapNodes[$city->id] = [
                    'id' => $city->id,
                    'name' => $city->name,
                    'slug' => $city->slug,
                    'level' => $city->level,
                    'x' => $x,
                    'y' => $y,
                    'has_workshop' => $city->has_workshop,
                    'has_shop' => $city->has_shop,
                    'has_saloon' => $city->has_saloon,
                    'surplus_resources' => $surplusResources,
                    'deficit_resources' => $deficitResources,
                    'locations' => $locationNodes,
                    'is_current' => $player && $player->city_id === $city->id && !$player->current_location_id,
                ];
            }
        }

        // Map route links. Both city-to-city routes and city-to-location
        // spurs are drawn as plain straight edges, so the map reads as a
        // classic node-link graph rather than a stylised railway diagram.
        // Each edge ends with an arrowhead marker so the direction of travel
        // is always visible, and two-way pairs are offset slightly apart so
        // both arrows (one per direction) remain distinguishable.
        $mapLinks = [];
        $pairCounts = [];
        $cityNodeRadius = 20;
        $locationNodeRadius = 12;
        $arrowGap = 6; // extra clearance so the arrowhead doesn't dip into the node circle
        $bidirectionalOffset = 5; // perpendicular shift applied to each direction of a two-way route

        foreach ($routes as $route) {
            if ($route->isCityToCity()) {
                $pairKey = min($route->from_city_id, $route->to_city_id) . '-' . max($route->from_city_id, $route->to_city_id);
                $pairCounts[$pairKey] = ($pairCounts[$pairKey] ?? 0) + 1;
            }
        }

        foreach ($routes as $route) {
            $fromX = null;
            $fromY = null;
            $toX = null;
            $toY = null;
            $isLocationRoute = false;
            $targetRadius = $cityNodeRadius;

            if ($route->isCityToCity()) {
                if (isset($mapNodes[$route->from_city_id]) && isset($mapNodes[$route->to_city_id])) {
                    $fromX = $mapNodes[$route->from_city_id]['x'];
                    $fromY = $mapNodes[$route->from_city_id]['y'];
                    $toX = $mapNodes[$route->to_city_id]['x'];
                    $toY = $mapNodes[$route->to_city_id]['y'];

                    $pairKey = min($route->from_city_id, $route->to_city_id) . '-' . max($route->from_city_id, $route->to_city_id);
                    $isBidirectional = ($pairCounts[$pairKey] ?? 0) > 1;

                    if ($isBidirectional) {
                        // Shift this direction's line perpendicular to the
                        // travel vector so both directions of a two-way
                        // route sit side by side instead of overlapping.
                        $dx = $toX - $fromX;
                        $dy = $toY - $fromY;
                        $len = sqrt(($dx * $dx) + ($dy * $dy)) ?: 1;
                        $perpX = -$dy / $len;
                        $perpY = $dx / $len;
                        $sign = $route->from_city_id < $route->to_city_id ? 1 : -1;

                        $fromX += $perpX * $bidirectionalOffset * $sign;
                        $fromY += $perpY * $bidirectionalOffset * $sign;
                        $toX += $perpX * $bidirectionalOffset * $sign;
                        $toY += $perpY * $bidirectionalOffset * $sign;
                    }
                }
            } elseif ($route->isCityToLocation()) {
                if (isset($mapNodes[$route->from_city_id]) && isset($mapNodes[$route->from_city_id]['locations'][$route->to_location_id])) {
                    $fromX = $mapNodes[$route->from_city_id]['x'];
                    $fromY = $mapNodes[$route->from_city_id]['y'];
                    $toX = $mapNodes[$route->from_city_id]['locations'][$route->to_location_id]['x'];
                    $toY = $mapNodes[$route->from_city_id]['locations'][$route->to_location_id]['y'];
                    $isLocationRoute = true;
                    $targetRadius = $locationNodeRadius;
                }
            }

            if ($fromX !== null && $fromY !== null && $toX !== null && $toY !== null) {
                // Trim the line's end point back from the target node's
                // center so the arrowhead marker sits just outside the node
                // circle instead of being drawn underneath it.
                $dx = $toX - $fromX;
                $dy = $toY - $fromY;
                $len = sqrt(($dx * $dx) + ($dy * $dy));
                if ($len > 0) {
                    $trim = $targetRadius + $arrowGap;
                    $ratio = max(0, ($len - $trim) / $len);
                    $trimmedToX = $fromX + ($dx * $ratio);
                    $trimmedToY = $fromY + ($dy * $ratio);
                } else {
                    $trimmedToX = $toX;
                    $trimmedToY = $toY;
                }

                // Plain straight edge for both city-to-city routes and
                // city-to-location spurs, matching a classic node-link graph.
                $pathD = sprintf('M %.2f %.2f L %.2f %.2f', $fromX, $fromY, $trimmedToX, $trimmedToY);

                $mapLinks[] = [
                    'id' => $route->id,
                    'type' => $route->type,
                    'from_x' => $fromX,
                    'from_y' => $fromY,
                    'to_x' => $toX,
                    'to_y' => $toY,
                    'path' => $pathD,
                    'is_location' => $isLocationRoute,
                    'distance_km' => $route->distance_km,
                ];
            }
        }

        $stats = [
            'total_cities' => $cities->count(),
            'total_locations' => $locations->count(),
            'surplus_count' => $cities->flatMap(fn($c) => $c->resources->where('is_surplus', true))->pluck('resource_id')->unique()->count(),
            'deficit_count' => $cities->flatMap(fn($c) => $c->resources->where('is_deficit', true))->pluck('resource_id')->unique()->count(),
        ];

        return view('map.index', compact('cities', 'locations', 'player', 'mapNodes', 'mapLinks', 'svgWidth', 'svgHeight', 'stats'));
    }
}
