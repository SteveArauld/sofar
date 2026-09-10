@extends('layouts.app')

@section('title', 'Resolução Alternativa de Litígios | DFP Interiores')

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
                                                <h1>Resolução alternativa de disputa</h1>
                        </div>
    <div class='actions'>
        
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
                
            <p>Em caso de litígio, informamos que o cliente pode recorrer às entidades de resolução alternativa de litígios de consumo abaixo indicadas.</p>

            <p>Direção-Geral do Consumidor<br>
                <a href='https://www.consumidor.gov.pt/'>https://www.consumidor.gov.pt/</a></p>

            <p>Resolução de Litígios Online<br>
                <a href='https://ec.europa.eu/consumers/odr/main/index.cfm?event=main.home.chooseLanguage'>https://ec.europa.eu/consumers/odr/main/index.cfm?event=main.home.chooseLanguage</a></p>

            <p>Centro Nacional de Informação e Arbitragem de Conflitos de Consumo<br>
                <a href='https://www.cniacc.pt/pt'>https://www.cniacc.pt/pt</a></p>

            <p>Centro de Arbitragem de Conflitos de Consumo do Distrito de Coimbra<br>
                <a href='https://cacrc.pt/'>https://cacrc.pt/</a></p>

            <p>Centro de Arbitragem de Conflitos de Consumo de Lisboa<br>
                <a href='http://www.centroarbitragemlisboa.pt/'>http://www.centroarbitragemlisboa.pt/</a></p>

            <p>Centro de Arbitragem de Conflitos de Consumo da Região Autónoma da Madeira<br>
                <a href='https://www.madeira.gov.pt/cacc/'>https://www.madeira.gov.pt/cacc/</a></p>

            <p>Centro de Informação e Arbitragem do Porto<br>
                <a href="https://www.cicap.pt/">https://www.cicap.pt/</a></p>

            <p>Centro de Informação e Arbitragem do Vale do Ave<br>
                <a href='https://www.triave.pt/'>https://www.triave.pt/</a></p>

            <p>Centro de Informação e Arbitragem do Vale do Cávado<br>
                <a href="https://www.ciab.pt/pt/">https://www.ciab.pt/pt/</a></p>

            <p>Centro de Informação, Mediação e Arbitragem do Algarve<br>
                <a href='https://www.consumidoronline.pt/pt/'>https://www.consumidoronline.pt/pt/</a></p>
                    
        </div>
    </div>
</div>
@endverbatim
@endsection
