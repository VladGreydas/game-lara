<x-app-layout>
    {{-- Перевіряємо, чи існує об'єкт $player --}}
    <?php /** @var \App\Models\Player $player */ ?>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400"
                                     viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
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
                                <x-dropdown-link :href="route('player.destroy', $player)"
                                                 onclick="event.preventDefault();this.closest('form').submit();">
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
                <div class="text-green-600 font-semibold m-4 pt-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="text-red-600 font-semibold m-4">{{ session('error') }}</div>
            @endif
            <div class="p-6 flex-col">
                <p class="mt-4 text-lg text-gray-900">Nickname: {{ $player->nickname }}</p>
                <p class="mt-4 text-lg text-gray-900">Level: {{ $player->lvl }}</p>
                <p class="mt-4 text-lg text-gray-900">Cash: {{ $player->money }}</p>
                <p class="mt-4 text-lg text-gray-900">EXP: {{ $player->exp }} / {{ $player->max_exp }}</p>

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

                        // Безпечно беремо дати
                        $startsAt = $player->travel_starts_at;
                        $finishesAt = $player->travel_finishes_at;

                        // Розрахунок початкового стану для першого рендеру (запобігає стрибкам при завантаженні)
                        $totalDuration = $startsAt && $finishesAt ? $finishesAt->diffInSeconds($startsAt) : 0;
                        $elapsed = $startsAt ? now()->diffInSeconds($startsAt, false) : 0;
                        $progress = $totalDuration > 0 ? min(100, max(0, ($elapsed / $totalDuration) * 100)) : 0;
                    @endphp

                    <h3 class="text-xl font-semibold mb-4">Current Status: Traveling</h3>
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
                        <p class="font-bold text-blue-800">On the way!</p>
                        <p class="mb-2">From: <strong>{{ $route->fromCity->name }}</strong> to
                            <strong>{{ $route->toCity->name }}</strong></p>

                        {{-- Прогрес-бар з плавним анімованим переходом --}}
                        <div class="w-full bg-blue-200 rounded-full h-3 dark:bg-gray-700 mt-2 overflow-hidden">
                            <div id="dashboard-progress"
                                 class="bg-blue-600 h-3 rounded-full transition-all duration-1000 ease-linear"
                                 style="width: {{ $progress }}%"
                                 data-start="{{ $startsAt ? $startsAt->toISOString() : '' }}"
                                 data-finish="{{ $finishesAt ? $finishesAt->toISOString() : '' }}">
                            </div>
                        </div>
                        <p class="text-sm mt-1">Progress: <span
                                id="dashboard-progress-text">{{ round($progress) }}</span>%</p>

                        <p class="text-sm font-medium mt-1">
                            Time left: <strong id="dashboard-timer"
                                               class="font-mono text-blue-900">Calculating...</strong>
                        </p>

                        <p class="text-xs text-blue-600 mt-2">Estimated
                            arrival: {{ $finishesAt ? $finishesAt->format('H:i, M d') : 'N/A' }}</p>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">You will automatically arrive at your destination once the
                        travel time is up. This page will refresh when you arrive.</p>

                    {{-- Скрипт працює тільки коли гравець подорожує --}}
                    @push('scripts')
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const progressBar = document.getElementById('dashboard-progress');
                                const timerText = document.getElementById('dashboard-timer');
                                const progressText = document.getElementById('dashboard-progress-text');

                                if (!progressBar || !timerText) return;

                                const startsAt = new Date(progressBar.dataset.start).getTime();
                                const finishesAt = new Date(progressBar.dataset.finish).getTime();
                                const totalDuration = finishesAt - startsAt;

                                function updateDashboardTravel() {
                                    const now = new Date().getTime();
                                    const remaining = finishesAt - now;
                                    const elapsed = now - startsAt;

                                    if (remaining <= 0) {
                                        clearInterval(dashboardInterval);
                                        timerText.innerText = 'Arrived!';
                                        progressBar.style.width = '100%';
                                        progressText.innerText = '100';

                                        setTimeout(() => {
                                            window.location.reload();
                                            window.location.reload();
                                        }, 1500);
                                        return;
                                    }

                                    // Розрахунок таймера
                                    const totalSeconds = Math.floor(remaining / 1000);
                                    const hours = Math.floor(totalSeconds / 3600);
                                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                                    const seconds = totalSeconds % 60;

                                    let timeString = '';
                                    if (hours > 0) timeString += `${hours}h `;
                                    if (minutes > 0 || hours > 0) timeString += `${minutes}m `;
                                    timeString += `${seconds}s`;

                                    timerText.innerText = timeString;

                                    // Розрахунок та оновлення прогресу
                                    if (totalDuration > 0) {
                                        const progressPercent = Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
                                        progressBar.style.width = `${progressPercent}%`;
                                        progressText.innerText = Math.round(progressPercent);
                                    }
                                }

                                updateDashboardTravel();
                                const dashboardInterval = setInterval(updateDashboardTravel, 1000);
                            });
                        </script>
                    @endpush
                @elseif ($player->inCity())
                    {{-- Секція інформації про поточне місто та послуги --}}
                    <h3 class="text-xl font-semibold mb-4">Current Location:</h3>
                    <p class="text-lg font-semibold text-gray-800">You are in: <a
                            href="{{ route('city.show', $player->city) }}"
                            class="text-blue-600 hover:underline">{{ $player->city->name }}</a></p>

                    <div class="mt-4 p-4 bg-gray-50 rounded-lg shadow-inner">
                        <h4 class="font-bold text-lg mb-2">City Services:</h4>
                        @if ($player->city->has_workshop)
                            <p><a href="{{ route('workshop.index') }}" class="text-indigo-600 hover:underline">Go to
                                    Workshop</a> - Upgrade your train, wagons, and weapons.</p>
                        @else
                            <p class="text-gray-600">No Workshop in this city.</p>
                        @endif
                        @if ($player->city->has_shop)
                            <p><a href="{{ route('shop.index') }}" class="text-indigo-600 hover:underline">Go to
                                    Shop</a> - Buy and sell train parts, weapons, and resources.</p>
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
                            <x-locomotive-card :locomotive="$player->train->locomotive" :upgrade="false"
                                               :rename="true"/>
                        </div>

                        <div class="mb-4">
                            <h4 class="text-lg font-bold">Wagons:</h4>
                            @if($player->train->wagons->isNotEmpty())
                                @foreach($player->train->wagons as $wagon)
                                    <x-wagon-card :wagon="$wagon" :upgrade="false" :rename="true"/>
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
                @elseif ($player->hasArrived())
                    @php $player->processArrival() @endphp
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
                <input autocomplete="off" type="text" name="nickname"
                       class="ml-10 h-12 w-4/5 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-opacity-50 rounded-md shadow-sm"
                       placeholder="Enter your nickname here...">
                <x-primary-button class="ml-1 h-12">{{ __('Create New Player') }}</x-primary-button>
            </form>
        </div>
    @endif
</x-app-layout>
