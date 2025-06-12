<x-app-layout>
    {{-- Перевіряємо, чи існує об'єкт $player --}}
    <?php /** @var \App\Models\Player $player */?>
    @if ($player)
        <x-slot name="header">
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Welcome, ') . $player->nickname }}
                </h2>
                @if($player->user->is(auth()->user()))
                    <x-dropdown>
                        <x-slot name="trigger">
                            <button>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('player.edit', $player)">
                                {{ __('Edit') }}
                            </x-dropdown-link>
                            <form method="post" action="{{ route('player.destroy', $player) }}">
                                @csrf
                                @method('DELETE')
                                <x-dropdown-link :href="route('player.destroy', $player)" onclick="event.preventDefault();this.closest('form').submit();">
                                    {{ __('Delete') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endif
            </div>
        </x-slot>
        <div class="mt-6 mx-auto max-w-7xl bg-white shadow-sm rounded-lg divide-y">
            {{-- Область для відображення повідомлень сесії --}}
            @if(session('success'))
                <div class="text-green-600 font-semibold mb-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="text-red-600 font-semibold mb-4">{{ session('error') }}</div>
            @endif
            <div class="p-6 flex-col">
                <p class="mt-4 text-lg text-gray-900">Nickname: {{ $player->nickname }}</p>
                <p class="mt-4 text-lg text-gray-900">Level:    {{ $player->lvl }}</p>
                <p class="mt-4 text-lg text-gray-900">Cash:     {{ $player->money }}</p>
                <p class="mt-4 text-lg text-gray-900">EXP:      {{ $player->exp }} / {{ $player->max_exp }}</p>

                @if($player->canLevelUp())
                    <div class="mt-6 shadow-sm rounded-lg divide-y">
                        <form method="POST" action="{{ route('player.levelup', $player) }}">
                            @csrf
                            <x-primary-button class="ml-1 h-6">
                                {{ __('LEVEL UP!') }}
                            </x-primary-button>
                        </form>
                    </div>
                @endif

                <hr class="my-4 border-t">

                {{-- Умовне відображення: статус подорожі АБО поточна локація (місто/інша локація) --}}
                @if ($player->isTraveling())
                    {{-- Секція статусу подорожі --}}
                    @php
                        $route = $player->currentCityRoute;
                        // Переконайтеся, що travel_starts_at та travel_finishes_at встановлені
                        if ($player->travel_starts_at && $player->travel_finishes_at) {
                            $travelDurationSeconds = $player->travel_finishes_at->diffInSeconds($player->travel_starts_at);
                            $timeElapsedSeconds = now()->diffInSeconds($player->travel_starts_at);
                            $timeLeftSeconds = ($travelDurationSeconds - $timeElapsedSeconds) * -1;

                            $progress = ($timeElapsedSeconds / $travelDurationSeconds) * 100;
                            // Обмеження прогресу, щоб не перевищував 100%
                            $progress = min(100, $progress);

                            $timeLeftHours = floor($timeLeftSeconds / 3600);
                            $timeLeftMinutes = floor(($timeLeftSeconds % 3600) / 60);
                            $timeLeftSecondsRemainder = $timeLeftSeconds % 60;
                        } else {
                            echo 'Time not set';
                            $progress = 0;
                            $timeLeftHours = 0;
                            $timeLeftMinutes = 0;
                            $timeLeftSeconds = 0;
                            $timeLeftSecondsRemainder = 0;
                        }
                    @endphp

                    <h3 class="text-xl font-semibold mb-4">Current Status: Traveling</h3>
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
                        <p class="font-bold">On the way!</p>
                        <p>From: <strong>{{ $route->fromCity->name }}</strong> to <strong>{{ $route->toCity->name }}</strong></p>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-2">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                        <p class="text-sm mt-1">Progress: {{ round($progress) }}%</p>
                        @if ($timeLeftSeconds > 0)
                            <p class="text-sm">Time left:
                                @if ($timeLeftHours > 0){{ $timeLeftHours }}h @endif
                                @if ($timeLeftMinutes > 0){{ $timeLeftMinutes }}m @endif
                                {{ $timeLeftSecondsRemainder }}s
                            </p>
                        @else
                            <p class="text-sm">Arriving soon!</p>
                        @endif
                        <p class="text-sm">Estimated arrival: {{ $player->travel_finishes_at ? $player->travel_finishes_at->format('H:i, M d') : 'N/A' }}</p>
                    </div>
                    <p class="mt-4">You will automatically arrive at your destination once the travel time is up.</p>

                @elseif ($player->inCity())
                    {{-- Секція інформації про поточне місто та послуги --}}
                    <h3 class="text-xl font-semibold mb-4">Current Location:</h3>
                    <p class="text-lg font-semibold text-gray-800">You are in: <a href="{{ route('city.show', $player->city) }}" class="text-blue-600 hover:underline">{{ $player->city->name }}</a></p>

                    <div class="mt-4 p-4 bg-gray-50 rounded-lg shadow-inner">
                        <h4 class="font-bold text-lg mb-2">City Services:</h4>
                        @if ($player->city->has_workshop)
                            <p><a href="{{ route('workshop.index') }}" class="text-indigo-600 hover:underline">Go to Workshop</a> - Upgrade your train, wagons, and weapons.</p>
                        @else
                            <p class="text-gray-600">No Workshop in this city.</p>
                        @endif
                        @if ($player->city->has_shop)
                            <p><a href="{{ route('shop.index') }}" class="text-indigo-600 hover:underline">Go to Shop</a> - Buy and sell train parts, weapons, and resources.</p>
                        @else
                            <p class="text-gray-600">No Shop in this city.</p>
                        @endif
                        {{-- Додайте тут посилання на інші міські послуги, якщо вони є --}}
                    </div>

                    {{-- Показ інформації про потяги та вагони --}}
                    <hr class="my-4 border-t">

                    <h3 class="text-xl font-semibold mb-4">Your Train:</h3>
                    @if($player->train)
                        <div class="mb-4">
                            <h4 class="text-lg font-bold">Locomotive:</h4>
                            <x-locomotive-card :locomotive="$player->train->locomotive" :upgrade="false" :rename="true" />
                        </div>

                        <div class="mb-4">
                            <h4 class="text-lg font-bold">Wagons:</h4>
                            @if($player->train->wagons->isNotEmpty())
                                @foreach($player->train->wagons as $wagon)
                                    <x-wagon-card :wagon="$wagon" :upgrade="false" :rename="true" />
                                @endforeach
                            @else
                                <p>You have no wagons yet.</p>
                            @endif
                        </div>
                    @else
                        <p>You don't have a train yet!</p>
                    @endif
                @elseif ($player->currentLocation)
                    {{-- Секція інформації про локацію, якщо гравець не в місті і не в дорозі --}}
                    <h3 class="text-xl font-semibold mb-4">Current Location:</h3>
                    <p class="text-lg font-semibold text-gray-800">You are at: {{ $player->currentLocation->name }}</p>
                    <p>{{ $player->currentLocation->description }}</p>
                    {{-- Додайте тут функціонал для взаємодії з локацією (наприклад, збір ресурсів) --}}

                @else
                    {{-- Якщо гравець не в місті, не в дорозі і не в локації (якийсь проміжний стан) --}}
                    <h3 class="text-xl font-semibold mb-4">Current Status: Undefined Location</h3>
                    <p>Your current location is not clearly defined. Please contact support.</p>
                @endif
            </div>
        </div>
    @else
        <x-slot name="header">
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('My Player') }}
                </h2>

            </div>
        </x-slot>
        <div class="mt-6 shadow-sm rounded-lg divide-y">
            <form method="POST" action="{{route('player.store')}}">
                @csrf
                <input autocomplete="off" type="text" name="nickname" class="ml-10 h-12 w-4/5 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-opacity-50 rounded-md shadow-sm" placeholder="Enter your nickname here...">
                <x-primary-button class="ml-1 h-12">{{ __('Create New Player') }}</x-primary-button>
            </form>
        </div>
    @endif
</x-app-layout>
