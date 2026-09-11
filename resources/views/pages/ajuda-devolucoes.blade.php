@extends('layouts.app')

@section('title', 'Política de Devoluções e Reembolsos | DFP Interiores')

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
        <h1>Política de Devoluções e Reembolsos</h1>
    </div>
    <div class="actions"></div>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-12">

            <h2>1. Direito de livre resolução (14 dias)</h2>
            <p>Nos termos do <b>Decreto-Lei n.º 24/2014, de 14 de fevereiro</b>, o cliente
            consumidor dispõe de um prazo de <b>14 dias de calendário</b>, a contar da data
            de receção do artigo, para resolver o contrato de compra e venda, <b>sem
            necessidade de indicar qualquer motivo</b>.</p>
            <p>Para exercer este direito, o cliente deve comunicar a sua decisão através de
            declaração inequívoca, por e-mail para
            <a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a>,
            indicando o número da encomenda e o(s) artigo(s) a devolver. Pode utilizar o
            modelo de livre resolução abaixo, embora não seja obrigatório.</p>

            <h2>2. Condições de devolução</h2>
            <ul>
                <li>Os artigos devem ser devolvidos completos, sem sinais de uso, na
                embalagem original e com todos os acessórios, manuais e etiquetas.</li>
                <li>A devolução deve ser efetuada no prazo de 14 dias após a comunicação
                da decisão de livre resolução.</li>
                <li>Artigos de fabrico à medida ou personalizados segundo especificações
                do cliente estão excluídos do direito de livre resolução, nos termos do
                artigo 17.º, n.º 1, do DL 24/2014.</li>
            </ul>

            <h2>3. Custos de devolução</h2>
            <p>No caso de livre resolução, os <b>custos diretos de devolução do artigo são
            suportados pelo cliente</b>. Caso o artigo apresente defeito, não conformidade
            ou erro de expedição imputável à DFP Interiores, <b>todos os custos de devolução
            são suportados pela DFP Interiores</b>.</p>

            <h2>4. Reembolso</h2>
            <p>Após receção do artigo devolvido e verificação do cumprimento das condições
            acima, a DFP Interiores reembolsa todos os pagamentos recebidos, incluindo os
            custos de entrega, no prazo máximo de <b>14 dias</b>. O reembolso é efetuado
            pelo mesmo meio de pagamento utilizado na compra, salvo acordo expresso em
            contrário. Se o pagamento tiver sido feito por referência Multibanco, será
            solicitado o IBAN do cliente para a transferência.</p>

            <h2>5. Garantia legal de conformidade</h2>
            <p>Todos os artigos beneficiam da <b>garantia legal de conformidade de 3 anos</b>
            para consumidores (Decreto-Lei n.º 84/2021). Em caso de falta de conformidade,
            o cliente tem direito à reposição da conformidade (reparação ou substituição),
            à redução do preço ou à resolução do contrato, nos termos da lei.</p>

            <h2>6. Como iniciar uma devolução</h2>
            <ol>
                <li>Envie e-mail para <a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a>
                com o número da encomenda e o motivo.</li>
                <li>Aguarde a confirmação e as instruções de envio.</li>
                <li>Embale o artigo em segurança e expeça para a morada indicada.</li>
                <li>Receberá o reembolso após a receção e validação do artigo.</li>
            </ol>

            <h2>7. Modelo de livre resolução</h2>
            <p style="border:1px solid #ddd;padding:12px;">
            À DFP Interiores, Unipessoal Lda, Rua José Francisco Fragoso, n.º 45,
            7080-035 Vendas Novas — contacto@dfpinteriores.com<br><br>
            Pela presente comunico que resolvo o contrato de compra e venda do(s)
            seguinte(s) bem(ns): __________________________<br>
            Encomendado em ___/___/______ · Recebido em ___/___/______<br>
            Nome do consumidor: __________________________<br>
            Morada do consumidor: __________________________<br>
            Data: ___/___/______ · Assinatura (se em papel): __________________________
            </p>

            <h2>8. Reclamações e resolução de litígios</h2>
            <p>O cliente pode apresentar reclamação através do
            <a href="https://www.livroreclamacoes.pt/inicio/" target="_blank" rel="noopener">Livro de Reclamações Eletrónico</a>
            e recorrer às entidades de
            <a href="/ajuda/resolucao-alternativa-litigios">resolução alternativa de litígios</a>,
            ou à plataforma europeia de
            <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">Resolução de Litígios em Linha (ODR)</a>.</p>

        </div>
    </div>
</div>
@endverbatim
@endsection
