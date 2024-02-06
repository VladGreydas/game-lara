<section>
    <div class="flex justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Welcome, ').$player->nickname }}
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
</section>
