@extends('layouts.app')

@section('title', 'Favoritos | DFP Interiores')

@push('mods-css')
<link href="/assets/css/pt/mods/wishlist.css" rel="stylesheet">
@endpush

@section('content')
<div class="container breadcrumb">
    <div>
        <a href="/">Início</a>
        <h1>Favoritos</h1>
    </div>
</div>

<section class="container Wishlist" style="padding-bottom:60px;">
    @if ($products->isEmpty())
        <div class="row">
            <div class="col-md-6 bg-white p-5 mx-auto">
                <section class="text-center">
                    <div class="error-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>
                        </svg>
                    </div>
                    <h2>Não tem artigos nos favoritos</h2>
                    <p><a href="/" class="btn btn-primary">Continuar a comprar</a></p>
                </section>
            </div>
        </div>
    @else
        <div class="CatalogoGrid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;">
            @foreach ($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    @endif
</section>
@endsection
