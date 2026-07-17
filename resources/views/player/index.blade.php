<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-3xl text-[#5d3a1a]">
            {{ __('My Player') }}
        </h2>
        <p class="font-serif text-[#8b5a2b] italic">"{{ __('city.progress_but_with_dignity') }}"</p>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl space-y-8">
        @if ($player)
            <div class="victorian-card">
                <div class="p-6 border-b border-[#c5a059] bg-[#f5e6c8]">
                    <h3 class="text-2xl font-bold text-[#5d3a1a] mb-2 font-serif">
                        {{ __('Welcome, ') . $player->nickname }}
                    </h3>
                </div>

                <div class="p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Player Info -->
                        <div class="p-4 bg-white border border-[#d4b483] rounded">
                            <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('city.player_info') }}</h4>
                            <ul class="space-y-2 text-gray-800">
                                <li><strong>{{ __('city.nickname') }}:</strong> {{ $player->nickname }}</li>
                                <li><strong>{{ __('city.level') }}:</strong> {{ $player->lvl }}</li>
                                <li><strong>{{ __('city.cash') }}:</strong> ${{ $player->money }}</li>
                                <li><strong>{{ __('city.exp') }}:</strong> {{ $player->exp }} / {{ $player->max_exp }}</li>
                            </ul>

                            @if($player->canLevelUp())
                                <div class="mt-6">
                                    <form method="POST" action="{{ route('player.levelup', $player) }}">
                                        @csrf
                                        <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                                            {{ __('city.level_up') }}
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <!-- Location & Services -->
                        <div class="p-4 bg-white border border-[#d4b483] rounded">
                            <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('city.current_location') }}</h4>

                            @if ($player->isTraveling())
                                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded">
                                    <p class="font-bold text-blue-800 mb-2">{{ __('city.on_the_way') }}</p>
                                    <p class="text-gray-700">
                                        {{ __('city.from') }} {{ $player->currentCityRoute->fromCity->name }} → {{ $player->currentCityRoute->toCity->name }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ __('city.arrival') }} {{ $player->travel_finishes_at->format('H:i, M d') }}
                                    </p>
                                </div>
                            @elseif ($player->inCity())
                                <p class="text-gray-700 mb-2">
                                    {{ __('city.you_are_in') }} <a href="{{route('city.show', $player->city)}}" class="text-[#8b5a2b] underline">{{$player->city->name}}</a>
                                </p>
                            @elseif ($player->currentLocation)
                                <p class="text-gray-700 mb-2">
                                    {{ __('city.you_are_at') . ' ' . $player->currentLocation->name }}
                                </p>
                                <p class="text-gray-600 italic">{{ $player->currentLocation->description }}</p>
                            @endif

                            <div class="mt-4 p-4 bg-[#f5e6c8] border border-[#d4b483] rounded">
                                <h5 class="font-bold text-[#5d3a1a] mb-2">{{ __('city.city_services') }}</h5>
                                <ul class="space-y-1 text-sm text-gray-700">
                                    @if ($player->city->has_workshop)
                                        <li><a href="{{ route('workshop.index') }}" class="text-[#8b5a2b] underline">{{ __('city.go_to_workshop') }}</a> — {{ __('city.upgrade_train') }}</li>
                                    @else
                                        <li class="text-gray-500 italic">{{ __('city.no_workshop') }}</li>
                                    @endif
                                    @if ($player->city->has_shop)
                                        <li><a href="{{ route('shop.index') }}" class="text-[#8b5a2b] underline">{{ __('city.go_to_shop') }}</a> — {{ __('city.buy_sell_locomotives') }}</li>
                                    @else
                                        <li class="text-gray-500 italic">{{ __('city.no_shop') }}</li>
                                    @endif
                                    @if ($player->city->hasSaloon())
                                        <li><a href="{{ route('city.saloon.show', $player->city) }}" class="text-[#8b5a2b] underline">{{ __('city.visit_tavern') }}</a> — {{ __('city.rest_place') }}</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Train Info -->
                    <div class="mt-6 p-4 bg-white border border-[#d4b483] rounded">
                        <h4 class="text-xl font-bold text-[#5d3a1a] mb-3 font-serif">{{ __('city.your_train') }}</h4>
                        @if($player->train)
                            <div class="mb-4">
                                <h5 class="text-lg font-bold text-[#5d3a1a]">{{ __('city.locomotive') }}:</h5>
                                <x-locomotive-card :locomotive="$player->train->locomotive" :upgrade="false" :rename="true"/>
                            </div>
                            <div class="mb-4">
                                <h5 class="text-lg font-bold text-[#5d3a1a]">{{ __('city.wagons') }}:</h5>
                                @if($player->train->wagons->isNotEmpty())
                                    @foreach($player->train->wagons as $wagon)
                                        <x-wagon-card :wagon="$wagon" :upgrade="false" :rename="true"/>
                                    @endforeach
                                @else
                                    <p class="text-gray-600 italic">{{ __('city.you_have_no_wagons_yet') }}</p>
                                @endif
                            </div>
                            <div class="mb-4">
                                <h5 class="text-lg font-bold text-[#5d3a1a]">{{ __('Weapons') }}:</h5>
                                @php
                                    $allWeapons = collect();
                                    foreach($player->train->wagons as $wagon) {
                                        if($wagon->isWeapon()) {
                                            $allWeapons->push($wagon);
                                        } else {
                                            $allWeapons = $allWeapons->merge($wagon->weaponWagon->weapons ?? collect());
                                        }
                                    }
                                @endphp
                                @if($allWeapons->isNotEmpty())
                                    @foreach($allWeapons as $weapon)
                                        <x-weapon-card :weapon="$weapon" :upgrade="false" :rename="true"/>
                                    @endforeach
                                @else
                                    <p class="text-gray-600 italic">{{ __('You have no weapons yet.') }}</p>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-600 italic">{{ __('city.you_dont_have_a_train_yet') }}</p>
                        </endif
                    </div>
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
        @else
            <div class="victorian-card">
                <div class="p-6 border-b border-[#c5a059] bg-[#f5e6c8]">
                    <h3 class="text-2xl font-bold text-[#5d3a1a] mb-2 font-serif">{{ __('city.create_new_player') }}</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('player.store') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-[#5d3a1a] font-bold mb-2" for="nickname">{{ __('city.nickname') }}</label>
                            <input autocomplete="off" type="text" name="nickname"
                                   class="w-full px-4 py-2 border border-[#d4b483] rounded focus:outline-none focus:ring-2 focus:ring-[#8b5a2b]"
                                   placeholder="{{ __('city.enter_your_nickname_here') }}">
                        </div>
                        <button type="submit" class="victorian-btn py-2 px-4 rounded text-sm">
                            {{ __('city.create_new_player') }}
                        </button>
                    </form>
                </div>
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
