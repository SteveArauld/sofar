@props(['product'])
@php
    $p = $product;
    $img = $p->relationLoaded('images') ? $p->images->first() : $p->images()->first();

    $vrows = collect(data_get($p->variations, 'variations', []));

    // Une pastille de couleur par variation (l'option couleur porte "colorcode").
    $colors = $vrows->map(function ($row) {
        foreach ((array) $row as $opt) {
            if (is_array($opt) && data_get($opt, 'colorcode')) {
                return [
                    'name' => data_get($opt, 'color') ?: data_get($opt, 'name'),
                    'code' => data_get($opt, 'colorcode'),
                ];
            }
        }
        return null;
    })->filter()->unique('code')->values();

    // Prix : le prix barré vit dans les variations (precoantes) quand la colonne est vide.
    $precoAtual = (float) ($p->price ?: $vrows->min('preco'));
    $precoAntes = (float) ($p->price_before ?: $vrows->max('precoantes'));
    $temDesconto = $precoAntes > $precoAtual && $precoAtual > 0;
@endphp
<div class="Artigo">
    <div>
        <div class="EnvioImediato">
            <img src="/assets/stickers/EnvioImediato.png" alt="Envio Imediato">
        </div>
        <a href="#" class="Wishlist" data-artigo="{{ $p->id }}" title="Adicionar aos favoritos">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="lucide lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
        </a>
        <div class="Selos"></div>
        <div class="preview">
            <div class="box-image square">
                <div class="box-image-hover" style="aspect-ratio:1;">
                    <a href="{{ url('/'.$p->slug) }}">
                        <img class="img-responsive" style="aspect-ratio:1;" loading="lazy"
                             src="{{ $img ? asset($img->path) : '/assets/images/no-photo.svg' }}"
                             onerror="this.onerror=null;this.src='/assets/images/no-photo.svg'"
                             alt="{{ $p->name }}">
                    </a>
                </div>
            </div>
        </div>
        <div class="ProductFooter">
            <div class="headProduto">
                <div class="nome">{{ $p->name }}</div>
            </div>
            <div class="foot">
                <div>
                    <div class="colors">
                        @foreach ($colors as $c)
                            <span style="background: {{ $c['code'] }};" title="{{ $c['name'] }}"></span>
                        @endforeach
                    </div>
                </div>
                <div class="Preco">
                    @if ($temDesconto)
                        <span>{{ number_format($precoAntes, 0, ',', ' ') }}€</span>
                    @endif
                    {{ $precoAtual > 0 ? number_format($precoAtual, 0, ',', ' ').'€' : 'Sob consulta' }}
                </div>
                <div style="display:none;">
                    <a class="vermais" href="{{ url('/'.$p->slug) }}">VER MAIS</a>
                </div>
            </div>
        </div>
    </div>
</div>
