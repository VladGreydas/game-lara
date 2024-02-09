@if(session('status') === 'upgrade-successful')
    <p
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 2000)"
        class="text-green-800 text-lg">
        {{__('Upgraded successfully')}}
    </p>
@elseif(session('status') === 'upgrade-failed')
    <p
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 2000)"
        class="text-lg text-red-800">
        {{__("Can't upgrade, not enough money")}}
    </p>
@endif
