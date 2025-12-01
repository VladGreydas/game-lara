<div class="border-t border-gray-200 pt-6 mt-3 flex flex-row">
    @php
        $temp_exp = 50; //($player->exp * 100)/$player->max_exp
    @endphp
    <h2 class="font-bold pr-2 w-24">{{$player->nickname}}</h2>
    <span class="font-semibold text-gray-700">Lvl {{ $player->lvl }}</span>
    <div class="w-3/4 bg-gray-200 rounded-full mx-2">
        <div class="h-full bg-green-800 rounded-full" style="width: {{ $progress }}%;"></div>
    </div>
    <p class="text-gray-500">{{$player->exp}} / {{$player->max_exp}}</p>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500 pl-2" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM5 9a1 1 0 000 2h10a1 1 0 100-2H5z" />
    </svg>
    <span class="font-semibold text-gray-700">{{ number_format($player->money, 2, '.', ' ') }}</span>
</div>
