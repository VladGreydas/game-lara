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
        @if($town->rail_shop)
            <x-bladewind.tab-heading
                name="rail_shop"
                label="Rail shop"
            />
        @endif
        @if($town->shop)
            <x-bladewind.tab-heading
                name="shop"
                label="Shop"
            />
        @endif
    </x-slot>
    <x-bladewind.tab-body>
        <x-bladewind.tab-content name="station" active="true">
            @include('town.partials.station')
        </x-bladewind.tab-content>
        @if($town->workshop)
            <x-bladewind.tab-content name="workshop">
                @include('town.partials.workshop')
            </x-bladewind.tab-content>
        @endif
        @if($town->rail_shop)
            <x-bladewind.tab-content name="rail_shop">
                @include('town.partials.rail_shop')
            </x-bladewind.tab-content>
        @endif
    </x-bladewind.tab-body>
</x-bladewind.tab-group>
