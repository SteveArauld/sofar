@extends('layouts.app')

@section('title', 'Contactos | DFP Interiores')

@push('scripts')
<script src="/assets/vue/cookies.js"></script>
<script src="/assets/modulos/ajuda.js"></script>
@endpush

@section('content')
@verbatim
<div class="container breadcrumb">
    <div>
        <a href="/">Início</a>
        <h1>Contactos</h1>
    </div>
    <div class="actions"></div>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-12">

            <h2>Apoio ao cliente</h2>
            <p>
                <b>E-mail:</b> <a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a><br>
                <b>Telefone:</b> <a href="tel:+351912026453">+351 912 026 453</a>
                <span style="color:#666;">(chamada para a rede móvel nacional)</span><br>
                <b>Horário:</b> Segunda a Sexta, 9h–22h · Sábado, 9h–18h
            </p>
            <p>Pode também usar o <a href="/apoioaocliente">formulário de apoio ao cliente</a>.</p>

            <h2>Dados da empresa</h2>
            <p>
                <b>Denominação social:</b> DFP Interiores, Unipessoal Lda<br>
                <b>NIF / NIPC:</b> 504074571<br>
                <b>Capital social:</b> 500.000,00 €<br>
                <b>Sede:</b> Rua José Francisco Fragoso, n.º 45, 7080-035 Vendas Novas, Portugal<br>
                <b>Conservatória do Registo Comercial:</b> Vendas Novas, sob o número único de
                matrícula e de pessoa coletiva 504074571
            </p>
            <p>
                <a href="https://www.google.com/maps/search/Rua+Jos%C3%A9+Francisco+Fragoso+45+Vendas+Novas"
                   target="_blank" rel="noopener">Ver morada no Google Maps</a>
            </p>

            <h2>Reclamações e litígios</h2>
            <p>
                <a href="https://www.livroreclamacoes.pt/inicio/" target="_blank" rel="noopener">Livro de Reclamações Eletrónico</a><br>
                <a href="/ajuda/resolucao-alternativa-litigios">Resolução Alternativa de Litígios (RAL)</a><br>
                <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">Plataforma Europeia de Resolução de Litígios em Linha (ODR)</a>
            </p>

            <h2>Documentos úteis</h2>
            <p>
                <a href="/ajuda/termos-e-condicoes">Termos e Condições</a> ·
                <a href="/ajuda/politica-privacidade">Política de Privacidade</a> ·
                <a href="/ajuda/politica-de-cookies">Política de Cookies</a> ·
                <a href="/ajuda/politica-de-envios">Política de Envios e Entregas</a> ·
                <a href="/ajuda/politica-de-devolucoes">Política de Devoluções e Reembolsos</a>
            </p>

        </div>
    </div>
</div>
@endverbatim
@endsection
