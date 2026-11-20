@props(['paginator'])

@php
    $hasPages = $paginator->hasPages();
    $total = $paginator->total();
    $firstItem = $paginator->firstItem();
    $lastItem = $paginator->lastItem();
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $onFirstPage = $paginator->onFirstPage();
    $hasMorePages = $paginator->hasMorePages();
    $prevPage = $onFirstPage ? 1 : ($paginator->currentPage() - 1);
    $nextPage = $hasMorePages ? ($paginator->currentPage() + 1) : $lastPage;
@endphp

<div class="mt-6">
    {{-- Per page selector — always visible --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2 text-sm text-[#a3a3a3]">
            <span>{{ __('admin.showing') }}</span>
            <span class="text-[#e5e5e5]">{{ $firstItem ?? 0 }}</span>
            <span>—</span>
            <span class="text-[#e5e5e5]">{{ $lastItem ?? 0 }}</span>
            <span>{{ __('admin.of') }}</span>
            <span class="text-[#e5e5e5]">{{ $total }}</span>
        </div>

        <form method="GET" action="{{ request()->url() }}" class="flex items-center gap-2">
            @foreach(request()->except('page') as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="per_page" class="text-sm text-[#a3a3a3]">{{ __('admin.per_page') }}:</label>
            <select name="per_page" id="per_page"
                onchange="this.form.submit()"
                class="bg-[#1f1f1f] border border-[#3f3f3f] rounded px-2 py-1 text-[#e5e5e5] text-sm focus:border-[#eab308] focus:outline-none">
                @foreach([5, 10, 20, 50, 100] as $option)
                    <option value="{{ $option }}" {{ request('per_page', 10) == $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Page numbers — only when there are multiple pages --}}
    @if ($hasPages)
    <nav class="flex items-center justify-center gap-1">
        {{-- Previous button --}}
        @if ($onFirstPage)
            <span class="px-3 py-1.5 text-sm text-[#737373] bg-[#1f1f1f] border border-[#3f3f3f] rounded cursor-not-allowed">
                ←
            </span>
        @else
            <form method="GET" action="{{ request()->url() }}" class="inline">
                @foreach(request()->except('page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <button type="submit" name="page" value="{{ $prevPage }}"
                    class="px-3 py-1.5 text-sm text-[#e5e5e5] bg-[#1f1f1f] border border-[#3f3f3f] rounded hover:bg-[#2d2d2d] hover:border-[#eab308]">
                    ←
                </button>
            </form>
        @endif

        {{-- Page numbers --}}
        @php
            $range = 2;
            $start = max(1, $currentPage - $range);
            $end = min($lastPage, $currentPage + $range);
        @endphp

        @if ($start > 1)
            <form method="GET" action="{{ request()->url() }}" class="inline">
                @foreach(request()->except('page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <button type="submit" name="page" value="1"
                    class="px-3 py-1.5 text-sm text-[#e5e5e5] bg-[#1f1f1f] border border-[#3f3f3f] rounded hover:bg-[#2d2d2d] hover:border-[#eab308]">
                    1
                </button>
            </form>
            @if ($start > 2)
                <span class="px-2 py-1.5 text-sm text-[#737373]">...</span>
            @endif
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $currentPage)
                <span class="px-3 py-1.5 text-sm text-[#eab308] bg-[#2d2d2d] border border-[#eab308] rounded font-semibold">
                    {{ $page }}
                </span>
            @else
                <form method="GET" action="{{ request()->url() }}" class="inline">
                    @foreach(request()->except('page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" name="page" value="{{ $page }}"
                        class="px-3 py-1.5 text-sm text-[#e5e5e5] bg-[#1f1f1f] border border-[#3f3f3f] rounded hover:bg-[#2d2d2d] hover:border-[#eab308]">
                        {{ $page }}
                    </button>
                </form>
            @endif
        @endfor

        @if ($end < $lastPage)
            @if ($end < $lastPage - 1)
                <span class="px-2 py-1.5 text-sm text-[#737373]">...</span>
            @endif
            <form method="GET" action="{{ request()->url() }}" class="inline">
                @foreach(request()->except('page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <button type="submit" name="page" value="{{ $lastPage }}"
                    class="px-3 py-1.5 text-sm text-[#e5e5e5] bg-[#1f1f1f] border border-[#3f3f3f] rounded hover:bg-[#2d2d2d] hover:border-[#eab308]">
                    {{ $lastPage }}
                </button>
            </form>
        @endif

        {{-- Next button --}}
        @if ($hasMorePages)
            <form method="GET" action="{{ request()->url() }}" class="inline">
                @foreach(request()->except('page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <button type="submit" name="page" value="{{ $nextPage }}"
                    class="px-3 py-1.5 text-sm text-[#e5e5e5] bg-[#1f1f1f] border border-[#3f3f3f] rounded hover:bg-[#2d2d2d] hover:border-[#eab308]">
                    →
                </button>
            </form>
        @else
            <span class="px-3 py-1.5 text-sm text-[#737373] bg-[#1f1f1f] border border-[#3f3f3f] rounded cursor-not-allowed">
                →
            </span>
        @endif
    </nav>
    @endif
</div>
