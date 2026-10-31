@extends('layouts.app')

@section('title', __('Contactos').' | DFP Interiores')

@push('scripts')
<script src="/assets/vue/cookies.js"></script>
<script src="/assets/modulos/ajuda.js"></script>
@endpush

@section('content')
<div class="container breadcrumb">
    <div>
        <a href="/">{{ __('Início') }}</a>
        <h1>{{ __('Contactos') }}</h1>
    </div>
    <div class="actions"></div>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-12">

            <h2>{{ __('Apoio ao Cliente') }}</h2>
            <p>
                <b>{{ __('Email') }}:</b> <a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a><br>
                <b>{{ __('Telefone') }}:</b> <a href="tel:+351912026453">+351 912 026 453</a>
                <span style="color:#666;">{{ __('(chamada para a rede móvel nacional)') }}</span><br>
                <b>{{ __('Horário') }}:</b> {{ __('Segunda a Sexta, 9h–22h · Sábado, 9h–18h') }}
            </p>
            <p>{{ __('Pode também usar o') }} <a href="/apoioaocliente">{{ __('formulário de apoio ao cliente') }}</a>{{ __('.') }}</p>

            <h2>{{ __('Dados da empresa') }}</h2>
            <p>
                <b>{{ __('Denominação social') }}:</b> DFP Interiores, Unipessoal Lda<br>
                <b>{{ __('NIF / NIPC') }}:</b> 504074571<br>
                <b>{{ __('Capital social') }}:</b> 500.000,00 €<br>
                <b>{{ __('Sede') }}:</b> Rua José Francisco Fragoso, n.º 45, 7080-035 Vendas Novas, Portugal<br>
                <b>{{ __('Conservatória do Registo Comercial') }}:</b> {{ __('Vendas Novas, sob o número único de matrícula e de pessoa coletiva 504074571') }}
            </p>
            <p>
                <a href="https://www.google.com/maps/search/Rua+Jos%C3%A9+Francisco+Fragoso+45+Vendas+Novas"
                   target="_blank" rel="noopener">{{ __('Ver morada no Google Maps') }}</a>
            </p>

            <h2>{{ __('Reclamações e litígios') }}</h2>
            <p>
                <a href="https://www.livroreclamacoes.pt/inicio/" target="_blank" rel="noopener">{{ __('Livro de Reclamações Eletrónico') }}</a><br>
                <a href="/ajuda/resolucao-alternativa-litigios">{{ __('Resolução Alternativa de Litígios (RAL)') }}</a><br>
                <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">{{ __('Plataforma Europeia de Resolução de Litígios em Linha (ODR)') }}</a>
            </p>

            <h2>{{ __('Documentos úteis') }}</h2>
            <p>
                <a href="/ajuda/termos-e-condicoes">{{ __('Termos e Condições') }}</a> ·
                <a href="/ajuda/politica-privacidade">{{ __('Política de Privacidade') }}</a> ·
                <a href="/ajuda/politica-de-cookies">{{ __('Política de Cookies') }}</a> ·
                <a href="/ajuda/politica-de-envios">{{ __('Política de Envios e Entregas') }}</a> ·
                <a href="/ajuda/politica-de-devolucoes">{{ __('Política de Devoluções e Reembolsos') }}</a>
            </p>

        </div>
    </div>
</div>
@endsection
