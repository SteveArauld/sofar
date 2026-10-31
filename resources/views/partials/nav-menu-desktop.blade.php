{{-- Menu desktop bilingue (labels via __() + catégories Spatie) --}}
<ul>
    <li>
        <a href="{{ locale_url('descontos70') }}">
            <svg width="30" height="30" class="icon" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="/media/categorias/icons/descontos70.svg#descontos70"
                    href="/media/categorias/icons/descontos70.svg#descontos70"></use>
            </svg>
            {{ __('Descontos até 70%') }}
        </a>
    </li>
    <li>
        <a href="{{ locale_url('artigosExclusivosOnline') }}">
            <svg width="30" height="30" class="icon" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="/media/categorias/icons/exclusivo_online.svg#exclusivo_online"
                    href="/media/categorias/icons/exclusivo_online.svg#exclusivo_online"></use>
            </svg>
            {{ __('Exclusivo Online') }}
        </a>
    </li>
    @foreach ($navMenu ?? [] as $item)
        <li>
            <a href="#">
                <svg width="30" height="30" class="icon" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="/media/categorias/icons/{{ $item['icon'] }}.svg#{{ $item['iconId'] }}"
                        href="/media/categorias/icons/{{ $item['icon'] }}.svg#{{ $item['iconId'] }}"></use>
                </svg>
                {{ $item['label'] }}
            </a>
            <div class="menuBox">
                <ul style="padding-bottom:20px">
                    <li class="Tudo"><a href="{{ locale_url($item['slug']) }}">{{ __('Ver tudo') }}</a></li>
                </ul>
                <ul style="padding-top:0px">
                    @foreach ($item['children'] as $child)
                        <li>
                            <a href="{{ locale_url($child->slug) }}">{{ $child->name }}</a>
                            @if ($child->children->isNotEmpty())
                                <ul>
                                    @foreach ($child->children as $i => $grand)
                                        <li style="order: {{ $i + 1 }};">
                                            <a href="{{ locale_url($grand->slug) }}">{{ $grand->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </li>
    @endforeach
</ul>
