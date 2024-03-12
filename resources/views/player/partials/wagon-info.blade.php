<section>
    <div class="flex">
        <p class="pt-2 pr-2 text-lg text-gray-900">Name:             {{ $wagon->name }}</p>
        <x-rename name="wagon" id="{{$wagon->id}}"/>
    </div>
    <p class="mt-4 text-lg text-gray-900">Level:            {{ $wagon->lvl }}</p>
    <p class="mt-4 text-lg text-gray-900">Weight:           {{ $wagon->weight }}t</p>
    <p class="mt-4 text-lg text-gray-900">Armor:            {{ $wagon->armor }} / {{ $wagon->max_armor }}</p>
    <p class="mt-4 text-lg text-gray-900">Type:             {{ $type }}</p>
</section>
