@extends('layouts.app')

@section('title', 'Política de Cookies | DFP Interiores')

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
        <h1>Política de Cookies</h1>
    </div>
    <div class="actions"></div>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-12">

            <h2>1. O que são cookies</h2>
            <p>Cookies são pequenos ficheiros de texto que um sítio na Internet coloca no
            dispositivo do utilizador para guardar informação, nomeadamente sobre as suas
            preferências ou a sua atividade de navegação. São utilizadas também tecnologias
            equivalentes, como o armazenamento local do navegador (localStorage) e píxeis.</p>

            <h2>2. Base legal e consentimento</h2>
            <p>Nos termos do artigo 5.º da Lei n.º 41/2004 e do Regulamento (UE) 2016/679
            (RGPD), a DFP Interiores apenas utiliza cookies não estritamente necessários
            mediante o <b>consentimento prévio e expresso</b> do utilizador, recolhido
            através do banner apresentado na primeira visita. O consentimento pode ser
            retirado ou alterado a qualquer momento nesta página ou através do banner,
            com o mesmo grau de simplicidade com que foi concedido. As cookies estritamente
            necessárias não carecem de consentimento.</p>

            <h2>3. Categorias de cookies utilizadas</h2>
            <table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;">
                <thead>
                    <tr>
                        <th>Categoria</th><th>Finalidade</th><th>Consentimento</th><th>Prazo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Estritamente necessárias</td>
                        <td>Sessão, carrinho de compras, autenticação, segurança (CSRF),
                        registo da própria escolha de cookies.</td>
                        <td>Não exigível</td>
                        <td>Sessão a 12 meses</td>
                    </tr>
                    <tr>
                        <td>Preferências</td>
                        <td>Memorizar opções do utilizador (por exemplo, lista de desejos,
                        idioma, região).</td>
                        <td>Opcional</td>
                        <td>Até 12 meses</td>
                    </tr>
                    <tr>
                        <td>Estatísticas</td>
                        <td>Medição de audiência agregada para melhorar o sítio
                        (Google Analytics, via Google Tag Manager).</td>
                        <td>Opcional</td>
                        <td>Até 14 meses</td>
                    </tr>
                    <tr>
                        <td>Marketing</td>
                        <td>Publicidade personalizada e medição de campanhas
                        (Google Ads / Merchant Center, redes sociais).</td>
                        <td>Opcional</td>
                        <td>Até 12 meses</td>
                    </tr>
                </tbody>
            </table>
            <p style="font-size:13px;color:#666;">As cookies de estatísticas e de marketing
            só são ativadas após consentimento. Enquanto o utilizador não aceitar, os
            serviços de terceiros correspondentes não são carregados.</p>

            <h2>4. Cookies de terceiros</h2>
            <p>Quando consentidas, podem ser utilizadas cookies dos seguintes terceiros,
            sujeitas às respetivas políticas de privacidade: Google (Analytics, Tag Manager,
            Ads/Merchant Center), Meta/Facebook, TikTok, Pinterest.</p>

            <h2>5. Como gerir ou eliminar cookies</h2>
            <p>Pode alterar a sua escolha a qualquer momento através do banner de cookies.
            Pode ainda configurar o seu navegador para bloquear ou eliminar cookies
            (Chrome, Firefox, Safari, Edge disponibilizam essa opção nas definições de
            privacidade). O bloqueio de cookies estritamente necessárias pode afetar o
            funcionamento do sítio, designadamente o carrinho e a finalização de compras.</p>

            <h2>6. Contacto</h2>
            <p>Para questões sobre esta política ou sobre proteção de dados:
            <a href="mailto:contacto@dfpinteriores.com">contacto@dfpinteriores.com</a>.
            Consulte também a nossa
            <a href="/ajuda/politica-privacidade">Política de Privacidade</a>.</p>

        </div>
    </div>
</div>
@endverbatim
@endsection
