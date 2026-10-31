{{-- Menu mobile bilingue --}}
<ul class="mobileMenu">
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
                {{ $item['label'] }} <span class="cross"></span>
            </a>
            <ul>
                @foreach ($item['children'] as $child)
                    <li>
                        <a href="{{ locale_url($child->slug) }}">
                            {{ $child->name }}
                            @if ($child->children->isNotEmpty())
                                <span class="cross"></span>
                            @endif
                        </a>
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
                <li class="Tudo"><a href="{{ locale_url($item['slug']) }}">{{ __('Ver tudo') }}</a></li>
            </ul>
        </li>
    @endforeach
</ul>
