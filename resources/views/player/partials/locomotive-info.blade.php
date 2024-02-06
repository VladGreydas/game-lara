<section>
    <div class="p-6 ml-5 flex-col">
        <?php $locomotive = $train->locomotive;?>
        <h3 class="p-5 font-semibold text-xl text-gray-800 leading-tight">
            Locomotive
        </h3>
        <div class="p-6 flex-col">
            <p class="mt-4 text-lg text-gray-900">Name:             {{ $locomotive->name }}</p>
            <p class="mt-4 text-lg text-gray-900">Level:            {{ $locomotive->lvl }}</p>
            <p class="mt-4 text-lg text-gray-900">Weight:           {{ $locomotive->weight }}t</p>
            <p class="mt-4 text-lg text-gray-900">Power:            {{ $locomotive->power }}hp</p>
            <p class="mt-4 text-lg text-gray-900">Wagon Capacity:   {{ $locomotive->getWagonCap() }}</p>
            <p class="mt-4 text-lg text-gray-900">Armor:            {{ $locomotive->armor }} / {{ $locomotive->max_armor }}</p>
            <p class="mt-4 text-lg text-gray-900">Fuel:             {{ $locomotive->fuel }} / {{ $locomotive->max_fuel }}</p>
        </div>
    </div>
</section>
