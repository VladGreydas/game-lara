<section>
    <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y"> {{-- Player Info --}}
        <div class="p-6 flex-col">
            <p class="mt-4 text-lg text-gray-900">Nickname:     {{ $player->nickname }}</p>
            <p class="mt-4 text-lg text-gray-900">Level:        {{ $player->lvl }}</p>
            <p class="mt-4 text-lg text-gray-900">Cash:         {{ $player->money }}</p>
            <p class="mt-4 text-lg text-gray-900">EXP:          {{ $player->exp }} / {{ $player->max_exp }}</p>
            <p class="mt-4 text-lg text-gray-900">Current Town: {{ $player->town->name }}</p>

            @if( $player->exp >= $player->max_exp )
                <div class="mt-6 shadow-sm rounded-lg divide-y">
                    <form method="POST" action="{{route('player.update', $player)}}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="lvl" value="{{ $player->lvl+1 }}">
                        <input type="hidden" name="exp" value="{{ $player->exp - $player->max_exp }}">
                        <input type="hidden" name="max_exp" value="{{ $player->max_exp * 1.75 }}">
                        <input type="hidden" name="money" value="{{ $player->money+=500 }}">
                        <x-primary-button class="ml-1 h-12">{{ __('LEVEL UP!') }}</x-primary-button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</section>
