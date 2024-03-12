<section>
    <x-bladewind.tab-content name="weapon-{{$weapon->id}}">
        <div class="p-6 ml-5 flex-col">
            <h3 class="p-5 font-semibold text-xl text-gray-800 leading-tight">
                {{ $weapon->name }}
            </h3>
            <div class="p-6 flex-col">
                <div class="flex">
                    <p class="pt-2 pr-2 text-lg text-gray-900">Name: {{ $weapon->name }}</p>
                    <x-rename name="weapon" id="{{$weapon->id}}"/>
                </div>
                <p class="mt-4 text-lg text-gray-900">Level: {{ $weapon->lvl }}</p>
                <p class="mt-4 text-lg text-gray-900">Damage: {{ $weapon->damage }}</p>
                <p class="mt-4 text-lg text-gray-900">Type: {{ $weapon->type }}</p>
            </div>
        </div>
    </x-bladewind.tab-content>
</section>
