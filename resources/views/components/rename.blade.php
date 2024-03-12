<div>
    <x-bladewind.button onclick="showModal('modal-rename-{{$name}}-{{$id}}')"
                        class="text-white w-1/8 h-full mt-1 p-2 bg-slate-800">
        {{__('Rename')}}
    </x-bladewind.button>
    <x-bladewind.modal
        backdrop_can_close="false"
        name="modal-rename-{{$name}}-{{$id}}"
        ok_button_label=""
    >
        <form method="post" action="{{route($name.'.rename', $id)}}"
              class="flex flex-col flex-wrap items-center font-semibold">
            @csrf
            @method('PATCH')
            Enter new name:
            <input class="w-9/12" type="text" name="new_name" placeholder="Enter here..." autocomplete="off">
            <x-bladewind.button can_submit="true"
                                class="w-2/5 m-2 p-2 h-10 bg-slate-800 text-white">
                Rename
            </x-bladewind.button>
        </form>
    </x-bladewind.modal>
</div>
