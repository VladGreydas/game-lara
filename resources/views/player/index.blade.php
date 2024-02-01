<x-app-layout>
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    @if (count($player))
        <?php $player = $player[0];?>
        <?php $train = $player->train;?>
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
        <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y">
            <div class="p-6 flex-col">
                <p class="mt-4 text-lg text-gray-900">Nickname: {{ $player->nickname }}</p>
                <p class="mt-4 text-lg text-gray-900">Level:    {{ $player->lvl }}</p>
                <p class="mt-4 text-lg text-gray-900">Cash:     {{ $player->money }}</p>
                <p class="mt-4 text-lg text-gray-900">EXP:      {{ $player->exp }} / {{ $player->max_exp }}</p>

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
        <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y">
            <h2 class="p-5 ml-10 font-semibold text-xl text-gray-800 leading-tight">
                My Train
            </h2>
        </div>
        <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y">
            <x-bladewind.tab-group name="train-info" color="gray">
                <x-slot name="headings">
                    <x-bladewind.tab-heading
                        name="locomotive"
                        active="true"
                        label="Locomotive" />
                    @foreach($train->wagon as $wagon)
                        <x-bladewind.tab-heading
                            name="wagon{{$wagon->id}}"
                            label="{{$wagon->name}}" />
                    @endforeach
                </x-slot>

                <x-bladewind.tab-body>
                    <x-bladewind.tab-content name="locomotive" active="true">
                        <div class="p-6 ml-5 flex-col">
                                <?php $locomotive = $train->locomotive;?>
                            <h3 class="p-5 font-semibold text-xl text-gray-800 leading-tight">
                                Locomotive
                            </h3>
                            <div class="p-6 flex-col">
                                <p class="mt-4 text-lg text-gray-900">Name:             {{ $locomotive->name }}</p>
                                <p class="mt-4 text-lg text-gray-900">Level:            {{ $locomotive->lvl }}</p>
                                <p class="mt-4 text-lg text-gray-900">Weight:           {{ $locomotive->weight }}t</p>
                                <p class="mt-4 text-lg text-gray-900">Power:            {{ $locomotive->power }}hp</p>
                                <p class="mt-4 text-lg text-gray-900">Wagon Capacity:   {{ $locomotive->getWagonCap() }}</p>
                                <p class="mt-4 text-lg text-gray-900">Armor:            {{ $locomotive->armor }} / {{ $locomotive->max_armor }}</p>
                                <p class="mt-4 text-lg text-gray-900">Fuel:             {{ $locomotive->fuel }} / {{ $locomotive->max_fuel }}</p>
                            </div>
                        </div>
                    </x-bladewind.tab-content>
                    @foreach($train->wagon as $wagon)
                        <x-bladewind.tab-content name="wagon{{$wagon->id}}">
                                <?php $type = str_replace('App\\Models\\', '', $wagon->wagonable_type)?>
                            <div class="p-6 ml-5 flex-col">
                                <h3 class="p-5 font-semibold text-xl text-gray-800 leading-tight">
                                    {{ $wagon->name }}
                                </h3>
                                <div class="p-6 flex-col">
                                    <p class="mt-4 text-lg text-gray-900">Name:             {{ $wagon->name }}</p>
                                    <p class="mt-4 text-lg text-gray-900">Level:            {{ $wagon->lvl }}</p>
                                    <p class="mt-4 text-lg text-gray-900">Weight:           {{ $wagon->weight }}t</p>
                                    <p class="mt-4 text-lg text-gray-900">Armor:            {{ $wagon->armor }} / {{ $wagon->max_armor }}</p>
                                    <p class="mt-4 text-lg text-gray-900">Type:             {{ $type }}</p>
                                    @if($type == 'CargoWagon')
                                        <?php $specWagon = \App\Models\CargoWagon::find($wagon->wagonable_id) ?>
                                        <p class="mt-4 text-lg text-gray-900">Capacity:     {{ $specWagon->capacity }}t</p>
                                    @elseif($type == 'WeaponWagon')
                                            <?php $specWagon = \App\Models\WeaponWagon::find($wagon->wagonable_id) ?>
                                        <p class="mt-4 text-lg text-gray-900">Weapon slots available:     {{ $specWagon->slots_available }}</p>
                                        @if($specWagon->weapons)
                                            <x-bladewind.tab-group name="weapon-info-{{$wagon->wagonable_id}}" color="gray">
                                                <x-slot name="headings">
                                                    @foreach($specWagon->weapons as $weapon)
                                                        <x-bladewind.tab-heading
                                                            name="weapon-{{$weapon->id}}"
                                                            label="{{$weapon->name}}" />
                                                    @endforeach
                                                </x-slot>
                                                <x-bladewind.tab-body>
                                                    @foreach($specWagon->weapons as $weapon)
                                                        <x-bladewind.tab-content name="weapon-{{$weapon->id}}">
                                                            <div class="p-6 ml-5 flex-col">
                                                                <h3 class="p-5 font-semibold text-xl text-gray-800 leading-tight">
                                                                    {{ $weapon->name }}
                                                                </h3>
                                                                <div class="p-6 flex-col">
                                                                    <p class="mt-4 text-lg text-gray-900">Name:             {{ $weapon->name }}</p>
                                                                    <p class="mt-4 text-lg text-gray-900">Level:            {{ $weapon->lvl }}</p>
                                                                    <p class="mt-4 text-lg text-gray-900">Damage:           {{ $weapon->damage }}</p>
                                                                    <p class="mt-4 text-lg text-gray-900">Type:             {{ $weapon->type }}</p>
                                                                </div>
                                                            </div>
                                                        </x-bladewind.tab-content>
                                                    @endforeach
                                                </x-bladewind.tab-body>
                                            </x-bladewind.tab-group>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </x-bladewind.tab-content>
                    @endforeach
                </x-bladewind.tab-body>

            </x-bladewind.tab-group>
            @if($train->locomotive)

            @endif
        </div>
        <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y">
            @if($train->wagon)
                @foreach($train->wagon as $wagon)

                @endforeach
            @endif
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
