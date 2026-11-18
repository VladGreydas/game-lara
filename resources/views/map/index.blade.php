<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-serif text-3xl text-[#e5e5e5]">
                    {{ __('map.title') }}
                </h2>
                <p class="font-serif text-[#eab308] italic">
                    {{ __('map.subtitle') }}
                </p>
            </div>
        </div>
        @if($player)
            <x-player-info />
        @endif
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
        <!-- Player Status & Network Overview Stats -->
        <div class="industrial-card p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                <!-- Current Station / Position -->
                <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded col-span-1 md:col-span-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#eab308] mb-1 font-serif">
                        {{ __('map.current_location') }}
                    </h4>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-[#2d2d2d] border-2 border-[#eab308] flex items-center justify-center text-xl">
                            🚂
                        </div>
                        <div>
                            @if($player && $player->isTraveling())
                                <p class="text-lg font-bold text-[#e5e5e5]">
                                    {{ __('map.traveling') }}
                                </p>
                                <p class="text-xs text-[#a3a3a3]">
                                    {{ $player->travel_finishes_at ? $player->travel_finishes_at->diffForHumans() : '' }}
                                </p>
                            @elseif($player && $player->currentLocation)
                                <p class="text-lg font-bold text-[#e5e5e5]">
                                    {{ $player->currentLocation->name }}
                                </p>
                                <p class="text-xs text-[#a3a3a3]">
                                    {{ __('map.location') }} ({{ $player->currentLocation->city ? $player->currentLocation->city->name : __('location.orphan') }})
                                </p>
                            @elseif($player && $player->city)
                                <p class="text-lg font-bold text-[#e5e5e5]">
                                    {{ $player->city->name }}
                                </p>
                                <p class="text-xs text-[#a3a3a3]">
                                    {{ __('map.city') }} ({{ __('map.level') }} {{ $player->city->level }})
                                </p>
                            @else
                                <p class="text-lg font-bold text-[#a3a3a3]">
                                    -
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Stats: Total Cities & Locations -->
                <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded text-center">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#eab308] mb-1 font-serif">
                        {{ __('map.total_cities') }} / {{ __('map.total_locations') }}
                    </p>
                    <p class="text-2xl font-bold text-[#e5e5e5]">
                        {{ $stats['total_cities'] }} <span class="text-sm font-normal text-[#a3a3a3]">/</span> {{ $stats['total_locations'] }}
                    </p>
                </div>

                <!-- Stats: Surplus Types -->
                <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded text-center">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#eab308] mb-1 font-serif">
                        {{ __('map.discovered_surpluses') }}
                    </p>
                    <p class="text-2xl font-bold text-[#e5e5e5]">
                        {{ $stats['surplus_count'] }}
                    </p>
                </div>

                <!-- Stats: Deficit Types -->
                <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded text-center">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#eab308] mb-1 font-serif">
                        {{ __('map.discovered_deficits') }}
                    </p>
                    <p class="text-2xl font-bold text-[#e5e5e5]">
                        {{ $stats['deficit_count'] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Railway Network Graph -->
        <div class="industrial-card">
            <div class="p-4 border-b border-[#3f3f3f] bg-[#2d2d2d] flex flex-col md:flex-row md:items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-[#e5e5e5] font-serif">
                        {{ __('map.railway_network') }}
                    </h3>
                </div>
                <!-- Map Legend -->
                <div class="flex flex-wrap items-center gap-3 text-xs mt-2 md:mt-0 text-[#e5e5e5]">
                    <span class="inline-flex items-center">
                        <span class="w-3 h-3 rounded-full bg-[#eab308] border border-[#e5e5e5] mr-1"></span>
                        {{ __('map.city_station') }}
                    </span>
                    <span class="inline-flex items-center">
                        <span class="w-3 h-3 rounded bg-[#3f3f3f] border border-[#eab308] mr-1"></span>
                        {{ __('map.resource_location') }}
                    </span>
                    <span class="inline-flex items-center">
                        <span class="w-3 h-3 rounded-full bg-[#166534] border border-green-700 mr-1"></span>
                        {{ __('map.surplus') }}
                    </span>
                    <span class="inline-flex items-center">
                        <span class="w-3 h-3 rounded-full bg-[#991b1b] border border-red-700 mr-1"></span>
                        {{ __('map.deficit') }}
                    </span>
                    <span class="inline-flex items-center">
                        <span class="w-3 h-3 rounded-full bg-yellow-400 border border-yellow-600 mr-1 animate-pulse"></span>
                        {{ __('map.player_position') }}
                    </span>
                </div>
            </div>

            <div class="p-4 bg-[#fbf5e8] overflow-x-auto">
                <div class="min-w-[900px] flex justify-center">
                    <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}" class="w-full max-w-[1100px] h-auto select-none" style="filter: drop-shadow(0 2px 4px rgba(93, 58, 26, 0.1));">
                        <defs>
                            <!-- Decorative background pattern -->
                            <pattern id="gridPattern" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#ecdcc2" stroke-width="0.75"/>
                            </pattern>

                            <!-- Arrowhead markers indicating route direction -->
                            <marker id="arrowhead-city" viewBox="0 0 10 10" refX="8.5" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                                <path d="M 0 0 L 10 5 L 0 10 z" fill="#eab308" opacity="0.9" />
                            </marker>
                            <marker id="arrowhead-location" viewBox="0 0 10 10" refX="8.5" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                <path d="M 0 0 L 10 5 L 0 10 z" fill="#3f3f3f" opacity="0.95" />
                            </marker>
                        </defs>

                        <!-- Background -->
                        <rect width="100%" height="100%" fill="url(#gridPattern)" rx="8" />

                        <!-- Decorative Outer Border -->
                        <rect x="10" y="10" width="{{ $svgWidth - 20 }}" height="{{ $svgHeight - 20 }}" fill="none" stroke="#d4b483" stroke-width="2" rx="6" stroke-dasharray="8 4" />

                        <!-- Graph Edges: plain straight lines connecting nodes, each with an arrowhead showing travel direction -->
                        @foreach($mapLinks as $link)
                            @if(!$link['is_location'])
                                <!-- City-to-city edge -->
                                <path d="{{ $link['path'] }}" fill="none" stroke="#eab308" stroke-width="2.5" stroke-linecap="round" opacity="0.85" marker-end="url(#arrowhead-city)" />
                            @else
                                <!-- City-to-location edge -->
                                <path d="{{ $link['path'] }}" fill="none" stroke="#3f3f3f" stroke-width="1.75" stroke-dasharray="3 3" opacity="0.9" marker-end="url(#arrowhead-location)" />
                            @endif
                        @endforeach

                        <!-- Location Graph Nodes -->
                        @foreach($mapNodes as $cNode)
                            @foreach($cNode['locations'] as $lNode)
                                <g class="cursor-pointer group" onclick="document.getElementById('location-card-{{ $lNode['id'] }}')?.scrollIntoView({behavior: 'smooth'})">
                                    <circle cx="{{ $lNode['x'] }}" cy="{{ $lNode['y'] }}" r="12" fill="{{ $lNode['is_current'] ? '#f59e0b' : '#2d2d2d' }}" stroke="#eab308" stroke-width="2" />
                                    <!-- Location icon symbol -->
                                    <text x="{{ $lNode['x'] }}" y="{{ $lNode['y'] + 4 }}" font-size="10" text-anchor="middle" fill="#e5e5e5" font-weight="bold">🏭</text>

                                    <!-- Location name badge -->
                                    <rect x="{{ $lNode['x'] - 42 }}" y="{{ $lNode['y'] + 15 }}" width="84" height="17" rx="3" fill="#ffffff" stroke="#3f3f3f" stroke-width="1" opacity="0.95" />
                                    <text x="{{ $lNode['x'] }}" y="{{ $lNode['y'] + 26 }}" font-size="8" text-anchor="middle" fill="#e5e5e5" font-weight="bold" font-family="serif">
                                        {{ Str::limit($lNode['name'], 14) }}
                                    </text>

                                    @if($lNode['resource'])
                                        <!-- Resource produced indicator -->
                                        <rect x="{{ $lNode['x'] - 32 }}" y="{{ $lNode['y'] - 20 }}" width="64" height="13" rx="2" fill="#e0f2fe" stroke="#0284c7" stroke-width="1" />
                                        <text x="{{ $lNode['x'] }}" y="{{ $lNode['y'] - 11 }}" font-size="7" text-anchor="middle" fill="#0369a1" font-weight="bold">
                                            {{ __('resource.' . $lNode['resource']->name) }}
                                        </text>
                                    @endif

                                    @if($lNode['is_current'])
                                        <circle cx="{{ $lNode['x'] }}" cy="{{ $lNode['y'] }}" r="18" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-dasharray="3 3">
                                            <animateTransform attributeName="transform" type="rotate" from="0 {{ $lNode['x'] }} {{ $lNode['y'] }}" to="360 {{ $lNode['x'] }} {{ $lNode['y'] }}" dur="10s" repeatCount="indefinite"/>
                                        </circle>
                                    @endif
                                </g>
                            @endforeach
                        @endforeach

                        <!-- City Graph Nodes -->
                        @foreach($mapNodes as $cNode)
                            <g class="cursor-pointer group" onclick="document.getElementById('city-card-{{ $cNode['id'] }}')?.scrollIntoView({behavior: 'smooth'})">
                                <!-- City Station Outer Ring -->
                                <circle cx="{{ $cNode['x'] }}" cy="{{ $cNode['y'] }}" r="20" fill="{{ $cNode['is_current'] ? '#fef3c7' : '#ffffff' }}" stroke="#eab308" stroke-width="3" />
                                <circle cx="{{ $cNode['x'] }}" cy="{{ $cNode['y'] }}" r="14" fill="{{ $cNode['is_current'] ? '#f59e0b' : '#eab308' }}" />

                                <!-- City Icon -->
                                <text x="{{ $cNode['x'] }}" y="{{ $cNode['y'] + 4 }}" font-size="12" text-anchor="middle" fill="#ffffff" font-weight="bold">🏛️</text>

                                <!-- City Name Label Box -->
                                <rect x="{{ $cNode['x'] - 55 }}" y="{{ $cNode['y'] + 23 }}" width="110" height="21" rx="4" fill="#1f1f1f" stroke="#eab308" stroke-width="1.5" />
                                <text x="{{ $cNode['x'] }}" y="{{ $cNode['y'] + 37 }}" font-size="10.5" text-anchor="middle" fill="#e5e5e5" font-weight="bold" font-family="serif">
                                    {{ $cNode['name'] }}
                                </text>

                                <!-- Surplus Resources Badges -->
                                @if($cNode['surplus_resources']->count())
                                    @foreach($cNode['surplus_resources'] as $sIndex => $sRes)
                                        <g transform="translate({{ $cNode['x'] - 45 }}, {{ $cNode['badges_base_y'] + $cNode['surplus_offset'] - 10}})">
                                            <rect width="90" height="15" rx="3" fill="#dcfce7" stroke="#16a34a" stroke-width="1.2" />
                                            <text x="45" y="11" font-size="8" text-anchor="middle" fill="#15803d" font-weight="bold">
                                                ★ {{ __('resource.' . $sRes->resource->name) }} (T{{ $sRes->resource->tier }})
                                            </text>
                                        </g>
                                    @endforeach
                                @endif

                                <!-- Deficit Resources Badges -->
                                @if($cNode['deficit_resources']->count())
                                    @foreach($cNode['deficit_resources'] as $dIndex => $dRes)
                                        <g transform="translate({{ $cNode['x'] - 45 }}, {{ $cNode['badges_base_y'] + $cNode['deficit_offset'] - 10}})">
                                            <rect width="90" height="15" rx="3" fill="#fee2e2" stroke="#dc2626" stroke-width="1.2" />
                                            <text x="45" y="11" font-size="8" text-anchor="middle" fill="#b91c1c" font-weight="bold">
                                                ▼ {{ __('resource.' . $dRes->resource->name) }} (T{{ $dRes->resource->tier }})
                                            </text>
                                        </g>
                                    @endforeach
                                @endif

                                <!-- Player marker halo -->
                                @if($cNode['is_current'])
                                    <circle cx="{{ $cNode['x'] }}" cy="{{ $cNode['y'] }}" r="28" fill="none" stroke="#f59e0b" stroke-width="3" stroke-dasharray="4 4">
                                        <animateTransform attributeName="transform" type="rotate" from="0 {{ $cNode['x'] }} {{ $cNode['y'] }}" to="360 {{ $cNode['x'] }} {{ $cNode['y'] }}" dur="8s" repeatCount="indefinite"/>
                                    </circle>
                                    <!-- Steam train icon above city -->
                                    <g transform="translate({{ $cNode['x'] - 12 }}, {{ $cNode['y'] - 30 }})">
                                        <rect width="24" height="14" rx="3" fill="#f59e0b" stroke="#78350f" stroke-width="1" />
                                        <text x="12" y="11" font-size="9" text-anchor="middle" fill="#78350f" font-weight="bold">🚂</text>
                                    </g>
                                @endif
                            </g>
                        @endforeach
                    </svg>
                </div>
            </div>
        </div>

        <!-- Regional Cities, Surplus Resources & Locations Catalog -->
        <div class="space-y-6">
            <h3 class="text-2xl font-bold text-[#e5e5e5] font-serif border-b-2 border-[#3f3f3f] pb-2">
                {{ __('map.city_station') }} &amp; {{ __('map.production_locations') }}
            </h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($cities as $city)
                    @php
                        $citySurplus = $city->resources->where('is_surplus', true);
                        $cityDeficit = $city->resources->where('is_deficit', true);
                        $isPlayerHere = $player && $player->city_id === $city->id && !$player->current_location_id;
                    @endphp

                    <div id="city-card-{{ $city->id }}" class="industrial-card {{ $isPlayerHere ? 'ring-2 ring-[#f59e0b]' : '' }}">
                        <!-- Card Header -->
                        <div class="p-5 border-b border-[#3f3f3f] bg-[#2d2d2d] flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center space-x-2">
                                <span class="text-2xl">🏛️</span>
                                <div>
                                    <h4 class="text-xl font-bold text-[#e5e5e5] font-serif">
                                        {{ $city->name }}
                                    </h4>
                                    <span class="text-xs text-[#d4d4d4]">
                                        {{ __('map.level') }}: {{ $city->level }}/{{ $city->max_level }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                @if($isPlayerHere)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-200 text-yellow-900 border border-yellow-400">
                                        🚂 {{ __('map.you_are_here') }}
                                    </span>
                                @endif
                                @if($isPlayerHere)
                                    <a href="{{ route('city.show', $city) }}" class="industrial-btn py-1 px-3 rounded text-xs font-serif">
                                        {{ __('map.view_city') }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="p-5 space-y-4">
                            <!-- Services Available -->
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="font-bold text-[#eab308] mr-1">{{ __('map.services') }}:</span>
                                @if($city->has_workshop)
                                    <span class="px-2 py-0.5 bg-[#fef3c7] text-[#92400e] border border-[#d97706] rounded font-medium">🔧 {{ __('map.workshop') }}</span>
                                @endif
                                @if($city->has_shop)
                                    <span class="px-2 py-0.5 bg-[#e0e7ff] text-[#3730a3] border border-[#6366f1] rounded font-medium">🛒 {{ __('map.shop') }}</span>
                                @endif
                                @if($city->has_saloon)
                                    <span class="px-2 py-0.5 bg-[#fce7f3] text-[#9d174d] border border-[#ec4899] rounded font-medium">🍺 {{ __('map.saloon') }}</span>
                                @endif
                                @if(!$city->has_workshop && !$city->has_shop && !$city->has_saloon)
                                    <span class="text-[#a3a3a3] italic">-</span>
                                @endif
                            </div>

                            <!-- Surplus Resources Section -->
                            <div class="p-3 bg-[#1f1f1f] border border-[#d4b483] rounded">
                                <h5 class="text-sm font-bold text-[#e5e5e5] mb-2 font-serif flex items-center">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#166534] mr-2"></span>
                                    {{ __('map.surplus_resources') }}
                                </h5>

                                @if($citySurplus->count())
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($citySurplus as $surplus)
                                            <div class="p-2 bg-[#14532c] border border-green-200 rounded text-xs flex justify-between items-center">
                                                <div>
                                                    <span class="font-bold text-green-900">
                                                        {{ __('resource.' . $surplus->resource->name) }}
                                                    </span>
                                                    <span class="text-[10px] px-1 bg-green-200 text-green-800 rounded ml-1">
                                                        T{{ $surplus->resource->tier }}
                                                    </span>
                                                </div>
                                                <div class="text-right text-[#d4d4d4]">
                                                    <div>{{ $surplus->quantity }} {{ __('resource.' . $surplus->resource->unit) }}</div>
                                                    <div class="text-[10px] text-[#a3a3a3]">${{ $surplus->getCurrentBuyPrice() }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-[#a3a3a3] italic">{{ __('map.no_surplus') }}</p>
                                @endif
                            </div>

                            <!-- Deficit Resources Section -->
                            <div class="p-3 bg-[#1f1f1f] border border-[#d4b483] rounded">
                                <h5 class="text-sm font-bold text-[#e5e5e5] mb-2 font-serif flex items-center">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#991b1b] mr-2"></span>
                                    {{ __('map.deficit_resources') }}
                                </h5>

                                @if($cityDeficit->count())
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($cityDeficit as $deficit)
                                            <div class="p-2 bg-[#7f1d1d] border border-red-200 rounded text-xs flex justify-between items-center">
                                                <div>
                                                    <span class="font-bold text-red-900">
                                                        {{ __('resource.' . $deficit->resource->name) }}
                                                    </span>
                                                    <span class="text-[10px] px-1 bg-red-200 text-red-800 rounded ml-1">
                                                        T{{ $deficit->resource->tier }}
                                                    </span>
                                                </div>
                                                <div class="text-right text-[#d4d4d4]">
                                                    <div>{{ $deficit->quantity }} {{ __('resource.' . $deficit->resource->unit) }}</div>
                                                    <div class="text-[10px] text-[#a3a3a3]">${{ $deficit->getCurrentBuyPrice() }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-[#a3a3a3] italic">{{ __('map.no_deficit') }}</p>
                                @endif
                            </div>

                            <!-- Connected Production Locations Section -->
                            <div class="p-3 bg-[#1f1f1f] border border-[#d4b483] rounded">
                                <h5 class="text-sm font-bold text-[#e5e5e5] mb-2 font-serif flex items-center">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#3f3f3f] mr-2"></span>
                                    {{ __('map.production_locations') }}
                                </h5>

                                @if($city->locations->count())
                                    <div class="space-y-2">
                                        @foreach($city->locations as $location)
                                            @php
                                                $isPlayerAtLoc = $player && $player->current_location_id === $location->id;
                                            @endphp
                                            <div id="location-card-{{ $location->id }}" class="p-2.5 bg-[#fbf5e8] border border-[#d4b483] rounded text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2 {{ $isPlayerAtLoc ? 'ring-1 ring-yellow-500' : '' }}">
                                                <div>
                                                    <div class="font-bold text-[#e5e5e5] flex items-center">
                                                        <span>🏭 {{ $location->name }}</span>
                                                        @if($isPlayerAtLoc)
                                                            <span class="ml-2 px-1.5 py-0.2 bg-yellow-200 text-yellow-900 border border-yellow-400 rounded text-[10px]">
                                                                {{ __('map.you_are_here') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="text-[#a3a3a3] text-[11px] mt-0.5">
                                                        <span class="italic">{{ __('location.types.' . $location->type) }}</span>
                                                        @if($location->location_resource)
                                                            • <span class="font-semibold text-[#e5e5e5]">{{ __('map.produces') }}:</span>
                                                            {{ __('resource.' . $location->location_resource->resource->name) }}
                                                            ({{ $location->location_resource->current_amount }} / {{ $location->location_resource->max_capacity }})
                                                        @endif
                                                    </div>
                                                </div>

                                                @if($isPlayerAtLoc)
                                                    <a href="{{ route('locations.show', $location) }}" class="industrial-btn py-1 px-2.5 rounded text-[11px] text-center shrink-0">
                                                        {{ __('map.view_location') }}
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-[#a3a3a3] italic">{{ __('map.no_locations') }}</p>
                                @endif
                            </div>

                            <!-- Outgoing Railway Routes Section -->
                            <div class="p-3 bg-[#1f1f1f] border border-[#d4b483] rounded">
                                <h5 class="text-sm font-bold text-[#e5e5e5] mb-2 font-serif flex items-center">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#eab308] mr-2"></span>
                                    {{ __('map.connected_routes') }}
                                </h5>

                                @if($city->outgoingRoutes->count())
                                    <ul class="space-y-1.5">
                                        @foreach($city->outgoingRoutes as $route)
                                            @php
                                                $destName = $route->toCity->name ?? $route->toLocation->name ?? '-';
                                                $canTravel = $player && !$player->isTraveling() && $player->city_id === $city->id && !$player->current_location_id;
                                            @endphp
                                            <li class="p-2 bg-[#2d2d2d] border border-[#3f3f3f] rounded text-xs flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-bold text-[#e5e5e5]">→ {{ $destName }}</span>
                                                    <span class="text-[#a3a3a3] text-[11px]">({{ $route->distance_km }} km)</span>
                                                    @if($player && $player->train && $player->train->locomotive)
                                                        <span class="text-[#a3a3a3] text-[10px] italic">
                                                            [{{ __('route.fuel') }} {{ $player->train->locomotive->getFuelCost($route) }},
                                                            {{ $player->train->locomotive->getTravelTime($route) }}h]
                                                        </span>
                                                    @endif
                                                </div>

                                                @if($canTravel)
                                                    <form method="POST" action="{{ route('city.travel', $route) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="industrial-btn py-0.5 px-2 rounded text-[10px]">
                                                            {{ __('map.travel') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-[#a3a3a3] italic">{{ __('map.no_routes') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
