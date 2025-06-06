<x-app-layout>
    @if (count($player))
            <?php $player = $player[0]?>
        <x-slot name="header">
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
        </x-slot>
        <div class="mt-6 mx-auto max-w-7xl bg-white shadow-sm rounded-lg divide-y">
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

                <h3 class="text-lg font-semibold text-gray-800">Train Info</h3>

                @php
                    $train = $player->train;
                @endphp

                @if($train && $train->locomotive)
                    <x-locomotive-card :locomotive="$player->train->locomotive" />
                @endif

                @if($train && $train->wagons->count())
                    @foreach($train->wagons as $wagon)
                        <x-wagon-card :wagon="$wagon" />
                    @endforeach
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
