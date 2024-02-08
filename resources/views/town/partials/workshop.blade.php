<p>In progress</p>
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Choose which do you want to upgrade') }}
</h2>
<x-bladewind.tab-group name="workshop" color="black">
    <x-slot name="headings">
        <x-bladewind.tab-heading
            name="locomotive"
            label="Locomotive" />
    </x-slot>
    <x-bladewind.tab-body>
        <x-bladewind.tab-content name="locomotive">
            @include('town.partials.upgrade.locomotive')
        </x-bladewind.tab-content>
    </x-bladewind.tab-body>
</x-bladewind.tab-group>
