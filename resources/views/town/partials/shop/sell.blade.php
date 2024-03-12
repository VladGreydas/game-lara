<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Choose what do you want to sell') }}
</h2>
<x-bladewind.tab-group name="rail_shop_sell" color="black">
    <x-slot name="headings">
        <x-bladewind.tab-heading
            name="sell-wagon"
            label="Wagons"
        />
        <x-bladewind.tab-heading
            name="sell-weapon"
            label="Weapons"
        />
    </x-slot>
    <x-bladewind.tab-body>
        <x-bladewind.tab-content name="sell-wagon">
            @include('town.partials.shop.sell.wagons')
        </x-bladewind.tab-content>
        <x-bladewind.tab-content name="sell-weapon">
            @include('town.partials.shop.sell.weapons')
        </x-bladewind.tab-content>
    </x-bladewind.tab-body>
</x-bladewind.tab-group>
