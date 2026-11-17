<?php

namespace App\Services;

use App\Models\City;
use App\Models\CityRoute;
use App\Models\Location;
use App\Models\Player;

class MapService
{
    /**
     * Generate map data for SVG rendering.
     *
     * Uses BFS to layout cities in rows based on graph depth from the root city,
     * then positions locations and routes as a node-link graph.
     */
    public function generateMapData(?Player $player): array
    {
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

        $cityMap = $cities->keyBy('id');

        // Build adjacency list from city_to_city routes
        $adjacency = [];
        foreach ($routes->where('type', 'city_to_city') as $route) {
            $adjacency[$route->from_city_id][] = $route->to_city_id;
        }

        // BFS from root city to determine depth levels
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

        // Identify valid locations connected via city_to_location routes
        $validLocationsByCity = [];
        foreach ($routes->where('type', 'city_to_location') as $route) {
            $validLocationsByCity[$route->from_city_id][] = $route->to_location_id;
        }

        // Group reachable cities into rows by BFS depth
        $levels = [];
        foreach ($visitOrder as $cityId) {
            $levels[$cityDepth[$cityId]][] = $cityId;
        }
        ksort($levels);

        // Calculate SVG dimensions
        $svgWidth = 1100;
        $rowHeight = 230;
        $topMargin = 90;
        $bottomMargin = 160;
        $maxDepth = empty($levels) ? 0 : max(array_keys($levels));
        $svgHeight = $topMargin + ($maxDepth * $rowHeight) + $bottomMargin;

        // Build map nodes (cities and their locations)
        $mapNodes = $this->buildMapNodes($cityMap, $levels, $validLocationsByCity, $player, $svgWidth, $svgHeight, $rowHeight, $topMargin);

        // Build map links (routes between nodes)
        $mapLinks = $this->buildMapLinks($routes, $mapNodes);

        // Calculate stats
        $stats = [
            'total_cities' => $cities->count(),
            'total_locations' => $locations->count(),
            'surplus_count' => $cities->flatMap(fn ($c) => $c->resources->where('is_surplus', true))->pluck('resource_id')->unique()->count(),
            'deficit_count' => $cities->flatMap(fn ($c) => $c->resources->where('is_deficit', true))->pluck('resource_id')->unique()->count(),
        ];

        return [
            'cities' => $cities,
            'locations' => $locations,
            'mapNodes' => $mapNodes,
            'mapLinks' => $mapLinks,
            'svgWidth' => $svgWidth,
            'svgHeight' => $svgHeight,
            'stats' => $stats,
        ];
    }

    /**
     * Build map nodes from BFS levels.
     */
    protected function buildMapNodes($cityMap, array $levels, array $validLocationsByCity, ?Player $player, int $svgWidth, int $svgHeight, int $rowHeight, int $topMargin): array
    {
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

                // SVG dimensions for badge positioning relative to city node
                $nodeRadius = 20;
                $nameBoxTop = $nodeRadius + 3; // gap between node edge and name box
                $nameBoxHeight = 21;
                $badgeHeight = 15;
                $badgeGap = 2; // gap between adjacent badges
                $badgeStep = $badgeHeight + $badgeGap; // 17px per badge

                // All badges go below the name box
                $badgesBaseY = $y + $nodeRadius + $nameBoxTop + $nameBoxHeight + $badgeGap;
                // Surplus badges come first, then deficit badges follow after surplus count
                $surplusOffset = 0;
                $deficitOffset = $surplusResources->count() * $badgeStep;

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
                    'badges_base_y' => $badgesBaseY,
                    'surplus_offset' => $surplusOffset,
                    'deficit_offset' => $deficitOffset,
                    'badge_step' => $badgeStep,
                    'locations' => $locationNodes,
                    'is_current' => $player && $player->city_id === $city->id && !$player->current_location_id,
                ];
            }
        }

        return $mapNodes;
    }

    /**
     * Build map links (route edges) between nodes.
     */
    protected function buildMapLinks($routes, array $mapNodes): array
    {
        $mapLinks = [];
        $pairCounts = [];
        $cityNodeRadius = 20;
        $locationNodeRadius = 12;
        $arrowGap = 6;
        $bidirectionalOffset = 5;

        // Count bidirectional pairs
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

        return $mapLinks;
    }
}
