@extends('layouts.app')

@section('title', 'As nossas lojas | DFP Interiores')

@push('mods-css')
<link href="/assets/css/pt/mods/lojas.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="/assets/vue/cookies.js"></script>
@endpush

@section('content')
@verbatim
<section class="container Lojas">
  <div class="container breadcrumb">
    <div>
        <a href="/">Início</a>
        <h1>Lojas</h1>
    </div>
    <div class='actions'></div>
  </div>

  <h2 style="margin-bottom:20px;">A nossa loja</h2>
  <div class="lista">
    <div style="margin-bottom: 30px;">
      <div style="padding:10px;">
        <div class="nome">DFP INTERIORES &ndash; VENDAS NOVAS</div>
        <p>
          <i class="fa fa-map-marker" aria-hidden="true" style="font-size:13px;margin-right:7px;margin-left:6px"></i>
          Rua José Francisco Fragoso, n.º 45, 7080-035 Vendas Novas &ndash; Évora, Portugal
        </p>
        <b><a href="https://www.google.com/maps/search/Rua+Jos%C3%A9+Francisco+Fragoso+45+Vendas+Novas" target="_blank" rel="noopener" style="color: rgb(73 150 249);">Obter direções no Google Maps</a></b>

        <p class="contato">
          <span class="email">
            <i class="fa fa-envelope" aria-hidden="true" style="font-size:13px;margin-right:4px;margin-left:3px"></i>
            <a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a>
          </span>
          <span class="telefone">
            <i class="fa fa-phone" aria-hidden="true" style="font-size:13px;margin-right:4px;margin-left:3px"></i>
            <a href="tel:+351912026453">+351 912 026 453</a>
          </span>
        </p>

        <p style="font-size:13px;color:#666;">
          DFP Interiores, Unipessoal Lda &nbsp;&bull;&nbsp; NIF 504074571 &nbsp;&bull;&nbsp; Capital social 500.000,00 &euro;
        </p>
      </div>
    </div>
  </div>
</section>
@endverbatim
@endsection
