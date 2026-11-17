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
                                {{ __('resource.'.$location->location_resource->resource->name) }}:
                                {{ $location->location_resource->current_amount }} {{ __('resource.'.$location->location_resource->resource->unit) }}
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

        <!-- Production Progress Section (T0 locations only) -->
        @if($productionStatus)
            <div class="victorian-card">
                <div class="p-6 border-b border-[#c5a059] bg-[#f5e6c8]">
                    <h3 class="text-2xl font-bold text-[#5d3a1a] font-serif">{{ __('location.production') }}</h3>
                </div>
                <div class="p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Production Info -->
                        <div class="p-4 bg-white border border-[#d4b483] rounded">
                            <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('location.production_info') }}</h4>
                            <p class="text-gray-800 mb-2">
                                {{ __('location.current_amount') }}:
                                <strong>{{ $productionStatus['current_amount'] }}</strong>
                                {{ __('resource.' . $location->location_resource->resource->unit) }}
                            </p>
                            <p class="text-gray-800 mb-2">
                                {{ __('location.max_capacity') }}:
                                {{ $productionStatus['max_capacity'] }} {{ __('resource.' . $location->location_resource->resource->unit) }}
                            </p>
                            <p class="text-gray-800">
                                {{ __('location.production_rate') }}:
                                {{ $productionStatus['production_rate_per_hour'] }} {{ __('location.per_hour') }}
                            </p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="p-4 bg-white border border-[#d4b483] rounded">
                            <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('location.production_progress') }}</h4>
                            <div class="mb-2 flex justify-between text-sm text-gray-600">
                                <span>{{ __('location.progress') }}: {{ $productionStatus['progress_percent'] }}%</span>
                                @if($productionStatus['estimated_time_minutes'] > 0)
                                    <span>{{ __('location.estimated_time') }}: {{ $productionStatus['estimated_time_minutes'] }} {{ __('location.minutes') }}</span>
                                @else
                                    <span>{{ __('location.capacity_full') }}</span>
                                @endif
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4 border border-[#d4b483]">
                                <div class="bg-[#c5a059] h-4 rounded-full transition-all duration-300"
                                     style="width: {{ $productionStatus['progress_percent'] }}%"></div>
                            </div>
                            <p class="text-sm text-gray-600 mt-2 italic">
                                {{ __('location.production_description_t0', ['resource' => __('resource.' . $location->location_resource->resource->name)]) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Processing Section (T1 locations only) -->
        @if($processingStatus && $processingStatus['has_processing'])
            <div class="victorian-card">
                <div class="p-6 border-b border-[#c5a059] bg-[#f5e6c8]">
                    <h3 class="text-2xl font-bold text-[#5d3a1a] font-serif">{{ __('location.processing') }}</h3>
                </div>
                <div class="p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Processing Info -->
                        <div class="p-4 bg-white border border-[#d4b483] rounded">
                            <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('location.processing_info') }}</h4>
                            <p class="text-gray-800 mb-2">
                                {{ __('location.input_resource') }}:
                                {{ $processingStatus['input_resource'] ? __('resource.' . $processingStatus['input_resource']->name) : __('location.unknown') }}
                            </p>
                            @if($processingStatus['has_second_input'] && $processingStatus['input_resource_2'])
                                <p class="text-gray-800 mb-2">
                                    {{ __('location.second_input_resource') }}:
                                    {{ __('resource.' . $processingStatus['input_resource_2']->name) }}
                                </p>
                            @endif
                            <p class="text-gray-800 mb-2">
                                {{ __('location.ratio') }}:
                                {{ $processingStatus['ratio_input'] }} {{ __('location.input_resource') }}
                                @if($processingStatus['has_second_input'])
                                    + {{ $processingStatus['ratio_input2'] }} {{ __('location.second_input_resource') }}
                                @endif
                                → {{ $processingStatus['ratio_output'] }} {{ __('resource.' . $location->location_resource->resource->name) }}
                            </p>
                            <p class="text-gray-800 mb-2">
                                {{ __('location.processing_queue') }}:
                                {{ $processingStatus['processing_amount'] }} {{ __('resource.' . $location->location_resource->resource->name) }}
                            </p>
                            <p class="text-gray-800">
                                {{ __('location.production_rate') }}:
                                {{ $processingStatus['production_rate_per_hour'] }} {{ __('location.per_hour') }}
                            </p>
                        </div>

                        <!-- Unload Form -->
                        <div class="p-4 bg-white border border-[#d4b483] rounded">
                            <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('location.unload_resources') }}</h4>
                            @php
                                $hasSecondInput = isset($availableInputs['second']);
                                $canUnload = $availableInputs['available_amount'] > 0;
                                if ($hasSecondInput) {
                                    $canUnload = $canUnload && $availableInputs['second']['available_amount'] > 0;
                                }
                            @endphp
                            @if($canUnload)
                                <p class="text-gray-800 mb-2">
                                    {{ __('location.available_in_wagons') }}:
                                    <strong>{{ $availableInputs['available_amount'] }}</strong>
                                    {{ __('resource.' . $availableInputs['resource_name']) }}
                                </p>
                                @if($hasSecondInput)
                                    <p class="text-gray-800 mb-3">
                                        <strong>{{ $availableInputs['second']['available_amount'] }}</strong>
                                        {{ __('resource.' . $availableInputs['second']['resource_name']) }}
                                        ({{ __('location.second_input_resource') }})
                                    </p>
                                @endif
                                <form method="POST" action="{{ route('locations.unload', $location) }}" class="flex flex-col gap-3">
                                    @csrf
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('location.unload_amount') }} ({{ __('resource.' . $availableInputs['resource_name']) }})
                                        </label>
                                        <input type="number" name="amount" id="amount" min="1" max="{{ $availableInputs['available_amount'] }}" value="1"
                                               class="w-full px-3 py-2 border border-[#d4b483] rounded bg-[#faf3e6] text-[#5d3a1a] font-serif focus:outline-none focus:ring-2 focus:ring-[#c5a059]">
                                    </div>
                                    @if($hasSecondInput)
                                        <p class="text-sm text-gray-600 italic">
                                            {{ __('location.second_input_auto', ['amount' => ':amount', 'resource' => __('resource.' . $availableInputs['second']['resource_name'])]) }}
                                        </p>
                                    @endif
                                    <button type="submit" class="victorian-btn py-2 px-6 rounded text-sm font-serif">
                                        {{ __('location.unload') }}
                                    </button>
                                </form>
                            @else
                                @if(!$hasSecondInput)
                                    <p class="text-gray-600 italic">{{ __('location.no_input_resource_in_wagons') }}</p>
                                @else
                                    <p class="text-gray-600 italic">{{ __('location.no_input_resource_2_in_wagons') }}</p>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    @if($processingStatus['processing_amount'] > 0)
                        <div class="mt-6 p-4 bg-white border border-[#d4b483] rounded">
                            <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('location.production_progress') }}</h4>
                            <div class="mb-2 flex justify-between text-sm text-gray-600">
                                <span>{{ __('location.progress') }}: {{ $processingStatus['progress_percent'] }}%</span>
                                <span>{{ __('location.estimated_time') }}: {{ $processingStatus['estimated_time_minutes'] }} {{ __('location.minutes') }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4 border border-[#d4b483]">
                                <div class="bg-[#c5a059] h-4 rounded-full transition-all duration-300"
                                     style="width: {{ $processingStatus['progress_percent'] }}%"></div>
                            </div>
                            <p class="text-sm text-gray-600 mt-2 italic">
                                {{ __('location.processing_description', ['input' => $processingStatus['ratio_input'], 'output' => $processingStatus['ratio_output'], 'resource' => __('resource.' . $location->location_resource->resource->name)]) }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

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
                                        ({{ __('route.fuel') }} {{ $player->train->locomotive->getFuelCost($outgoingRoute) }},
                                        {{ __('route.time') }} {{ $player->train->locomotive->getTravelTime($outgoingRoute) }}h)
                                    </span>
                            </div>
                            <form method="POST" action="{{ route('city.travel', $outgoingRoute) }}" class="inline">
                                @csrf
                                <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                                    {{ __('route.travel') }}
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
</x-app-layout>
