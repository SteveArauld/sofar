@extends('layouts.app')

@section('title', 'Política de Envios e Entregas | DFP Interiores')

@push('scripts')
<script src="/assets/vue/cookies.js"></script>
<script src="/assets/modulos/ajuda.js"></script>
@endpush

@section('content')
@verbatim
<div class="container breadcrumb">
    <div>
        <a href="/">Início</a>
        <a href="/">Ajuda</a>
        <h1>Política de Envios e Entregas</h1>
    </div>
    <div class="actions"></div>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-12">

            <h2>1. Zona de entrega</h2>
            <p>A DFP Interiores efetua entregas <b>exclusivamente em Portugal</b> (Portugal
            Continental e Regiões Autónomas dos Açores e da Madeira). Não são realizadas
            entregas para fora do território nacional português.</p>

            <h2>2. Prazos de entrega</h2>
            <p>Para os artigos disponíveis em stock:</p>
            <ul>
                <li><b>Preparação da encomenda:</b> 1 a 2 dias úteis.</li>
                <li><b>Transporte:</b> 1 a 2 dias úteis, para todo o Portugal.</li>
            </ul>
            <p style="font-size:16px;">
                <b>Entrega em 2 a 4 dias úteis (preparação 1-2 dias + transporte 1-2 dias).</b>
            </p>
            <p>Os artigos por encomenda ou de fabrico à medida têm um prazo indicado na
            respetiva ficha de produto, que acresce ao prazo de transporte acima.
            Os prazos são contados em dias úteis, a partir da confirmação do pagamento.</p>

            <h2>3. Custos de envio</h2>
            <p><b>Envio gratuito para todo o Portugal</b>, sem valor mínimo de encomenda.
            O custo de transporte apresentado ao cliente antes da finalização da compra é
            sempre <b>0,00 €</b>.</p>

            <h2>4. Receção da encomenda</h2>
            <p>No momento da entrega, o cliente deve verificar o estado da embalagem e dos
            artigos. Caso detete danos, deve recusar a entrega ou registar a ocorrência no
            documento de transporte e contactar-nos de imediato para
            <a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a>.</p>

            <h2>5. Entregas falhadas</h2>
            <p>Se não for possível concluir a entrega por ausência do destinatário ou dados
            de morada incorretos, a transportadora efetuará novo contacto para reagendamento.
            Após tentativas de entrega sem sucesso, a encomenda regressa aos nossos armazéns
            e a DFP Interiores contactará o cliente para nova expedição.</p>

            <h2>6. Contacto</h2>
            <p>Para qualquer questão sobre a sua entrega:
            <a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a> ·
            <a href="tel:+351912026453">+351 912 026 453</a> (chamada para a rede móvel nacional).</p>

        </div>
    </div>
</div>
@endverbatim
@endsection
