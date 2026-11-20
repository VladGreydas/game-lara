<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-3xl text-[#e5e5e5]">
            {{ __('city.current_city') . ' ' . $city->name }}
        </h2>
        <p class="font-serif text-[#eab308] italic">"{{ __('common.progress_but_with_dignity') }}"</p>
        <x-player-info/>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl space-y-8">
        <!-- Main Header Card -->
        <div class="industrial-card">
            <div class="p-6 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                <h3 class="text-2xl font-bold text-[#e5e5e5] mb-2 font-serif">{{ __('city.welcome_to') . ' ' . $city->name . '!' }}</h3>
                <p class="text-[#d4d4d4] italic">{{ __('locomotive.refuel_locomotive') }}</p>
            </div>

            <div class="p-6">
                <div class="grid md:grid-cols-3 gap-6">
                    <!-- City Level -->
                    <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded">
                        <h4 class="text-xl font-bold text-[#e5e5e5] mb-3 font-serif">{{ __('city.city_level') }}</h4>
                        <p class="text-[#e5e5e5] mb-2">
                            {{ $city->level }} / {{ $city->max_level }}
                        </p>
                        <p class="text-sm text-[#a3a3a3] mb-4">{{ __('city.upgrade_cost') }} {{ $city->getUpgradeCost() }}</p>

                        @if($city->level < $city->max_level)
                            <form action="{{ route('city.upgrade') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="industrial-btn py-2 px-4 rounded text-sm">
                                    {{ __('city.upgrade_city') }}
                                </button>
                            </form>
                        @else
                            <p class="text-[#a3a3a3] mt-2 italic">{{ __('city.city_is_at_max_level') }}</p>
                        @endif
                    </div>

                    <!-- Shop Discount -->
                    @if($city->has_shop)
                    <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded">
                        <h4 class="text-xl font-bold text-[#e5e5e5] mb-3 font-serif">{{ __('city.shop_discount') }}</h4>
                        <p class="text-[#e5e5e5] mb-2">
                            {{ number_format($city->getShopDiscount() * 100, 0) }}%
                        </p>
                        <p class="text-sm text-[#a3a3a3]">{{ __('city.shop_discount_desc') }}</p>
                    </div>
                    @endif

                    <!-- Workshop Discount -->
                    @if($city->has_workshop)
                    <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded">
                        <h4 class="text-xl font-bold text-[#e5e5e5] mb-3 font-serif">{{ __('city.workshop_discount') }}</h4>
                        <p class="text-sm text-[#d4d4d4] mb-1">
                            {{ __('city.repair') }}: {{ number_format($city->getWorkshopDiscount('repair') * 100, 0) }}%
                        </p>
                        <p class="text-sm text-[#d4d4d4] mb-2">
                            {{ __('city.upgrade') }}: {{ number_format($city->getWorkshopDiscount('upgrade') * 100, 0) }}%
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Refuel Card -->
        <div class="industrial-card">
            <div class="p-6 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                <h3 class="text-2xl font-bold text-[#e5e5e5] font-serif">{{ __('locomotive.locomotive_fuel') }}</h3>
            </div>
            <div class="p-6">
                <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded">
                        <h4 class="text-xl font-bold text-[#e5e5e5] mb-3 font-serif">{{ __('locomotive.locomotive_fuel') }}</h4>
                        <p class="text-[#e5e5e5] mb-2">
                            {{ $player->train->locomotive->fuel }} / {{ $player->train->locomotive->max_fuel }}
                        </p>
                        <p class="text-sm text-[#a3a3a3] mb-4">{{ __('locomotive.refueling_cost') . ($player->train->locomotive->max_fuel - $player->train->locomotive->fuel) * $refuelPrice }}</p>

                        @if($player->train->locomotive->fuel < $player->train->locomotive->max_fuel)
                            <form action="{{ route('city.refuel') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="industrial-btn py-2 px-4 rounded text-sm">
                                    {{ __('locomotive.refuel') }}
                                </button>
                            </form>
                        @else
                            <p class="text-[#a3a3a3] mt-2 italic">{{ __('locomotive.fuel_tank_is_full') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Resource Market -->
        <div class="industrial-card">
            <div class="p-6 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                <h3 class="text-2xl font-bold text-[#e5e5e5] font-serif">{{ __('resource.resource_market') }}</h3>
            </div>
            <div class="p-6">
                @if($city->resources->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($city->resources as $resourceEntry)
                            <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-[#e5e5e5] text-lg">{{'T'.$resourceEntry->resource->tier.' '. __('resource.'.$resourceEntry->resource->name) }}</span>
                                    <span class="text-sm font-semibold px-2 py-1 rounded
                                        {{ $resourceEntry->isSurplus() ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $resourceEntry->isDeficit() ? 'bg-red-100 text-red-800' : '' }}
                                        {{ (!$resourceEntry->isSurplus() && !$resourceEntry->isDeficit()) ? 'bg-[#1f1f1f] text-[#e5e5e5]' : '' }}
                                    ">
                                        @if($resourceEntry->isSurplus())
                                            {{ __('common.surplus') }}
                                        @elseif($resourceEntry->isDeficit())
                                            {{ __('common.deficit') }}
                                        @else
                                            {{ __('common.equilibrium') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="text-sm text-[#d4d4d4] space-y-1">
                                    <div>{{ __('common.quantity') }}: {{ $resourceEntry->quantity }} {{ __('resource.'.$resourceEntry->resource->unit) }}</div>
                                    <div>
                                        {{ __('common.buy_price') }}: ${{ $resourceEntry->getCurrentBuyPrice() }}
                                        @if($resourceEntry->getPriceDirection() === 'up')
                                            <span class="text-[#f87171] font-bold" title="{{ __('common.price_up') }}">↑</span>
                                        @elseif($resourceEntry->getPriceDirection() === 'down')
                                            <span class="text-[#4ade80] font-bold" title="{{ __('common.price_down') }}">↓</span>
                                        @else
                                            <span class="text-[#737373]" title="{{ __('common.price_stable') }}">→</span>
                                        @endif
                                    </div>
                                    <div>
                                        {{ __('common.sell_price') }}: ${{ $resourceEntry->getCurrentSellPrice() }}
                                        @if($resourceEntry->getPriceDirection() === 'up')
                                            <span class="text-[#f87171] font-bold" title="{{ __('common.price_up') }}">↑</span>
                                        @elseif($resourceEntry->getPriceDirection() === 'down')
                                            <span class="text-[#4ade80] font-bold" title="{{ __('common.price_down') }}">↓</span>
                                        @else
                                            <span class="text-[#737373]" title="{{ __('common.price_stable') }}">→</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[#a3a3a3] mt-2 italic">{{ __('city.no_resources_available') }}</p>
                @endif
            </div>
        </div>

        <!-- Outgoing Routes -->
        <div class="industrial-card">
            <div class="p-6 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                <h3 class="text-2xl font-bold text-[#e5e5e5] font-serif">{{ __('route.outgoing_routes') }}</h3>
            </div>
            <div class="p-6">
                @if($city->getOutgoingRoutes()->count())
                    <ul class="space-y-4">
                        @foreach($city->getOutgoingRoutes() as $route)
                            <li class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded shadow-sm flex flex-col md:flex-row md:items-center justify-between">
                                <div class="mb-2 md:mb-0">
                                    <span class="font-bold text-[#e5e5e5] text-lg">→ {{ $route->toCity->name ?? $route->toLocation->name }}</span>
                                    <span class="ml-2 text-[#a3a3a3] text-sm italic">({{ __('route.fuel') }} {{ $player->train->locomotive->getFuelCost($route) }}, {{ __('route.time') }} {{ $player->train->locomotive->getTravelTime($route) }}h)</span>
                                </div>
                                <form method="POST" action="{{ route('city.travel', $route) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="industrial-btn py-2 px-4 rounded text-sm">
                                        {{ __('route.travel') }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-[#a3a3a3] mt-2 italic">{{ __('route.no_outgoing_routes') }}</p>
                @endif
            </div>
        </div>

        <!-- Services -->
        <div class="grid md:grid-cols-3 gap-6 mt-8">
            @if ($player->city->has_workshop)
                <div class="industrial-card">
                    <div class="p-4 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                        <h3 class="text-xl font-bold text-[#e5e5e5] font-serif">{{ __('workshop.workshop') }}</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-[#d4d4d4] mb-4">{{ __('workshop.upgrade_train') }}</p>
                        <a href="{{ route('workshop.index') }}" class="industrial-btn inline-block w-full text-center py-2 px-4 rounded text-sm">
                            {{ __('workshop.go_to_workshop') }}
                        </a>
                    </div>
                </div>
            @endif

            @if ($player->city->has_shop)
                <div class="industrial-card">
                    <div class="p-4 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                        <h3 class="text-xl font-bold text-[#e5e5e5] font-serif">{{ __('shop.shop') }}</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-[#d4d4d4] mb-4">{{ __('shop.buy_sell_locomotives') }}</p>
                        <a href="{{ route('shop.index') }}" class="industrial-btn inline-block w-full text-center py-2 px-4 rounded text-sm">
                            {{ __('shop.go_to_shop') }}
                        </a>
                    </div>
                </div>
            @endif

            @if ($player->city->hasSaloon())
                <div class="industrial-card">
                    <div class="p-4 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                        <h3 class="text-xl font-bold text-[#e5e5e5] font-serif">{{ __('tavern.tavern') }}</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-[#d4d4d4] mb-4">{{ __('tavern.rest_place') }}</p>
                        <a href="{{ route('city.saloon.show', $player->city) }}" class="industrial-btn inline-block w-full text-center py-2 px-4 rounded text-sm">
                            {{ __('tavern.visit_tavern') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Session Messages -->
        @if(session('success'))
            <div class="mt-6 p-4 bg-[#14532c] border border-green-200 rounded text-green-800 font-semibold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mt-6 p-4 bg-[#7f1d1d] border border-red-200 rounded text-red-800 font-semibold">
                {{ session('error') }}
            </div>
        @endif
    </div>
</x-app-layout>
