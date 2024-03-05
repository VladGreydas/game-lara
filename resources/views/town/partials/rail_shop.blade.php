<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Choose what do you want to buy') }}
</h2>
<x-bladewind.tab-group name="rail_shop" color="black">
    <x-slot name="headings">
        <x-bladewind.tab-heading
            name="shop-locomotive"
            label="Locomotives" />
        <x-bladewind.tab-heading
            name="shop-wagon"
            label="Wagons"
        />
        <x-bladewind.tab-heading
            name="shop-weapon"
            label="Weapons"
        />
    </x-slot>
    <x-bladewind.tab-body>
        <x-bladewind.tab-content name="shop-locomotive">
            @include('town.partials.shop.locomotive')
        </x-bladewind.tab-content>
        <x-bladewind.tab-content name="shop-wagon">
            <x-bladewind.tab-group name="shop-wagons">
                <x-slot name="headings">
                    <x-bladewind.tab-heading
                        name="cargo-wagons"
                        label="{{__('Cargo Wagons')}}" />
                    <x-bladewind.tab-heading
                        name="weapon-wagons"
                        label="{{__('Weapon Wagons')}}" />
                </x-slot>
                <x-bladewind.tab-body>
                    <x-bladewind.tab-content name="cargo-wagons">
                        @include('town.partials.shop.cargo_wagons')
                    </x-bladewind.tab-content>
                    <x-bladewind.tab-content name="weapon-wagons">
                        @include('town.partials.shop.weapon_wagons')
                    </x-bladewind.tab-content>
                </x-bladewind.tab-body>
            </x-bladewind.tab-group>
        </x-bladewind.tab-content>
        <x-bladewind.tab-content name="shop-weapon">
            @include('town.partials.shop.weapons')
        </x-bladewind.tab-content>
    </x-bladewind.tab-body>
</x-bladewind.tab-group>
