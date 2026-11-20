<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityRoute;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class RouteController extends Controller
{
    public function index(Request $request): View
    {
        $query = CityRoute::with(['fromCity', 'toCity', 'fromLocation.city', 'toLocation.city']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by name (city or location name)
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('fromCity', function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereHas('toCity', function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereHas('fromLocation', function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereHas('toLocation', function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereHas('fromLocation.city', function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereHas('toLocation.city', function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        // Filter by distance
        if ($request->filled('distance_min')) {
            $query->where('distance_km', '>=', (int) $request->distance_min);
        }
        if ($request->filled('distance_max')) {
            $query->where('distance_km', '<=', (int) $request->distance_max);
        }

        $perPage = $request->get('per_page', 10);
        if (!in_array($perPage, [5, 10, 20, 50, 100])) {
            $perPage = 10;
        }

        $allRoutes = $query->orderBy('id', 'asc')->paginate($perPage);

        // Group bidirectional routes within current page
        $grouped = [];
        $processed = [];
        $pageRoutes = collect($allRoutes->items());

        foreach ($pageRoutes as $route) {
            if (isset($processed[$route->id])) {
                continue;
            }

            $reverse = null;

            if ($route->type === 'city_to_city') {
                $reverse = $pageRoutes->firstWhere(function ($r) use ($route) {
                    return $r->from_city_id === $route->to_city_id
                        && $r->to_city_id === $route->from_city_id
                        && $r->type === 'city_to_city';
                });
            } elseif ($route->type === 'city_to_location') {
                $reverse = $pageRoutes->firstWhere(function ($r) use ($route) {
                    return $r->from_location_id === $route->to_location_id
                        && $r->to_city_id === $route->from_city_id
                        && $r->type === 'location_to_city';
                });
            } elseif ($route->type === 'location_to_city') {
                $reverse = $pageRoutes->firstWhere(function ($r) use ($route) {
                    return $r->from_city_id === $route->to_city_id
                        && $r->to_location_id === $route->from_location_id
                        && $r->type === 'city_to_location';
                });
            }

            if ($reverse) {
                $route->bidirectional = true;
                $route->reverse_route = $reverse;
                $processed[$route->id] = true;
                $processed[$reverse->id] = true;
                $grouped[] = $route;
            } else {
                $route->bidirectional = false;
                $grouped[] = $route;
            }
        }

        $routeTypes = ['city_to_city', 'city_to_location', 'location_to_city'];

        // Wrap grouped results in a paginator that keeps pagination links
        $groupedCollection = collect($grouped);
        $total = $allRoutes->total();

        return view('admin.routes.index', [
            'routes' => $allRoutes->appends($request->except('page')),
            'routeTypes' => $routeTypes,
        ]);
    }

    public function create(): View
    {
        return view('admin.routes.create', [
            'cities' => City::all(),
            'locations' => Location::with('city')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'from_city_id' => 'required|exists:cities,id',
            'to_type' => 'required|in:city,location',
            'to_city_id' => 'required_if:to_type,city|exists:cities,id',
            'to_location_id' => 'required_if:to_type,location|exists:locations,id',
            'distance' => 'required|integer|min:1',
            'is_one_way' => 'boolean',
        ]);

        CityRoute::create([
            'from_city_id' => $request->from_city_id,
            'to_city_id' => $request->to_type === 'city' ? $request->to_city_id : null,
            'to_location_id' => $request->to_type === 'location' ? $request->to_location_id : null,
            'distance' => $request->distance,
            'is_one_way' => $request->has('is_one_way'),
        ]);

        return redirect()->route('admin.routes.index')
            ->with('success', __('admin.route_created'));
    }

    public function destroy(CityRoute $route): RedirectResponse
    {
        $route->delete();
        return redirect()->route('admin.routes.index')
            ->with('success', __('admin.route_deleted'));
    }
}
