<x-bladewind.tab-group name="weapons">
    <x-slot name="headings">
        @foreach($player->train->wagon as $wagon)
            @if($wagon->wagonable instanceof \App\Models\WeaponWagon && $wagon->wagonable->weapons)
                @foreach($wagon->wagonable->weapons as $weapon)
                    <x-bladewind.tab-heading
                        name="weapon{{$weapon->id}}"
                        label="{{$weapon->name.' - '.$wagon->name}}" />
                @endforeach
            @endif
        @endforeach
    </x-slot>
    <x-bladewind.tab-body>
        @foreach($player->train->wagon as $wagon)
            @if($wagon->wagonable instanceof \App\Models\WeaponWagon && $wagon->wagonable->weapons)
                @foreach($wagon->wagonable->weapons as $weapon)
                    <x-bladewind.tab-content name="weapon{{$weapon->id}}">
                        <div class="p-6 flex-col">
                            <p class="mt-4 text-lg text-gray-900">Name:             {{ $weapon->name }}</p>
                            <p class="mt-4 text-lg text-gray-900">Level:            {{ $weapon->lvl }}(+1)</p>
                            <p class="mt-4 text-lg text-gray-900">Damage:           {{ $weapon->damage }} (+{{ 50 * ($weapon->lvl+1) }})</p>
                            <p class="mt-4 text-lg text-gray-900">Upgrade Cost:     {{ $weapon->upgrade_cost }}</p>

                            <form method="POST" action="{{ route('weapon.upgrade', $weapon) }}">
                                @csrf
                                @method('PATCH')
                                <div class="mt-4 space-x-2">
                                    <x-primary-button>{{ __('Upgrade') }}</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </x-bladewind.tab-content>
                @endforeach
            @endif
        @endforeach
    </x-bladewind.tab-body>
</x-bladewind.tab-group>

