<div class="mt-4 p-4 border rounded-lg shadow bg-gray-50">
    <div class="flex flex-row pb-1">
        <h3 class="text-lg font-bold m-1 mt-0">🚂 Locomotive: {{ $locomotive->name }}</h3>
        <x-rename type="locomotive" id="{{$locomotive->id}}" name="{{$locomotive->name}}"/>
    </div>

    <p><strong>Power:</strong> {{ $locomotive->power }}</p>
    <p><strong>Armor:</strong> {{ $locomotive->armor }} / {{ $locomotive->max_armor }}</p>
    <p><strong>Fuel:</strong> {{ $locomotive->fuel }} / {{ $locomotive->max_fuel }}</p>
    <p><strong>Level:</strong> {{ $locomotive->lvl }}</p>
</div>
