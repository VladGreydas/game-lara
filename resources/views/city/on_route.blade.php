<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-3xl text-[#5d3a1a]">
            {{ __('Traveling') }}
        </h2>
        <p class="font-serif text-[#8b5a2b] italic">
            "{{ __('city.progress_but_with_dignity') }}"
        </p>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl">
        <div class="victorian-card">
            <div class="p-6 border-b border-[#c5a059] bg-[#f5e6c8]">
                <h3 class="text-2xl font-bold text-[#5d3a1a] mb-2 font-serif">
                    {{ __('On Route to :city', ['city' => $route->toCity->name]) }}
                </h3>
            </div>

            <div class="p-6">
                <p class="text-gray-800 mb-4">
                    {{ __('Your train is currently traveling from :from to :to.', [
                        'from' => $route->fromCity->name,
                        'to' => $route->toCity->name
                    ]) }}
                </p>

                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded text-green-800 font-semibold">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mb-6">
                    <div class="flex justify-between items-end mb-2">
                        <span class="font-serif text-[#5d3a1a]">{{ __('Time remaining:') }}</span>
                        <strong id="arrival-time" class="font-mono text-[#bf360c] text-lg">Loading...</strong>
                    </div>

                    <div class="relative w-full h-6 bg-[#e0d6b8] border border-[#c5a059] rounded overflow-hidden shadow-inner">
                        <div id="travel-progress"
                             class="absolute top-0 left-0 h-full bg-[#8b5a2b] border-r border-[#6d4a24] transition-all duration-1000 ease-linear"
                             style="width:0%"></div>
                    </div>
                </div>

                <p class="text-gray-700 italic mb-6">
                    {{ __('You cannot perform most actions while traveling. Please wait for your train to arrive.') }}
                </p>

                <div class="flex justify-end">
                    <a href="{{ route('player.index') }}"
                       class="victorian-btn py-2 px-4 rounded text-sm">
                        {{ __('Go to Dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const finishesStr = "{{ $player->travel_finishes_at->toISOString() }}";
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
                        clearInterval(travelInterval);
                        timerElement.innerText = '{{ __('Arrived!') }}';
                        progressElement.style.width = '100%';

                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        return;
                    }

                    const totalSeconds = Math.floor(remainingTimeMs / 1000);
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    timerElement.innerText =
                        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                    if (totalTravelDurationMs > 0) {
                        const progress = (elapsedTravelDurationMs / totalTravelDurationMs) * 100;
                        progressElement.style.width = `${Math.min(100, progress)}%`;
                    }
                }

                updateTravelStatus();
                const travelInterval = setInterval(updateTravelStatus, 1000);
            });
        </script>
    @endpush
</x-app-layout>
