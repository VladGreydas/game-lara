<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Welcome to rail shop!') }}
</h2>
<x-bladewind.tab-group name="rail_shop" color="black">
    <x-slot name="headings">
        <x-bladewind.tab-heading
            name="buy"
            label="Buy" />
        <x-bladewind.tab-heading
            name="sell"
            label="Sell"
        />
    </x-slot>
    <x-bladewind.tab-body>
        <x-bladewind.tab-content name="buy">
            @include('town.partials.shop.buy')
        </x-bladewind.tab-content>
        <x-bladewind.tab-content name="sell">
            @include('town.partials.shop.sell')
        </x-bladewind.tab-content>
    </x-bladewind.tab-body>
</x-bladewind.tab-group>
