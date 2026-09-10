@extends('layouts.app')

@section('title', $metaTitle)

@push('mods-css')
<link href="/assets/css/pt/mods/catalogo.css" rel="stylesheet">
@endpush

@section('content')
<div class="container breadcrumb">
    <div>
        <a href="/">Início</a>
        @foreach ($breadcrumb as $node)
            <a href="/{{ $node['link'] ?? $node['seo'] ?? '' }}">{{ $node['nome'] ?? $node['name'] ?? '' }}</a>
        @endforeach
        <h1>{{ $h1 }}</h1>
    </div>
    <div class="actions">
        <div class="OrdenarDesktop">
            <select name="sort" class="Ordenar" data-field="sort">
                <option value="relevante">Mais relevantes</option>
                <option value="precoasc">Menor preço</option>
                <option value="precodesc">Maior Preço</option>
                <option value="recentes">Mais recentes</option>
                <option value="populares">Mais populares</option>
            </select>
        </div>
        <svg @click='offside.toggle("filtro")' class="OpenFiltroMobile" width="26" height="26" viewBox="0 0 24 24"
             xmlns="http://www.w3.org/2000/svg">
            <title>Filter</title>
            <g fill="none" fill-rule="evenodd">
                <line x1="4" y1="5" x2="16" y2="5" stroke="#0C0310" stroke-width="2" stroke-linecap="round"/>
                <line x1="4" y1="12" x2="10" y2="12" stroke="#0C0310" stroke-width="2" stroke-linecap="round"/>
                <line x1="14" y1="12" x2="20" y2="12" stroke="#0C0310" stroke-width="2" stroke-linecap="round"/>
                <line x1="8" y1="19" x2="20" y2="19" stroke="#0C0310" stroke-width="2" stroke-linecap="round"/>
                <circle stroke="#0C0310" stroke-width="2" cx="18" cy="5" r="2"/>
                <circle stroke="#0C0310" stroke-width="2" cx="12" cy="12" r="2"/>
                <circle stroke="#0C0310" stroke-width="2" cx="6" cy="19" r="2"/>
            </g>
        </svg>
    </div>
</div>

<section class="container">
    <div class="row">
        <div :class="{ open: offside.filtro }" class="col-2 CatalogoFiltro">
            <div class="FiltroCatalogo" style="position: sticky; top: 10px;">
                <div>
                    {!! $filters !!}
                    <div class="BlocoFiltro OrdenarMobile">
                        <h2>Ordenar</h2>
                        <select name="sort" class="Ordenar" data-field="sort">
                            <option value="relevante">Mais relevantes</option>
                            <option value="precoasc">Menor preço</option>
                            <option value="precodesc">Maior Preço</option>
                            <option value="recentes">Mais recentes</option>
                            <option value="populares">Mais populares</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-10 CatalogoLista">
            <div class="SemArtigos" @style(['display:none' => $products->isNotEmpty()])>
                <h3>Não foram encontrados artigos.</h3>
            </div>

            <div id="ListaArtigos" class="CatalogoGrid">
                @forelse ($products as $product)
                    <x-product-card :product="$product" />
                @empty
                    <p class="SemArtigos">Sem artigos (importação do catálogo em curso?).</p>
                @endforelse
            </div>

            <a href="#" class="CarregaMaisArtigos">
                Clique aqui para Ver mais artigos
                <div id="productsLoaderPreloader" class="Loader"></div>
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    var Filtro = @json($filtro);
</script>
@if (! empty($moreKey))
{!! $moreScript !!}
@endif
@endpush
