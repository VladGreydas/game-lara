<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Choose what do you want to buy') }}
</h2>
<x-bladewind.tab-group name="rail_shop_buy" color="black">
    <x-slot name="headings">
        <x-bladewind.tab-heading
            name="buy-locomotive"
            label="Locomotives" />
        <x-bladewind.tab-heading
            name="buy-wagon"
            label="Wagons"
        />
        <x-bladewind.tab-heading
            name="buy-weapon"
            label="Weapons"
        />
    </x-slot>
    <x-bladewind.tab-body>
        <x-bladewind.tab-content name="buy-locomotive">
            @include('town.partials.shop.locomotive')
        </x-bladewind.tab-content>
        <x-bladewind.tab-content name="buy-wagon">
            <x-bladewind.tab-group name="buy-wagons">
                <x-slot name="headings">
                    <x-bladewind.tab-heading
                        name="buy-cargo-wagons"
                        label="{{__('Cargo Wagons')}}" />
                    <x-bladewind.tab-heading
                        name="buy-weapon-wagons"
                        label="{{__('Weapon Wagons')}}" />
                </x-slot>
                <x-bladewind.tab-body>
                    <x-bladewind.tab-content name="buy-cargo-wagons">
                        @include('town.partials.shop.cargo_wagons')
                    </x-bladewind.tab-content>
                    <x-bladewind.tab-content name="buy-weapon-wagons">
                        @include('town.partials.shop.weapon_wagons')
                    </x-bladewind.tab-content>
                </x-bladewind.tab-body>
            </x-bladewind.tab-group>
        </x-bladewind.tab-content>
        <x-bladewind.tab-content name="buy-weapon">
            @include('town.partials.shop.weapons')
        </x-bladewind.tab-content>
    </x-bladewind.tab-body>
</x-bladewind.tab-group>
