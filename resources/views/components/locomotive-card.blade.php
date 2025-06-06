<div class="mt-4 p-4 border rounded-lg shadow bg-gray-50">
    <?php
    use App\Models\Locomotive;
    /** @var Locomotive $locomotive */
    /** @var bool $upgrade */

    $new_lvl = null;
    $add_weight = null;
    $add_power = null;
    $add_armor = null;
    $add_fuel = null;
    if($upgrade) {
        $new_lvl = '->' . $locomotive->lvl+1;
        $add_weight = '+' . 50 * ($locomotive->lvl+1);
        $add_power = '+' . 500 * ($locomotive->lvl+1);
        $add_armor = '+' . 100 * ($locomotive->lvl+1);
        $add_fuel = '+' . 5 * ($locomotive->lvl+1);
    }

    ?>
    <div class="flex flex-row pb-1">
        <h3 class="text-lg font-bold m-1 mt-0">Locomotive: {{ $locomotive->name }}</h3>
        @if($rename)
            <x-rename type="locomotive" id="{{$locomotive->id}}" name="{{$locomotive->name}}"/>
        @endif
    </div>

    <p><strong>Weight:</strong> {{ $locomotive->weight }}<span class="text-green-500">  {{ $add_weight }}</span></p>
    <p><strong>Power:</strong> {{ $locomotive->power }}<span class="text-green-500">  {{ $add_power }}</span></p>
    <p><strong>Armor:</strong> {{ $locomotive->armor }} / {{ $locomotive->max_armor }}<span class="text-green-500">  {{ $add_armor }}</span></p>
    @if($upgrade && $locomotive->armor < $locomotive->max_armor)
        <form action="{{ route('locomotive.repair', $locomotive) }}" method="POST">
            @csrf
            <x-primary-button>Repair</x-primary-button>
        </form>
    @endif
    <p><strong>Fuel:</strong> {{ $locomotive->fuel }} / {{ $locomotive->max_fuel }}<span class="text-green-500">  {{ $add_fuel }}</span></p>
    <p><strong>Level:</strong> {{ $locomotive->lvl }}<span class="text-green-500">  {{ $new_lvl }}</span></p>
    @if($upgrade)
        <p><strong>Upgrade cost:</strong> {{ $locomotive->upgrade_cost }}</p>
        <form action="{{ route('locomotive.upgrade', $locomotive) }}" method="POST" class="mt-2">
            @csrf
            <x-primary-button>Upgrade Locomotive</x-primary-button>
        </form>
    @endif
</div>
