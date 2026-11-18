<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-3xl text-[#e5e5e5]">
            {{ __('player.player') }}
        </h2>
        <p class="font-serif text-[#eab308] italic">"{{ __('common.progress_but_with_dignity') }}"</p>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl space-y-8">
        @if ($player)
            <div class="industrial-card">
                <div class="p-6 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                    <h3 class="text-2xl font-bold text-[#e5e5e5] mb-2 font-serif">
                        {{ __('player.welcome', ['nickname' => $player->nickname]) }}
                    </h3>
                </div>

                <div class="p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Player Info -->
                        <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded">
                            <h4 class="text-xl font-bold text-[#e5e5e5] mb-3 font-serif">{{ __('player.player_info') }}</h4>
                            <ul class="space-y-2 text-[#e5e5e5]">
                                <li><strong>{{ __('player.nickname') }}:</strong> {{ $player->nickname }}</li>
                                <li><strong>{{ __('player.level') }}:</strong> {{ $player->lvl }}</li>
                                <li><strong>{{ __('player.cash') }}:</strong> ${{ $player->money }}</li>
                                <li><strong>{{ __('player.exp') }}:</strong> {{ $player->exp }} / {{ $player->max_exp }}</li>
                                <li><strong>{{ __('player.stamina') }}:</strong> {{ $player->stamina }} / {{ $player->max_stamina }}</li>
                            </ul>
                        </div>

                        <!-- Location & Services -->
                        <div class="p-4 bg-[#1f1f1f] border border-[#d4b483] rounded">
                            <h4 class="text-xl font-bold text-[#e5e5e5] mb-3 font-serif">{{ __('player.current_location') }}</h4>

                            @if ($player->isTraveling())
                                <div class="mb-4 p-4 bg-[#1e3a5f] border border-blue-200 rounded">
                                    <p class="font-bold text-blue-800 mb-2">{{ __('route.on_the_way') }}</p>
                                    <p class="text-[#d4d4d4]">
                                        {{ __('route.from') }} {{ $player->currentCityRoute->fromCity ? $player->currentCityRoute->fromCity->name : $player->currentCityRoute->fromLocation->name }} → {{ $player->currentCityRoute->toCity->name ?? $player->currentCityRoute->toLocation->name }}
                                    </p>
                                    <p class="text-sm text-[#a3a3a3] mt-1">
                                        {{ __('route.arrival') }} {{ $player->travel_finishes_at->format('H:i, M d') }}
                                    </p>
                                </div>
                            @elseif ($player->inCity())
                                <p class="text-[#d4d4d4] mb-2">
                                    {{ __('player.you_are_in') }} <a href="{{route('city.show', $player->city)}}" class="text-[#eab308] underline">{{$player->city->name}}</a>
                                </p>
                            @elseif ($player->currentLocation)
                                <p class="text-[#d4d4d4] mb-2">
                                    {{ __('player.you_are_at') }} <a href="{{route('locations.show', $player->currentLocation)}}" class="text-[#eab308] underline">{{$player->currentLocation->name}}</a>
                                </p>
                                <p class="text-[#a3a3a3] italic">{{ $player->currentLocation->description }}</p>
                            @endif

                            @if (!$player->isTraveling())
                                <div class="mt-4">
                                    <form method="POST" action="{{ route('player.scout', $player) }}">
                                        @csrf
                                        <button type="submit" class="industrial-btn py-2 px-4 rounded text-sm">
                                            {{ __('player.scout_for_resources') }}
                                        </button>
                                    </form>
                                </div>
                            @endif

                            @if($player->city)
                                <div class="mt-4 p-4 bg-[#2d2d2d] border border-[#d4b483] rounded">
                                    <h5 class="font-bold text-[#e5e5e5] mb-2">{{ __('city.city_services') }}</h5>
                                    <ul class="space-y-1 text-sm text-[#d4d4d4]">
                                        @if ($player->city->has_workshop)
                                            <li><a href="{{ route('workshop.index') }}" class="text-[#eab308] underline">{{ __('workshop.go_to_workshop') }}</a> — {{ __('workshop.upgrade_train') }}</li>
                                        @else
                                            <li class="text-[#a3a3a3] italic">{{ __('city.no_workshop') }}</li>
                                        @endif
                                        @if ($player->city->has_shop)
                                            <li><a href="{{ route('shop.index') }}" class="text-[#eab308] underline">{{ __('shop.go_to_shop') }}</a> — {{ __('shop.buy_sell_locomotives') }}</li>
                                        @else
                                            <li class="text-[#a3a3a3] italic">{{ __('city.no_shop') }}</li>
                                        @endif
                                        @if ($player->city->hasSaloon())
                                            <li><a href="{{ route('city.saloon.show', $player->city) }}" class="text-[#eab308] underline">{{ __('tavern.visit_tavern') }}</a> — {{ __('tavern.rest_place') }}</li>
                                        @endif
                                    </ul>
                                </div>
                            @endif

                        </div>
                    </div>

                    <!-- Active Contracts -->
                    @if($player->activeContracts->isNotEmpty())
                        <div class="mt-6 p-4 bg-[#1f1f1f] border border-[#d4b483] rounded">
                            <h4 class="text-xl font-bold text-[#e5e5e5] mb-3 font-serif">📜 {{ __('contract.my_contracts') }}</h4>
                            <div class="space-y-3">
                                @foreach($player->activeContracts as $contract)
                                    <div class="p-3 bg-[#2d2d2d] border border-[#d4b483] rounded flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-[#e5e5e5]">
                                                {{ __('contract.deliver_resource_to', [
                                                    'quantity' => $contract->quantity,
                                                    'resource' => __('resource.' . $contract->resource->name),
                                                    'city' => $contract->destinationCity->name,
                                                ]) }}
                                            </p>
                                            <p class="text-xs text-[#eab308]">{{ __('contract.reward') }}: ${{ $contract->reward }}</p>
                                        </div>

                                        @if($contract->canBeDeliveredBy($player))
                                            <form method="POST" action="{{ route('contracts.deliver', $contract) }}">
                                                @csrf
                                                <button type="submit" class="industrial-btn py-1.5 px-3 rounded text-xs font-serif whitespace-nowrap">
                                                    {{ __('contract.deliver_contract') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-[#a3a3a3] italic whitespace-nowrap">
                                                {{ __('contract.need_resource_here') }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Train Info -->
                    <div class="mt-6 p-4 bg-[#1f1f1f] border border-[#d4b483] rounded">
                        <h4 class="text-xl font-bold text-[#e5e5e5] mb-3 font-serif">{{ __('train.your_train') }}</h4>
                        @if($player->train)
                            <div class="mb-4">
                                <h5 class="text-lg font-bold text-[#e5e5e5]">{{ __('locomotive.locomotive') }}:</h5>
                                <x-locomotive-card :locomotive="$player->train->locomotive" :upgrade="false" :rename="true"/>
                            </div>
                            <div class="mb-4">
                                <h5 class="text-lg font-bold text-[#e5e5e5]">{{ __('wagon.wagons') }}:</h5>
                                @if($player->train->wagons->isNotEmpty())
                                    @foreach($player->train->wagons as $wagon)
                                        <x-wagon-card :wagon="$wagon" :upgrade="false" :rename="true"/>
                                    @endforeach
                                @else
                                    <p class="text-[#a3a3a3] italic">{{ __('wagon.you_have_no_wagons_yet') }}</p>
                                @endif
                            </div>
                        @else
                            <p class="text-[#a3a3a3] italic">{{ __('train.you_dont_have_a_train_yet') }}</p>
                        @endif
                    </div>
                </div>
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
        @else
            <div class="industrial-card">
                <div class="p-6 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                    <h3 class="text-2xl font-bold text-[#e5e5e5] mb-2 font-serif">{{ __('player.create_new_player') }}</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('player.store') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-[#e5e5e5] font-bold mb-2" for="nickname">{{ __('player.nickname') }}</label>
                            <input autocomplete="off" type="text" name="nickname"
                                   class="w-full px-4 py-2 border border-[#d4b483] rounded focus:outline-none focus:ring-2 focus:ring-[#eab308]"
                                   placeholder="{{ __('player.enter_your_nickname_here') }}">
                        </div>
                        <button type="submit" class="industrial-btn py-2 px-4 rounded text-sm">
                            {{ __('player.create_new_player') }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
