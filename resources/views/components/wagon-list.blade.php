<div class="mt-4 p-4 border rounded-lg shadow bg-gray-50">
    <h3 class="text-lg font-bold mb-2">🚃 Wagons</h3>
    @foreach($wagons as $wagon)
        <div class="mb-4 p-2 border-b">
            <div class="flex flex-row">
                <p class="m-1"><strong>Wagon: {{ $wagon->name }}</strong></p>
                <x-rename type="wagon" id="{{$wagon->id}}" name="{{$wagon->name}}"/>
            </div>
            <ul class="list-disc ml-6">
                <li>Armor: {{ $wagon->armor }} / {{ $wagon->max_armor }}</li>
                <li>Level: {{ $wagon->lvl }}</li>
                <li>Weight: {{ $wagon->weight }}</li>
                @if($wagon->cargo_wagon)
                    <li>Type: <span class="text-indigo-600">Cargo</span></li>
                    <li>Capacity: {{ $wagon->cargo_wagon->capacity }}</li>
                @endif

                @if($wagon->weapon_wagon)
                    <li>Type: <span class="text-red-600">Weapon</span></li>
                    <x-weapon-list :weapons="$wagon->weapon_wagon->weapons" />
                @endif
            </ul>


        </div>
    @endforeach
</div>
