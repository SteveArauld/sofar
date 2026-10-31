@extends('layouts.app')

@section('title', __('Política de Envios e Entregas').' | DFP Interiores')

@push('scripts')
<script src="/assets/vue/cookies.js"></script>
<script src="/assets/modulos/ajuda.js"></script>
@endpush

@section('content')
<div class="container breadcrumb">
    <div>
        <a href="/">{{ __('Início') }}</a>
        <a href="/">{{ __('Ajuda') }}</a>
        <h1>{{ __('Política de Envios e Entregas') }}</h1>
    </div>
    <div class="actions"></div>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-12">

            <h2>{{ __('1. Zona de entrega') }}</h2>
            <p>{{ __('A DFP Interiores efetua entregas exclusivamente em Portugal (Portugal Continental e Regiões Autónomas dos Açores e da Madeira). Não são realizadas entregas para fora do território nacional português.') }}</p>

            <h2>{{ __('2. Prazos de entrega') }}</h2>
            <p>{{ __('Para os artigos disponíveis em stock:') }}</p>
            <ul>
                <li><b>{{ __('Preparação da encomenda: 1 dia útil.') }}</b></li>
                <li><b>{{ __('Transporte: até 2 dias úteis, para todo o Portugal.') }}</b></li>
            </ul>
            <p style="font-size:16px;">
                <b>{{ __('Entrega em 1 a 3 dias úteis (preparação: 1 dia útil).') }}</b>
            </p>
            <p>{{ __('Os prazos são contados em dias úteis, a partir da confirmação do pagamento. Envio disponível em Portugal Continental e nas Regiões Autónomas dos Açores e da Madeira.') }}</p>

            <h2>{{ __('3. Custos de envio') }}</h2>
            <p>{{ __('Envio gratuito para todo o Portugal, sem valor mínimo de encomenda. O custo de transporte apresentado ao cliente antes da finalização da compra é sempre 0,00 €.') }}</p>

            <h2>{{ __('4. Receção da encomenda') }}</h2>
            <p>{{ __('No momento da entrega, o cliente deve verificar o estado da embalagem e dos artigos. Caso detete danos, deve recusar a entrega ou registar a ocorrência no documento de transporte e contactar-nos de imediato.') }}
            <a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a>.</p>

            <h2>{{ __('5. Entregas falhadas') }}</h2>
            <p>{{ __('Se não for possível concluir a entrega por ausência do destinatário ou dados de morada incorretos, a transportadora efetuará novo contacto para reagendamento. Após tentativas de entrega sem sucesso, a encomenda regressa aos nossos armazéns e a DFP Interiores contactará o cliente para nova expedição.') }}</p>

            <h2>{{ __('6. Contacto') }}</h2>
            <p>{{ __('Para qualquer questão sobre a sua entrega:') }}
            <a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a> ·
            <a href="tel:+351912026453">+351 912 026 453</a> {{ __('(chamada para a rede móvel nacional)') }}</p>

        </div>
    </div>
</div>
@endsection
