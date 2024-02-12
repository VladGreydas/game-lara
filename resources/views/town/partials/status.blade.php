@if(is_array(session('status')))
    @switch(session('status')['status'])
        @case('success')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-green-800 text-lg">
                {{session('status')['message']}}
            </p>
            @break
        @case('failed')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-lg text-red-800">
                {{session('status')['message']}}
            </p>
            @break
    @endswitch
@endif
