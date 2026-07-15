<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Traveling') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-4">On Route to {{ $route->toCity->name }}!</h3>

                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p class="text-lg mb-2">Your train is currently traveling from {{ $route->fromCity->name }} to {{ $route->toCity->name }}.</p>
                    <p class="text-lg mb-4">Time remaining: <strong id="arrival-time" class="font-mono text-indigo-600">Loading...</strong></p>

                    <div class="relative pt-1">
                        <div class="overflow-hidden h-3 mb-4 text-xs flex rounded bg-indigo-200">
                            {{-- Додано transition-all duration-1000 для плавної зміни смуги --}}
                            <div id="travel-progress" style="width:0%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 transition-all duration-1000 ease-linear"></div>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600">You cannot perform most actions while traveling. Please wait for your train to arrive.</p>

                    <a href="{{ route('player.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Беремо ISO рядки прямо з Blade
                const finishesStr = "{{ $player->travel_finishes_at->toISOString() }}";
                // Безпечно копіюємо Carbon об'єкт перед відніманням годин
                const startsStr = "{{ $player->travel_finishes_at->copy()->subHours($route->travel_time)->toISOString() }}";

                const travelFinishesAt = new Date(finishesStr).getTime();
                const travelStartsAt = new Date(startsStr).getTime();
                const totalTravelDurationMs = travelFinishesAt - travelStartsAt;

                const timerElement = document.getElementById('arrival-time');
                const progressElement = document.getElementById('travel-progress');

                function updateTravelStatus() {
                    const now = new Date().getTime();
                    const remainingTimeMs = travelFinishesAt - now;
                    const elapsedTravelDurationMs = now - travelStartsAt;

                    if (remainingTimeMs <= 0) {
                        clearInterval(travelInterval); // Зупиняємо таймер
                        timerElement.innerText = 'Arrived!';
                        progressElement.style.width = '100%';

                        // Затримка 1.5 секунди, щоб консольна команда Laravel встигла відпрацювати
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        return;
                    }

                    // Рахуємо залишок часу
                    const totalSeconds = Math.floor(remainingTimeMs / 1000);
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    timerElement.innerText =
                        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                    // Розрахунок прогресу
                    if (totalTravelDurationMs > 0) {
                        const progress = (elapsedTravelDurationMs / totalTravelDurationMs) * 100;
                        progressElement.style.width = `${Math.min(100, progress)}%`;
                    }
                }

                // Запускаємо один раз одразу, щоб уникнути "Loading..." на першу секунду
                updateTravelStatus();
                // Зберігаємо інтервал у змінну, щоб мати змогу його очистити
                const travelInterval = setInterval(updateTravelStatus, 1000);
            });
        </script>
    @endpush
</x-app-layout>
