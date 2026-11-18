<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-3xl text-[#e5e5e5]">
            {{ __('route.traveling') }}
        </h2>
        <p class="font-serif text-[#eab308] italic">
            "{{ __('common.progress_but_with_dignity') }}"
        </p>
    </x-slot>

    <div class="mt-6 mx-auto max-w-7xl">
        <div class="industrial-card">
            <div class="p-6 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                <h3 class="text-2xl font-bold text-[#e5e5e5] mb-2 font-serif">
                    {{ __('route.on_route_to', ['city' => $route->toCity->name ?? $route->toLocation->name]) }}
                </h3>
            </div>

            <div class="p-6">
                <p class="text-[#e5e5e5] mb-4">
                    {{ __('route.traveling_from_to', [
                        'from' => $route->fromCity->name ?? $route->fromLocation->name,
                        'to' => $route->toCity->name ?? $route->toLocation->name
                    ]) }}
                </p>

                @if (session('status'))
                    <div class="mb-4 p-4 bg-[#14532c] border border-[#22c55e] rounded text-[#4ade80] font-semibold">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mb-6">
                    <div class="flex justify-between items-end mb-2">
                        <span class="font-serif text-[#e5e5e5]">{{ __('route.time_remaining') }}</span>
                        <strong id="arrival-time" class="font-mono text-[#eab308] text-lg">Loading...</strong>
                    </div>

                    <div class="relative w-full h-10 bg-[#1a1a1a] border border-[#3f3f3f] rounded overflow-hidden">
                        <div id="travel-progress"
                             class="absolute top-0 left-0 h-full bg-gradient-to-r from-[#ca8a04] to-[#facc15] shadow-[0_0_12px_rgba(234,179,8,0.6)] transition-all duration-1000 ease-linear"
                             style="width:0%"></div>
                        <span id="travel-progress-text" class="absolute inset-0 flex items-center justify-center text-sm font-bold text-[#e5e5e5] drop-shadow-[0_1px_2px_rgba(0,0,0,0.8)]">0%</span>
                    </div>
                </div>

                <p class="text-[#d4d4d4] italic mb-6">
                    {{ __('route.travel_restriction') }}
                </p>

                <div class="flex justify-end">
                    <a href="{{ route('player.index') }}"
                       class="industrial-btn py-2 px-4 rounded text-sm">
                        {{ __('common.go_to_dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Travel Event Modal --}}
    @if(isset($activeEvent) && $activeEvent)
    <div id="travel-event-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
        <div class="industrial-card max-w-lg w-full mx-4">
            <div class="p-6 border-b border-[#3f3f3f] bg-[#2d2d2d]">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-2xl font-bold text-[#e5e5e5] font-serif">
                        {{ $activeEvent['title'] }}
                    </h3>
                    @if(isset($unresolvedCount) && $unresolvedCount > 1)
                        <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-[#bf360c] text-white">
                            {{ __('travel_events.events_remaining', ['count' => $unresolvedCount]) }}
                        </span>
                    @endif
                </div>
                <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-[#eab308] text-white">
                    {{ $activeEvent['type'] === 'combat' ? __('travel_events.type_combat') : __('travel_events.type_adventure') }}
                </span>
            </div>

            <div class="p-6">
                <p class="text-[#e5e5e5] mb-6 italic">
                    {{ $activeEvent['description'] }}
                </p>

                @if(session('event_message'))
                    <div class="mb-4 p-4 bg-[#14532c] border border-green-200 rounded text-green-800 font-semibold">
                        {{ session('event_message') }}
                    </div>
                @endif

                <form action="{{ route('travel_event.resolve') }}" method="POST">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $activeEvent['id'] }}">

                    <div class="space-y-3">
                        @foreach($activeEvent['choices'] as $choice)
                            <button type="submit" name="choice" value="{{ $choice['label'] }}"
                                    class="w-full text-left py-3 px-4 rounded border border-[#3f3f3f] bg-[#2d2d2d] hover:bg-[#e0d6b8] transition-colors font-serif text-[#e5e5e5]">
                                {{ $choice['label'] }}
                            </button>
                        @endforeach
                    </div>
                </form>

                <p class="text-[#a3a3a3] text-xs mt-4 italic">
                    {{ __('travel_events.travel_paused') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const finishesStr = "{{ $player->travel_finishes_at->toISOString() }}";
                const startsStr = "{{ $player->travel_starts_at->toISOString() }}";

                const travelFinishesAt = new Date(finishesStr).getTime();
                const travelStartsAt = new Date(startsStr).getTime();
                const totalTravelDurationMs = travelFinishesAt - travelStartsAt;

                const timerElement = document.getElementById('arrival-time');
                const progressElement = document.getElementById('travel-progress');
                const progressTextElement = document.getElementById('travel-progress-text');

                function updateTravelStatus() {
                    const now = new Date().getTime();
                    const remainingTimeMs = travelFinishesAt - now;
                    const elapsedTravelDurationMs = now - travelStartsAt;

                    if (remainingTimeMs <= 0) {
                        clearInterval(travelInterval);
                        timerElement.innerText = '{{ __('route.arrived') }}';
                        progressElement.style.width = '100%';
                        progressTextElement.innerText = '100%';

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
                        const clampedProgress = Math.min(100, Math.max(0, progress));
                        progressElement.style.width = `${clampedProgress}%`;
                        progressTextElement.innerText = `${Math.round(clampedProgress)}%`;
                    }
                }

                updateTravelStatus();
                const travelInterval = setInterval(updateTravelStatus, 1000);
            });
        </script>
    @endpush
</x-app-layout>
