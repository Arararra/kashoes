@props([
    'breadcrumbs' => [],
])

<nav {{ $attributes->class(['fi-breadcrumbs']) }}>
    <ol class="fi-breadcrumbs-list flex flex-wrap items-center gap-x-2">
        @foreach ($breadcrumbs as $url => $label)
            <li class="fi-breadcrumbs-item flex items-center gap-x-2">
                @if (! $loop->first)
                    <x-filament::icon
                        alias="breadcrumbs.separator"
                        icon="heroicon-m-chevron-right"
                        @class([
                            'fi-breadcrumbs-item-separator flex h-5 w-5 rtl:hidden',
                        ])
                        style="color: #b84c65; opacity: 0.4;"
                    />

                    <x-filament::icon
                        :alias="['breadcrumbs.separator.rtl', 'breadcrumbs.separator']"
                        icon="heroicon-m-chevron-left"
                        @class([
                            'fi-breadcrumbs-item-separator flex h-5 w-5 ltr:hidden',
                        ])
                        style="color: #b84c65; opacity: 0.4;"
                    />
                @endif

                @if (is_int($url))
                    <span
                        class="fi-breadcrumbs-item-label text-sm font-medium"
                        style="color: #b84c65; {{ $loop->last ? 'opacity:1; font-weight:600;' : 'opacity:0.65;' }}"
                    >
                        {{ $label }}
                    </span>
                @else
                    <a
                        {{ \Filament\Support\generate_href_html($url) }}
                        class="fi-breadcrumbs-item-label text-sm font-medium transition duration-75"
                        style="color: #b84c65; {{ $loop->last ? 'opacity:1; font-weight:600;' : 'opacity:0.65;' }}"
                        onmouseover="this.style.opacity='1'"
                        onmouseout="this.style.opacity='{{ $loop->last ? '1' : '0.65' }}'"
                    >
                        {{ $label }}
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
