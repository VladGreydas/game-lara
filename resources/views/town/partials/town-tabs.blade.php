<x-bladewind.tab-group name="town-info" color="black">
    <x-slot name="headings">
        <x-bladewind.tab-heading
            name="station"
            label="Station"
            active="true"
        />
        @if($town->workshop)
            <x-bladewind.tab-heading
                name="workshop"
                label="Workshop"
            />
        @endif
    </x-slot>
    <x-bladewind.tab-body>
        <x-bladewind.tab-content name="station" active="true">
            <?php $fuel = $player->train->locomotive->max_fuel - $player->train->locomotive->fuel?>
            <p class="mt-4 text-lg text-gray-900">Fuel: {{ $player->train->locomotive->fuel }} / {{ $player->train->locomotive->max_fuel }}</p>
            @if($fuel > 0)

            @endif
        </x-bladewind.tab-content>
        @if($town->workshop)
            <x-bladewind.tab-content name="workshop">
                @include('town.partials.workshop')
            </x-bladewind.tab-content>
        @endif
    </x-bladewind.tab-body>
</x-bladewind.tab-group>
