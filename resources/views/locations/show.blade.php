<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-3xl text-[#5d3a1a]">
            {{ $location->name }}
        </h2>
        <p class="font-serif text-[#8b5a2b] italic">
            {{ __('location.type') . ': ' . __('location.types.' . $location->type) }}
        </p>
        <x-player-info/>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl space-y-8">
        <!-- Main Header Card -->
        <div class="victorian-card">
            <div class="p-6 border-b border-[#c5a059] bg-[#f5e6c8]">
                <h3 class="text-2xl font-bold text-[#5d3a1a] mb-2 font-serif">
                    {{ __('location.welcome_to') . ' ' . $location->name . '!' }}
                </h3>
                <p class="text-gray-700 italic">
                    {{ $location->description ?? __('location.no_description') }}
                </p>
            </div>

            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Location Type & City -->
                    <div class="p-4 bg-white border border-[#d4b483] rounded">
                        <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('location.type') }}</h4>
                        <p class="text-gray-800 mb-1">
                            {{ __('location.types.' . $location->type) }}
                        </p>
                        <p class="text-sm text-gray-600">
                            {{ __('location.belonging_city') . ': ' . ($location->city ? $location->city->name : __('location.orphan')) }}
                        </p>
                    </div>

                    <!-- Location Resources -->
                    <div class="p-4 bg-white border border-[#d4b483] rounded">
                        <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('location.resources') }}</h4>
                        @if($location->location_resource)
                            <div class="text-gray-800 mb-2">
                                {{ __('entity.resource.'.$location->location_resource->resource->name) }}:
                                {{ $location->location_resource->current_amount }} {{ __('common.'.$location->location_resource->resource->unit) }}
                            </div>
                            <div class="flex flex-row">
                                @if($location->location_resource->current_amount > 0)
                                    @if($player->train->locomotive->fuel < $player->train->locomotive->max_fuel)
                                        <form method="POST" action="{{ route('locations.refuel', $location) }}" class="mt-4">
                                            @csrf
                                            <button type="submit" class="victorian-btn py-2 px-6 mr-2 rounded text-sm font-serif">
                                                {{ __('location.refuel') }}
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('locations.collect', $location) }}" class="mt-4">
                                        @csrf
                                        <button type="submit" class="victorian-btn py-2 px-6 rounded text-sm font-serif">
                                            {{ __('location.collect') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-600 italic">{{ __('location.no_resources') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Outgoing Routes -->
        <div class="victorian-card">
            <div class="p-6 border-b border-[#c5a059] bg-[#f5e6c8]">
                <h3 class="text-2xl font-bold text-[#5d3a1a] font-serif">{{ __('location.outgoing_routes') }}</h3>
            </div>
            <div class="p-6">
                @php
                    $outgoingRoute = $location->getOutgoingRoute();
                    Debugbar::info($outgoingRoute);
                @endphp

                @if($outgoingRoute)
                    <ul class="space-y-4">
                        <li class="p-4 bg-white border border-[#d4b483] rounded shadow-sm flex flex-col md:flex-row md:items-center justify-between">
                            <div class="mb-2 md:mb-0">
                                    <span class="font-bold text-[#5d3a1a] text-lg">
                                        → {{ $outgoingRoute->toCity->name}}
                                    </span>
                                <span class="ml-2 text-gray-600 text-sm italic">
                                        ({{ __('city.fuel') }} {{ $player->train->locomotive->getFuelCost($outgoingRoute) }},
                                        {{ __('city.time') }} {{ $player->train->locomotive->getTravelTime($outgoingRoute) }}h)
                                    </span>
                            </div>
                            <form method="POST" action="{{ route('city.travel', $outgoingRoute) }}" class="inline">
                                @csrf
                                <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                                    {{ __('city.travel') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                @else
                    <p class="text-gray-600 mt-2 italic">{{ __('location.no_outgoing_routes') }}</p>
                @endif
            </div>
        </div>

        <!-- Session Messages -->
        @if(session('success'))
            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded text-green-800 font-semibold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded text-red-800 font-semibold">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <style>
        .victorian-card {
            background: #fff8e7;
            border: 2px solid #c5a059;
            box-shadow: 4px 4px 12px rgba(93, 58, 26, 0.15);
        }
        .victorian-btn {
            background: #8b5a2b;
            color: #fff8e7;
            border: 2px solid #c5a059;
            transition: all 0.2s ease;
        }
        .victorian-btn:hover {
            background: #6d4a24;
            transform: translateY(-1px);
            box-shadow: 2px 2px 6px rgba(93, 58, 26, 0.2);
        }
    </style>
</x-app-layout>
