<x-app-layout>
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    @if (count($player))
        <?php $player = $player[0];?>
        <?php $train = $player->train;?>
        <x-slot name="header">
            @include('player.partials.player-header')
        </x-slot>
        @include('player.partials.player-info')
        <div class="mt-6 ml-64 mr-64 bg-white shadow-sm rounded-lg divide-y"> {{-- Train Info --}}
            <h2 class="p-5 ml-10 font-semibold text-xl text-gray-800 leading-tight">
                My Train
            </h2>
            <x-bladewind.tab-group name="train-info" color="black">
                <x-slot name="headings">
                    <x-bladewind.tab-heading
                        name="locomotive"
                        label="Locomotive" />
                    @foreach($train->wagons as $wagon)
                        <x-bladewind.tab-heading
                            name="wagon{{$wagon->id}}"
                            label="{{$wagon->name}}" />
                    @endforeach
                </x-slot>

                <x-bladewind.tab-body>
                    <x-bladewind.tab-content name="locomotive">
                        @include('player.partials.locomotive-info')
                    </x-bladewind.tab-content>
                    @foreach($train->wagons as $wagon)
                        <x-bladewind.tab-content name="wagon{{$wagon->id}}">
                                <?php $type = str_replace('App\\Models\\', '', $wagon->wagonable_type)?>
                            <div class="p-6 ml-5 flex-col">
                                <h3 class="p-5 font-semibold text-xl text-gray-800 leading-tight">
                                    {{ $wagon->name }}
                                </h3>
                                <div class="p-6 flex-col">
                                    @include('player.partials.wagon-info')
                                    @if($type == 'CargoWagon')
                                            <?php $specWagon = \App\Models\CargoWagon::find($wagon->wagonable_id) ?>
                                        <p class="mt-4 text-lg text-gray-900">Capacity:     {{ $specWagon->capacity }}t</p>
                                    @elseif($type == 'WeaponWagon')
                                            <?php $specWagon = \App\Models\WeaponWagon::find($wagon->wagonable_id) ?>
                                        <p class="mt-4 text-lg text-gray-900">Weapon slots available:     {{ $specWagon->slots_available }}</p>
                                        <p class="mt-4 text-lg text-gray-900">Weapons:</p>
                                    @endif
                                </div>
                                @if($specWagon->weapons)
                                        <?php $weapons = $specWagon->weapons;?>
                                    <x-bladewind.tab-group name="weapon-info-{{$wagon->wagonable_id}}" color="black">
                                        <x-slot name="headings">
                                            @foreach($weapons as $weapon)
                                                <x-bladewind.tab-heading
                                                    name="weapon-{{$weapon->id}}"
                                                    label="{{$weapon->name}}" />
                                            @endforeach
                                        </x-slot>
                                        <x-bladewind.tab-body>
                                            @foreach($specWagon->weapons as $weapon)
                                                @include('player.partials.weapon-info')
                                            @endforeach
                                        </x-bladewind.tab-body>
                                    </x-bladewind.tab-group>
                                @endif
                            </div>
                        </x-bladewind.tab-content>
                    @endforeach
                </x-bladewind.tab-body>
            </x-bladewind.tab-group>
        </div>
    @else
        @include('player.partials.player-new')
    @endif
</x-app-layout>
