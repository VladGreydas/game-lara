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
{{--        <x-bladewind.tab-content name="shop-wagon">--}}
{{--            @include('')--}}
{{--        </x-bladewind.tab-content>--}}
{{--        <x-bladewind.tab-content name="shop-weapon">--}}
{{--            @include('')--}}
{{--        </x-bladewind.tab-content>--}}
    </x-bladewind.tab-body>
</x-bladewind.tab-group>
