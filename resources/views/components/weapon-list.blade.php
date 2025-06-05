@if($weapons->count())
    <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Weapons:</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($weapons as $weapon)
                <div class="border rounded-lg shadow p-4 bg-white">
                    <div class="flex flex-row pb-1">
                        <h4 class="text-md font-bold text-gray-900 m-1">{{ $weapon->name }}</h4>
                        <x-rename type="weapon" id="{{$weapon->id}}" name="{{$weapon->name}}"/>
                    </div>
                    <p class="text-sm text-gray-700"><span class="font-semibold">DMG:</span> {{ $weapon->damage }}</p>
                    <p class="text-sm text-gray-700"><span class="font-semibold">Type:</span> {{ $weapon->type }}</p>
                    <p class="text-sm text-gray-700"><span class="font-semibold">Level:</span> {{ $weapon->lvl }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endif
