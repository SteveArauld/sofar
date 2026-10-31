<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) === 'it' ? 'it-IT' : 'pt-PT' }}">


<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<head>
    <meta name="theme-color" content="#000000">
    <script></script>

    <link href="/assets/css/normalize.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="googlebot" content="index, follow">
    <meta name="robots" content="INDEX,FOLLOW">
    <meta name="theme-color" content="#e5007d">
    <link rel="dns-prefetch" href="http://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link rel="dns-prefetch" href="http://connect.facebook.net/">
    
    <link rel="dns-prefetch" href="http://cdn.jsdelivr.net/">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">





    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="alternate" href="/" hreflang="pt-pt" />
    {{-- Favicons DFP Interiores --}}
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon-32.png?v=3">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/images/favicon-192.png?v=3">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/favicon-180.png?v=3">
    <link rel="shortcut icon" href="/assets/favicon.ico?v=3">
    <meta name="msapplication-TileColor" content="#2e3a4a">
    <meta name="theme-color" content="#2e3a4a">
    <style>
        /* Badge favoris */
        .icons .wishlist-link { position: relative; }
        .icons .wish-badge {
            position: absolute; top: -4px; right: -6px; background: #e5007d; color: #fff;
            font-size: 11px; line-height: 1; padding: 3px 5px; border-radius: 999px; font-weight: 700;
        }
        /* Toast */
        .shop-toast {
            position: fixed; z-index: 99999; left: 50%; bottom: 28px; transform: translateX(-50%) translateY(20px);
            background: #1a1a1a; color: #fff; padding: 12px 20px; border-radius: 8px; font-size: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,.25); opacity: 0; transition: .25s ease; pointer-events: none; max-width: 90vw;
        }
        .shop-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        a.Wishlist.is-active svg { fill: #e5007d; stroke: #e5007d; }
    </style>



    <!-- Place this data between the <head> tags of your website -->
    <title>@yield('title', 'DFP Interiores')</title>
    <meta name="description"
        content="{{ __('DFP Interiores: Especialistas em Sofás e Mobiliário para a sua casa. Qualidade, design moderno e os melhores preços do mercado. Entrega rápida e garantida. Renove o seu lar hoje!') }}" />
    <script>
        window.APP_LOCALE = @json($currentLocale ?? app()->getLocale());
        window.DFP_I18N = {
            in_stock: @json(__('Em stock')),
            in_stock_upper: @json(__('EM STOCK')),
            to_order: @json(__('Por encomenda')),
            to_order_upper: @json(__('POR ENCOMENDA')),
            available: @json(__('Disponível')),
            cart_empty: @json(__('O seu carrinho está vazio.')),
            optionals: @json(__('Opcionais')),
            qty_unavailable: @json(__('Qtd. indisponível')),
            no_results: @json(__('Não foram encontrados resultados para')),
            categories: @json(__('Categorias')),
            search_history: @json(__('Histórico de pesquisa')),
            account: @json(__('Conta')),
            favorites: @json(__('Favoritos')),
            cart: @json(__('Carrinho')),
            close: @json(__('Fechar')),
            total: @json(__('Total')),
            view_cart: @json(__('Ver carrinho')),
            checkout: @json(__('Finalizar encomenda')),
            continue_shopping: @json(__('Continuar a comprar')),
            email_placeholder: @json(__('O seu e-mail')),
            subscribe: @json(__('Subscrever')),
            newsletter_ok: @json(__('Registo concluído com sucesso!')),
            search_placeholder: @json(__('Procurar produtos...'))
        };
        (function () {
            var loc = window.APP_LOCALE || 'pt';
            var skip = /^(assets|media|images|videos|img|css|js|storage|fonts|favicon|feeds|up)(\/|$)/;
            function withLocale(href) {
                if (!href || href.charAt(0) !== '/' || href.indexOf('//') === 0) return href;
                var path = href.split('?')[0], q = href.indexOf('?') >= 0 ? href.slice(href.indexOf('?')) : '';
                if (skip.test(path.replace(/^\//, ''))) return href;
                if (/^\/(pt|it)(\/|$)/.test(path)) return href;
                if (path === '/') return '/' + loc + q;
                return '/' + loc + path + q;
            }
            document.addEventListener('click', function (e) {
                var a = e.target.closest && e.target.closest('a[href^="/"]');
                if (!a || a.hasAttribute('data-no-locale')) return;
                var next = withLocale(a.getAttribute('href'));
                if (next !== a.getAttribute('href')) a.setAttribute('href', next);
            }, true);
        })();
    </script>
    <style>
        /* Switcher PT|IT — couleurs claires sur header sombre, sans casser .icons */
        .Header .icons { display: inline-flex; align-items: center; flex-wrap: nowrap; }
        .lang-switch {
            display: inline-flex;
            align-items: center;
            gap: 0;
            margin: 0 10px 0 0;
            padding: 0;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .06em;
            line-height: 1;
            white-space: nowrap;
            flex: 0 0 auto;
            vertical-align: middle;
        }
        .lang-switch a {
            display: inline-block !important;
            margin: 0 !important;
            padding: 6px 8px !important;
            color: #ffffffb8 !important;
            text-decoration: none !important;
            border: 1px solid transparent;
            border-radius: 3px;
            width: auto !important;
            text-align: center;
        }
        .lang-switch a:hover { color: #fff !important; border-color: rgba(255,255,255,.35); }
        .lang-switch a.is-active {
            color: #e5007d !important;
            border-color: #e5007d;
            background: rgba(229,0,125,.12);
        }
        .lang-switch .sep { color: rgba(255,255,255,.35); padding: 0 1px; font-weight: 400; }
        .MenuMobile .head { position: relative; }
        .lang-switch-mobile {
            position: absolute;
            right: 85px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 6;
            margin: 0;
            font-size: 11px;
        }
        .lang-switch-mobile a { color: #fff !important; padding: 4px 5px !important; }
        .lang-switch-mobile a.is-active {
            color: #fff !important;
            border-color: rgba(255,255,255,.75);
            background: rgba(255,255,255,.12);
        }
        .lang-switch-mobile .sep { color: rgba(255,255,255,.45); }
    </style>


    <meta itemprop="image" content="/social.jpg">
    <meta name="twitter:image:src" content="/Mocial.jpg">
    <meta property="og:image" content="/social.jpg" />

    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="DFP Interiores">
    <meta itemprop="description"
        content="DFP Interiores: Especialistas em Sofás e Mobiliário para a sua casa. Qualidade, design moderno e os melhores preços do mercado. Entrega rápida e garantida. Renove o seu lar hoje!">


    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="DFP Interiores">
    <meta name="twitter:description"
        content="DFP Interiores: Especialistas em Sofás e Mobiliário para a sua casa. Qualidade, design moderno e os melhores preços do mercado. Entrega rápida e garantida. Renove o seu lar hoje!">
    <meta name="twitter:creator" content="  @author_handle">


    <!-- Open Graph data -->
    <meta property="og:title" content="DFP Interiores" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="" />

    <meta property="og:description" content="" />
    <meta property="og:site_name" content="DFP Interiores" />



    <!---->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>



    <link href="/assets/all.css" rel="stylesheet">



    <link href="/assets/css/pt/global.css?t=1778100473?ssss" rel="stylesheet">
    @stack('mods-css')



    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-18390388389"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'AW-18390388389');
    </script>



    <script>
        var root = "index.html";
        let logged = "false";
        var Artigo = {};
    </script>





    <link href="/assets/css/pt/feira.css?id=2" rel="stylesheet">
    <link href="/assets/css/shop.css?v=16" rel="stylesheet">


    <style>
        [v-cloak] {
            display: none;
        }
    </style>



@stack('head')
@if(!empty($organizationSchema))
<script type="application/ld+json">@json($organizationSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)</script>
@endif
</head>

<body>
    @verbatim


        <div class="whatsapp">

            <a target="_blank" id="BottomWhatsapp"
                onclick="window.open('https://api.whatsapp.com/send?phone=351912026453', 'newwindow', 'width=632,height=750');"><img
                    src="/assets/whatsapp.png" alt='Whatsapp Logo'></a>
        </div>
        <style>
            .whatsapp {
                position: fixed;
                bottom: 5px;
                right: 20px;
                width: auto;
                overflow: hidden;
                z-index: 999999;
                padding-top: 10px;
                padding-left: 10px;
                padding-bottom: 10px;
            }

            .whatsapp img {
                animation-name: bounce;
                animation-timing-function: ease;
                animation-duration: 2s;
                animation-delay: 2s;
                animation-iteration-count: infinite;
                width: 60px;
                height: 70px;
                padding-top: 10px;
            }

            .Balao {
                display: none;
                opacity: 0;
                position: relative;
                display: inline-block;
                max-width: 30em;
                margin-left: 8px;
                margin-right: 5px;
                background-color: #f9f9f9;
                padding: 1em 1.5em;
                font-size: 1em;
                border-radius: 1rem !important;
                box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.3), 0 0.0625rem 0.125rem rgba(0, 0, 0, 0.2);
                animation-name: hideandshow;
                animation-timing-function: ease;
                animation-duration: 60s;
                animation-delay: 6s;
                animation-iteration-count: infinite;
                cursor: default !important;
            }

            .Balao::before {
                content: '';
                position: absolute;
                width: 0;
                height: 0;
                bottom: 1.2em;
                transform: rotate(90deg);
                left: 97%;
                border: .75rem solid transparent;
                border-top: none;
                border-bottom-color: #f9f9f9;
                filter: drop-shadow(0 -0.15rem 0.0625rem rgba(0, 0, 0, 0.1));
            }

            .Balao .Fechar {
                border-radius: 50% !important;
                background-color: #000;
                color: #fff;
                padding-left: 6px;
                line-height: 17px;
                height: 20px;
                width: 20px;
                position: absolute;
                top: -8px;
                left: -9px;
            }
        </style>


        <div id="_Global" class="Site" v-cloak>
            <div class='site'>
                <div @click="offside.toggle('all')" :class="{ active: offside.overlay }" class="overlay"></div>
                <div class="offside">
                    <div :class="{ open: offside.mobilemenu }" class="content MobileMenu">
                        <div>
                            @endverbatim
                            @include('partials.nav-menu-mobile')
                            @verbatim
                        </div>
                        <div class='FootMenu'>
                            @endverbatim
                            <a @click="offside.toggle('cliente')" class='fa fa-user'><span>{{ __('CONTA') }}</span></a>
                            <a href="{{ locale_url('wishlist') }}" class='fa fa-heart'><span>{{ __('FAVORITOS') }}</span></a>
                            <a href="{{ locale_url('lojas') }}" class='fa fa-map-marker'><span>{{ __('LOJAS') }}</span></a>
                            <a @click="openApoioCliente" class='fa fa-phone'><span>{{ __('APOIO AO CLIENTE') }}</span></a>
                            @verbatim
                        </div>
                    </div>
                    <div :class="{ open: offside.cliente }" class="content Cliente">
                        <div>
                            <a @click="offside.toggle('all')" class='close'><img src="/assets/icons/times-light.svg"
                                    alt="Fechar"></a>
                            <div class="title">Cliente</div>
                            <div class="LoginCliente">
                                <b>Faça login</b>
                                <form class="form" method="post" id="OffsideLoginCliente"
                                    v-on:submit.prevent="doSideLogin">
                                    <label for="sideUsername">Telemovel</label>
                                    <input id="sideUsername" v-model="sidelogin.username" type="text" maxlength="9">
                                    <span v-if="sidelogin.errors?.username" class="error">${ sidelogin.errors.username[0]
                                        }</span>
                                    <label for="sidePassword">Password</label>
                                    <input id="sidePassword" v-model="sidelogin.password" name="password"
                                        type="password" autocomplete="on">
                                    <span v-if="sidelogin.errors?.password" class="error">${ sidelogin.errors.password[0]
                                        }</span>
                                    <div v-if="sidelogin.error" class="erro">${ sidelogin.error }</div>
                                    <button class="btn btn-primary">Entrar</button>

                                    <a href="#" class="LinkRecuperarSenha">Recuperar a senha</a>
                                </form>

                                <div class="RegistarCliente">
                                    <b>Ainda não é cliente?</b>
                                    <a href="#" class="btn btn-default MostraRegisto">Registe aqui</a>
                                </div>
                            </div>
                            <div class="RegistoCliente">
                                <b>Registo de novo cliente</b>
                                <form class="form" method="post" id="OffsideRegistoCliente"
                                    v-on:submit.prevent="doSideRegisto">
                                    <label for="registo_nome">Nome *</label>
                                    <input id="registo_nome" v-model="sideRegisto.nome" name="nome" type="text"
                                        data-msg-required="Insira o seu nome e apelido"
                                        data-msg-wordcount="Falta o apelido">
                                    <span v-if="sideRegisto.errors?.nome" class="error">${ sideRegisto.errors.nome
                                        }</span>
                                    <label for="registo_email">Email</label>
                                    <input id="registo_email" v-model="sideRegisto.email" name="email" type="text"
                                        data-msg-required="Insira o seu email" data-msg-email="Email inválido">
                                    <span v-if="sideRegisto.errors?.email" class="error">${ sideRegisto.errors.email
                                        }</span>
                                    <label for="registo_username">Telemovel *</label>
                                    <input id="registo_username" v-model="sideRegisto.telemovel" name="telemovel"
                                        type="text" data-msg-required="Insira o seu telemóvel"
                                        data-msg-minlength="Número inválido" data-msg-number="Número inválido">
                                    <span v-if="sideRegisto.errors?.telemovel" class="error">${
                                        sideRegisto.errors.telemovel }</span>
                                    <label for="registo_password">Senha *</label>
                                    <input id="registo_password" v-model="sideRegisto.password" name="password"
                                        type="password" autocomplete="on"
                                        data-msg-required="Escolha uma senha ao seu gosto"
                                        data-msg-minlength="Tem que ter pelo menos 6 caracteres">
                                    <span v-if="sideRegisto.errors?.password" class="error">${
                                        sideRegisto.errors.password }</span>
                                    <button class="btn btn-primary">Registar</button>
                                    <div class="erros"></div>
                                </form>
                                <div class="RegistarCliente">
                                    <b>Já é cliente?</b>
                                    <a href="#" class="btn btn-default MostraLogin">Entre na sua conta</a>
                                </div>
                            </div>

                            <div class="RecuperarSenha">
                                <div class="success" v-if="recuperarSenha?.success">${ recuperarSenha.success }</div>
                                <form v-else method="post" id="OffsideRecuperarSenha"
                                    v-on:submit.prevent="doRecuperarSenha">
                                    <label>Nº de telemóvel *</label>
                                    <input v-model="recuperarSenha.username" type="text" maxlength="9">
                                    <span v-if="recuperarSenha.errors?.username" class="error">${
                                        recuperarSenha.errors.username[0] }</span>

                                    <div class="erro" v-if="recuperarSenha?.error">${ recuperarSenha.error[0] }</div>
                                    <button class="btn btn-primary">Recuperar</button>
                                </form>

                                <a href="#" class="LinkRetroceder">Retroceder</a>
                            </div>

                            <div class="RecuperarSenha_codigo" style="display: none;">

                            </div>
                        </div>
                    </div>
                    <div :class="{ open: offside.procurar }" class="content Procurar">
                        <div class="ProcurarInput">
                            <input v-model='searchField' @keyup='dooSearch()' type='text' name='procurar'
                                id='mobileProcurarField' placeholder='ex: Sofá chaise long...'>
                            <a @click="searchGo"><img src="/assets/icons/search-light.svg" alt="Procurar"></a>
                        </div>
                        <div class="caixaProcura" v-if="searchResults.results" ref="caixaProcura">
                            <div class="resultados">
                                <div v-if="searchResults.results.length == 0 && searchField?.length > 4"
                                    class="semResultados">
                                    <span>Não foram encontrados resultados para ""</span>
                                </div>
                                <div v-for="item in searchResults.results" class="item">
                                    <a :href="'/catalogo/' + item.link">
                                        <img :src="'/images/150-150-R/' + item.image_link">
                                        <span class="nome">${ item.title.substring(0, 90).concat('...') }</span>
                                        <span class="foot">
                                            <span class="disponibilidade"
                                                :class="item.availability == 'INSTOCK' ? 'available' : 'to-order'">${item.availability
                                                == 'INSTOCK' ? "EM STOCK" : "POR ENCOMENDA"}</span>
                                            <div><span class="preco">
                                                    <span class="antes">${ item.old_price ? item.old_price+"€" : ""
                                                        }</span>
                                                    ${ item.price }€
                                                </span></div>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div :class="{ open: offside.carrinho }" class="content Carrinho">
                        <div class="mc-body">
                            <a @click="offside.toggle('all')" href="#" class='close'><img
                                    src="/assets/icons/times-light.svg" alt="Fechar"></a>
                            <div class="title">Carrinho</div>

                            <div class="MiniCarrinho">

                                <div v-if="!Cart?.items || Cart.items.length == 0" class="mc-empty">
                                    O seu carrinho está vazio.
                                </div>

                                <div v-for="item in Cart?.items" class="item">
                                    <div class="img">
                                        <img v-if="item.preco > 0" :src="'/images/70-70/' + item?.images?.[0]"
                                            :alt="item.nome">
                                        <img v-else src="images/70-70/Desconto.jpg" alt="Desconto">
                                    </div>
                                    <div class="content">

                                        <div class="nome">
                                            <a :href="'/' + item.link">${ item.nome }</a>
                                        </div>

                                        <small style="font-size: 12px; color: #666;" v-if="item.variation_name">
                                            ${ item.variation_name }
                                        </small>

                                        <div v-if="item.Customtxt" class="custom">
                                            <div class="Custom">${ item.Customtxt }</div>
                                        </div>
                                        <div v-if="item.valor_opcionais" class="extra">
                                            Opcionais: ${ item.valor_opcionais }€
                                        </div>
                                        <small v-if="item.stock > item.qtd_pretendida" class='alertaStockIndisponivel'
                                            style="font-size: 10px; color: red; padding-left: 80px;">Qtq.
                                            indisponível</small>


                                    </div>

                                    <div class="preco">
                                        <b>
                                            ${ item.qtd * (Number(item.preco) + Number(item.valor_opcionais)) }€
                                        </b>
                                        <div class="qtd_update">
                                            <button @click="minusQtd(item)">
                                                <svg v-if="item.qtd == 1" xmlns="http://www.w3.org/2000/svg"
                                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="lucide lucide-trash2-icon lucide-trash-2">
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                </svg>
                                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="24"
                                                    height="24" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="lucide lucide-minus-icon lucide-minus">
                                                    <path d="M5 12h14" />
                                                </svg>
                                            </button>
                                            <span type="text" class="qtd">${ item.qtd }</span>
                                            <button @click="plusQtd(item)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="lucide lucide-plus-icon lucide-plus">
                                                    <path d="M5 12h14" />
                                                    <path d="M12 5v14" />
                                                </svg>
                                            </button>
                                        </div>


                                    </div>
                                </div>

                            </div><!-- /.MiniCarrinho -->
                        </div><!-- /.mc-body -->

                        <div v-if="Cart.total > 0" class="mc-foot">
                            <div class="totais">
                                <b>Total</b>
                                <span class="CarrinhoTotalValor">${ Cart.total.toFixed(2) }€</span>
                            </div>
                            <div class="RegistarCliente">
                                <a href="/carrinho" class="btn btn-primary">Ver carrinho</a>
                                <a href="/carrinho/checkout" class="btn btn-primary">Finalizar encomenda</a>
                                <a @click="offside.toggle('all')" class="btn btn-default closebtn">Continuar a comprar</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="Header">
                    <div class='menu-desktop Desktop d-none d-sm-block'>



                        <div class="container">

                            <div class="d-flex justify-content-between">
                                <div class="logo">
                                    @endverbatim
                                    <a href="{{ locale_url() }}"><img alt="DFP Interiores" src="/assets/logos/logo-nav.png"
                                            style="width: 100%;"></a>
                                    @verbatim
                                </div>

                                @endverbatim
                                <a href="{{ locale_url('lojas') }}" style="display: inline-flex;">
                                    <div class="descubraLojas">
                                        <img src="/assets/svg/pink_location.svg">
                                        <span>{{ __('DESCUBRA AS NOSSAS LOJAS') }}</span>
                                    </div>
                                </a>
                                @verbatim

                                <div id='_Search' class="search">
                                    <div class="ProcuraDesktop Procurar">
                                        @endverbatim
                                        <input type="text" placeholder="{{ __('Procurar produtos...') }}" value=""
                                            v-model="searchField" v-on:keyup="dooSearch()" v-on:focus="openSearchBox"
                                            v-on:keyup.enter="searchGo" ref="campoProcura">
                                        @verbatim
                                        <a @click="searchGo"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-search-icon lucide-search">
                                                <path d="m21 21-4.34-4.34" />
                                                <circle cx="11" cy="11" r="8" />
                                            </svg></a>
                                    </div>
                                    <div class="caixaProcura" v-if="searchBox" ref="caixaProcura">
                                        <div class="sugestoes">
                                            <div v-if="Object.keys(searchResults?.sugestions).length > 0">
                                                <a v-for="v in searchResults.sugestions" :href="'/' + (appLocale||'pt') + '/procura/' + v"
                                                    class="btn" :key="v">${ v }</a>
                                            </div>
                                        </div>
                                        <div class="categorias">
                                            <label
                                                v-if="Object.keys(searchResults?.categorias).length > 0">Categorias</label>
                                            <a :href="'/' + (appLocale||'pt') + '/' + cat.seo"
                                                v-for="cat in searchResults.categorias">${cat.name}</a>
                                        </div>
                                        <div class="historico">
                                            <div v-if="searchResults.history?.length > 0">
                                                <label>Histórico de pesquisa</label>
                                                <a v-for="term in searchResults?.history" :href="'/' + (appLocale||'pt') + '/procura/' + term"
                                                    :key="term" class="btn">${ term }</a>
                                            </div>
                                        </div>
                                        <div class="resultados">
                                            <div v-if="searchResults.results?.length == 0" class="semResultados">
                                                <span>Não foram encontrados resultados para ""</span>
                                            </div>
                                            <div v-for="item in searchResults?.results" class="item">
                                                <a :href="'/' + (appLocale||'pt') + '/' + item.link">
                                                    <img :src="'/images/150-150/' + item.image_link">
                                                    <span class="nome">${ item.title.substring(0, 90).concat('...')
                                                        }</span>
                                                    <span class="foot">
                                                        <span class="disponibilidade"
                                                            :class="item.availability == 'INSTOCK' ? 'available' : 'available'">${item.availability
                                                            == 'INSTOCK' ? (i18n.in_stock||'Em stock') : (i18n.available||'Disponível')}</span>
                                                        <span class="preco">
                                                            <span class="antes">${ item.old_price ? item.old_price+"€" :
                                                                "" }</span>
                                                            ${ item.price }€
                                                        </span>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="icons">
                                    @endverbatim
                                    <nav class="lang-switch" aria-label="{{ __('Idioma') }}">
                                        <a href="{{ $localeUrls['pt'] ?? locale_url('', 'pt') }}" class="{{ ($currentLocale ?? 'pt') === 'pt' ? 'is-active' : '' }}" hreflang="pt" data-no-locale>PT</a>
                                        <span class="sep">|</span>
                                        <a href="{{ $localeUrls['it'] ?? locale_url('', 'it') }}" class="{{ ($currentLocale ?? 'pt') === 'it' ? 'is-active' : '' }}" hreflang="it" data-no-locale>IT</a>
                                    </nav>
                                    @verbatim
                                    <a @click="openApoioCliente"><img src="/assets/svg/apoio_cliente.svg"><span>Apoio
                                            Cliente</span></a>
                                    @endverbatim
                                    <a href="{{ locale_url('cliente') }}" class="_OpenCliente"><img
                                            src="/assets/svg/pink_cliente.svg"><span>{{ $authUser ? \Illuminate\Support\Str::of($authUser->name)->before(' ')->limit(12, '') : __('Conta') }}</span></a>
                                    <a href="{{ locale_url('wishlist') }}" class="wishlist-link"><img
                                            src="/assets/svg/pink_wishlist.svg"><span>{{ __('Favoritos') }}</span>@if ($wishCount)<small class="wish-badge">{{ $wishCount }}</small>@endif</a>
                                    @verbatim
                                    <a @click="offside.toggle('carrinho')" class="OpenCarrinho cart">
                                        <img src="/assets/svg/cart.svg">
                                        <small v-if="Cart.items && Cart.items.length">${ Cart.items.length }</small>
                                        <span>Carrinho</span>
                                    </a>
                                </div>
                            </div>


                        </div>
                        <div class="NavBar">
                            <div class="container">
                                <div class="MainMenuDesktop">
                                    @endverbatim
                                    @include('partials.nav-menu-desktop')
                                    @verbatim
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="MenuMobile">
                        <div class='head'>
                            @endverbatim
                            <nav class="lang-switch lang-switch-mobile" aria-label="{{ __('Idioma') }}">
                                <a href="{{ $localeUrls['pt'] ?? locale_url('', 'pt') }}" class="{{ ($currentLocale ?? 'pt') === 'pt' ? 'is-active' : '' }}" hreflang="pt" data-no-locale>PT</a>
                                <span class="sep">|</span>
                                <a href="{{ $localeUrls['it'] ?? locale_url('', 'it') }}" class="{{ ($currentLocale ?? 'pt') === 'it' ? 'is-active' : '' }}" hreflang="it" data-no-locale>IT</a>
                            </nav>
                            @verbatim
                            <a @click="offside.toggle('mobilemenu')" :class="{ 'open': offside.mobilemenu }"
                                class='Buttons OpenMenu'>
                                <svg fill="#FFFFFF" width="25px" height="25px" viewBox="0 0 32 32" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M0.844 6.050c-0.256-0.256-0.381-0.581-0.381-0.975s0.125-0.719 0.381-0.975 0.581-0.381 0.975-0.381h28.512c0.394 0 0.719 0.125 0.975 0.381s0.381 0.581 0.381 0.975-0.125 0.719-0.381 0.975-0.581 0.381-0.975 0.381h-28.512c-0.394 0-0.719-0.125-0.975-0.381zM31.306 14.963c0.256 0.256 0.381 0.581 0.381 0.975s-0.125 0.719-0.381 0.975-0.581 0.381-0.975 0.381h-28.512c-0.394 0-0.719-0.125-0.975-0.381s-0.381-0.581-0.381-0.975 0.125-0.719 0.381-0.975 0.581-0.381 0.975-0.381h28.512c0.394 0 0.719 0.125 0.975 0.381zM31.306 25.819c0.256 0.256 0.381 0.581 0.381 0.975s-0.125 0.719-0.381 0.975-0.581 0.381-0.975 0.381h-28.512c-0.394 0-0.719-0.125-0.975-0.381s-0.381-0.581-0.381-0.975 0.125-0.719 0.381-0.975 0.581-0.381 0.975-0.381h28.512c0.394 0 0.719 0.131 0.975 0.381z">
                                    </path>
                                </svg>
                            </a>
                            <a href="/" class='logo'>
                                <img src="/assets/logos/logo-nav.png" alt="DFP Interiores">
                            </a>
                            <a @click="offside.toggle('procurar')" class='Buttons OpenProcura'>
                                <svg width="28px" height="28px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.7955 15.8111L21 21M18 10.5C18 14.6421 14.6421 18 10.5 18C6.35786 18 3 14.6421 3 10.5C3 6.35786 6.35786 3 10.5 3C14.6421 3 18 6.35786 18 10.5Z" />
                                </svg>
                            </a>

                            <a @click="offside.toggle('carrinho')" class='Buttons OpenCarrinho'>
                                <svg width="28px" height="28px" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6.29977 5H21L19 12H7.37671M20 16H8L6 3H3M9 20C9 20.5523 8.55228 21 8 21C7.44772 21 7 20.5523 7 20C7 19.4477 7.44772 19 8 19C8.55228 19 9 19.4477 9 20ZM20 20C20 20.5523 19.5523 21 19 21C18.4477 21 18 20.5523 18 20C18 19.4477 18.4477 19 19 19C19.5523 19 20 19.4477 20 20Z" />
                                </svg>
                                <span class="CarrinhoTotalItems">${ Cart.items?.length }</span>
                            </a>
                        </div>


                    </div>
                </div>

                <div class="ResultadosProcura"></div>
                <div class="Body">
                @endverbatim

                @yield('content')

                @include('partials.footer')

                @verbatim
<div class="backToTop">
                    <a href="#"><img alt="Voltar ao topo" src='/assets/icons/chevron-circle-up-duotone.svg'
                            width='40' height="40"></a>
                </div>

            </div>

            <div class="d-none">











            </div>
            <script>
                window.descontos70 = null;
            </script>


            <script src="/assets/vue.global.prod.js"></script>
            <script src="/assets/axios.min.js"></script>




            <script>
                const _Settings = {
                    "estados_encomendas": {
                        "AGUARDAR": {
                            "label": "<span class='label label-default'>A Aguardar<\/span>",
                            "texto": "A Aguardar",
                            "cor": "#CD96CD",
                            "notifica": true,
                            "estados": ["PENDENTE", "PROCESSAR", "CANCELADO"]
                        },
                        "PENDENTE": {
                            "label": "<span class='label label-info'>Agendar<\/span>",
                            "texto": "Agendar",
                            "cor": "#D2B48C",
                            "estados": ["PROCESSAR", "PRONTO", "CANCELADO"]
                        },
                        "PAGO": {
                            "label": "<span class='label label-default'>Pago<\/span>",
                            "texto": "Pago",
                            "cor": "#66CDAA",
                            "notifica": true,
                            "estados": ["PROCESSAR"]
                        },
                        "PROCESSAR": {
                            "label": "<span class='label label-warning'>A Processar<\/span>",
                            "texto": "A Processar",
                            "cor": "#7CFC00",
                            "notifica": true,
                            "estados": ["PRONTO", "CANCELADO", "AGUARDAR"]
                        },
                        "ENVIADO": {
                            "label": "<span class='label label-success'>Pronto<\/span>",
                            "texto": "Pronta a Enviar",
                            "cor": "#BDB76B",
                            "notifica": true,
                            "estados": ["PROCESSAR", "CANCELADO"]
                        },
                        "CANCELADO": {
                            "label": "<span class='label label-danger'>Cancelada<\/span>",
                            "texto": "Cancelada",
                            "cor": "#FFA07A",
                            "notifica": true,
                            "estados": []
                        },
                        "DEVOLVIDO": {
                            "label": "<span class='label label-danger'>Devolvido<\/span>",
                            "texto": "Devolvido",
                            "cor": "#FF7F50",
                            "notifica": false
                        },
                        "ENTREGUE": {
                            "label": "<span class='label label-success'>Entregue<\/span>",
                            "texto": "Entregue",
                            "cor": "#ADFF2F",
                            "notifica": true,
                            "estados": []
                        },
                        "FALHADA": {
                            "label": "<span class='label label-default'>Falhada<\/span>",
                            "texto": "Falhada",
                            "cor": "#FAEBD7",
                            "estados": []
                        },
                        "TEMPORARIA": {
                            "label": "<span class='label label-default'>Tempor\u00e1ria<\/span>",
                            "texto": "Temporaria",
                            "cor": "#F8F8FF",
                            "estados": []
                        },
                        "ENCFORNECEDOR": {
                            "label": "<span class='label label-default'>Enc. Fornecedor<\/span>",
                            "texto": "Enc. fornecedor",
                            "cor": "#F8F8FF",
                            "estados": []
                        }
                    },
                    "Stickers": {
                        "": "Nenhum",
                        "EnvioImediato": "Envio Imediado",
                        "BestSeller": "Best Seller",
                        "EnvioGratis": "Envio Gr\u00e1tis",
                        "PrecoMaisBaixo": "Pre\u00e7o mais baixo",
                        "Saldos": "Saldos",
                        "TudoCusto": "Tudo Custo",
                        "BlackFriday": "BlackFriday",
                        "Cobranca": "Cobranca",
                        "70desc": "70% desconto",
                        "ExcOnline": "Exclus\u00edvo Online"
                    },
                    "metodos_envio": {
                        "LEVANTAMENTO": {
                            "nome": "Levantamento em Armaz\u00e9m (Standard) ",
                            "descricao": "Escolha o armazem de levantamento",
                            "cor": "#D2B48C",
                            "valor": 0,
                            "range_cp": {
                                "1000-9999": 0
                            },
                            "tracklink": "https:\/\/www.ctt.pt\/TRACKCODE"
                        },
                        "ENTPORTA": {
                            "nome": "Entrega \u00e0 porta",
                            "descricao": "Encomenda entregue \u00e0 porta da rua ao lado do cami\u00e3o",
                            "cor": "#D2B48C",
                            "valor": 45,
                            "iva": 23,
                            "range_cp": {
                                "1000-8999": 45,
                                "9000-9999": 400
                            },
                            "tracklink": "https:\/\/www.ctt.pt\/TRACKCODE"
                        },
                        "ENTCASA": {
                            "nome": "Entrega em casa",
                            "descricao": "Encomenda entregue em casa do cliente",
                            "cor": "#D2B48C",
                            "valor": 69,
                            "iva": 23,
                            "range_cp": {
                                "1000-8999": 69,
                                "9000-9999": 400
                            },
                            "tracklink": "https:\/\/www.ctt.pt\/TRACKCODE"
                        },
                        "GRATIS": {
                            "nome": "Entrega Gr\u00e1tis",
                            "descricao": "Entrega em casa do cliente",
                            "cor": "#D2B48C",
                            "valor": 0,
                            "iva": 23,
                            "range_cp": {
                                "1000-8999": 0,
                                "9000-9999": 30
                            },
                            "tracklink": "https:\/\/www.ctt.pt\/TRACKCODE"
                        },
                        "EXP4": {
                            "nome": "Entrega Expresso",
                            "descricao": "Entrega em casa do cliente",
                            "cor": "#D2B48C",
                            "valor": 4.9,
                            "iva": 23,
                            "range_cp": {
                                "1000-8999": 4.9,
                                "9000-9999": 49
                            },
                            "tracklink": "https:\/\/www.ctt.pt\/TRACKCODE"
                        },
                        "EXP9": {
                            "nome": "Entrega Expresso",
                            "descricao": "Entrega em casa do cliente",
                            "cor": "#D2B48C",
                            "valor": 9.9,
                            "iva": 23,
                            "range_cp": {
                                "1000-8999": 9.9,
                                "9000-9999": 49
                            },
                            "tracklink": "https:\/\/www.ctt.pt\/TRACKCODE"
                        },
                        "EXP19": {
                            "nome": "Entrega Expresso 19",
                            "descricao": "Entrega em casa do cliente",
                            "cor": "#D2B48C",
                            "valor": 19.9,
                            "iva": 23,
                            "range_cp": {
                                "1000-8999": 19.9,
                                "9000-9999": 99
                            },
                            "tracklink": "https:\/\/www.ctt.pt\/TRACKCODE"
                        }
                    },
                    "metodos_pagamento": {
                        "EUPAGO_MB": {
                            "icon": "mb.png",
                            "nome": "Multibanco eupago",
                            "nomeFront": "Multibanco",
                            "descricao": "Pague na caixa Multibanco ou Homebanking",
                            "custo": 0,
                            "minimo": 0,
                            "minimocusto": 3,
                            "iva": 23,
                            "disableEnvios": ["1"],
                            "visivel": true,
                            "default": true,
                            "codigo_erp": "LP"
                        },
                        "WALLET_MB": {
                            "icon": "mb.png",
                            "nome": "Multibanco Wallet",
                            "nomeFront": "Multibanco",
                            "descricao": "Pague na caixa Multibanco ou Homebanking",
                            "custo": 0,
                            "minimo": 0,
                            "minimocusto": 3,
                            "iva": 23,
                            "disableEnvios": ["1"],
                            "visivel": false,
                            "default": true,
                            "codigo_erp": "LP"
                        },
                        "EUPAGO_MBWAY": {
                            "icon": "mbway.png",
                            "nome": "MB Way Eupago",
                            "nomeFront": "MB Way",
                            "descricao": "Pague com o seu telem\u00f3vel",
                            "descricao2": "",
                            "extra": {
                                "html": "<div class='ExtraMbway'><span class='label'>Insira o n\u00famero de telemovel MBWay<\/span>\n                    <input type='number' \n                        @input='validaCamposExtraPagamento' \n                        name='pagamento_mbway'\n                        class='form-control' placeholder='N\u00ba de telemovel'>\n                    <small>Se ainda n\u00e3o instalou a aplica\u00e7\u00e3o MBWay no smartphone ou ainda n\u00e3o aderiu ao servi\u00e7o MBWay, por favor consulte este <a target='_blank' href='https:\/\/www.mbway.pt\/adesao-utilizadores\/'>link<\/a><\/small><\/div>",
                                "validation": {
                                    "field": "pagamento_mbway",
                                    "rule": "telemovel",
                                    "message": "Insira um n\u00famero de telemovel com MBWay associado"
                                }
                            },
                            "custo": 0,
                            "minimo": 0,
                            "minimocusto": 3,
                            "iva": 23,
                            "disableEnvios": ["1"],
                            "visivel": true,
                            "codigo_erp": "LP"
                        },
                        "WALLET_MBWAY": {
                            "icon": "mbway.png",
                            "nome": "MB Way wallet",
                            "nomeFront": "MB Way",
                            "descricao": "Pague com o seu telemovel",
                            "extra": "<label>Insira o n\u00famero de telemovel MBWay<\/label>\n                <input type='number' \n                    data-msg-required='Insira um numero de telemovel v\u00e1lido'\n                    class='form-control' placeholder='N\u00ba de telemovel' name='cliente[mbway]'>\n                <small>Se ainda n\u00e3o instalou a aplica\u00e7\u00e3o MBWay no smartphone ou ainda n\u00e3o aderiu ao servi\u00e7o MBWay, por favor consulte este <a target='_blank' href='https:\/\/www.mbway.pt\/adesao-utilizadores\/'>link<\/a><\/small>",
                            "custo": 0,
                            "minimo": 0,
                            "minimocusto": 3,
                            "iva": 23,
                            "disableEnvios": ["1"],
                            "visivel": true,
                            "codigo_erp": "LP"
                        },
                        "SCALAPAY": {
                            "icon": "scalapay.png",
                            "nome": "Scalapay",
                            "nomeFront": "Scalapay",
                            "descricao": "Pagamento em <b>3x<\/b> sem juros, <b>PRESTACAO3\u20ac<\/b> por m\u00eas.<br>Pagamento em <b>4x<\/b> sem juros, <b>PRESTACAO4\u20ac<\/b> por m\u00eas.",
                            "iva": 23,
                            "custo": 0,
                            "minimo": 0,
                            "minimocusto": 3,
                            "disableEnvios": [1],
                            "visivel": true,
                            "default": false,
                            "codigo_erp": "SP"
                        },
                        "SEQURA": {
                            "icon": "sequra.png",
                            "nome": "Sequra",
                            "nomeFront": "Sequra",
                            "descricao": "Pagamento em presta\u00e7\u00f5es",
                            "custo": 0,
                            "minimo": 0,
                            "minimocusto": 3,
                            "iva": 23,
                            "disableEnvios": ["1"],
                            "visivel": true,
                            "default": true,
                            "codigo_erp": "SQ"
                        },
                        "EUPAGO_CC": {
                            "icon": "cc.png",
                            "nome": "Visa Eupago",
                            "nomeFront": "Cart\u00e3o de Cr\u00e9dito",
                            "descricao": "Visa \/ Mastercard",
                            "custo": 0,
                            "minimo": 0,
                            "minimocusto": 3,
                            "iva": 23,
                            "disableEnvios": ["1"],
                            "visivel": true,
                            "codigo_erp": "LP"
                        },
                        "EUPAGO_COFIDIS": {
                            "icon": "cofidis.png",
                            "nome": "Cofidis Eupago",
                            "nomeFront": "Cofidis",
                            "descricao": "Pagamento em presta\u00e7\u00f5es",
                            "custo": 0,
                            "minimo": 0,
                            "minimocusto": 3,
                            "iva": 23,
                            "disableEnvios": ["1"],
                            "visivel": true,
                            "codigo_erp": "CR"
                        },
                        "STRIPE": {
                            "id": "STRIPE",
                            "nome": "Visa Stripe",
                            "nomeFront": "Cart\u00e3o de Cr\u00e9dito",
                            "descricao": "Cart\u00e3o de Cr\u00e9dito &nbsp;&nbsp;<img src=\"%root%template\/images\/paypal.png\" alt=\"Pagamento por Paypal\">",
                            "descricao2": "Cart\u00e3o de cr\u00e9dito",
                            "custo": 0,
                            "minimo": 0,
                            "minimocusto": 3,
                            "iva": 23,
                            "disableEnvios": ["1"],
                            "visivel": false
                        }
                    }
                };

                @endverbatim
                const _Cliente = @json($authUser ? ['nome' => $authUser->name, 'email' => $authUser->email, 'telemovel' => $authUser->phone] : null);
                const _Cart = @json($cartData);
                @verbatim

                const _searchResults = {
                    history: null,
                    results: null,
                    categorias: null
                };
                const _Config = {

                };

                @endverbatim
                window.CSRF_TOKEN = "{{ csrf_token() }}";
                window.AUTH_USER = @json($authUser ? ['name' => $authUser->name] : null);
                window.WISH_IDS = @json($wishIds);
                @verbatim
            </script>
            @endverbatim

            {{-- scripts propres à une page, exécutés AVANT l'app Vue #_Global
                 (fiche produit : données + vue/catalogo.js qui monte #_Product) --}}
            @stack('page-scripts')

            @verbatim
            <script src="/assets/vue/cookies.js"></script>
            <script src="/assets/js/store-global.js"></script>

            <script src="/assets/js/jquery-3.6.0.min.js"></script>

            <script src="/assets/compiled.1.js"></script>

            <link rel="stylesheet" type="text/css" href="/assets/plugins/owlcarousel/owl.carousel.min.css">
            <script src="/assets/plugins/owlcarousel/owl.carousel.min.js"></script>
            <script src="/assets/js/functions.js"></script>

            <script src="/assets/js/mainc4ca.js?1"></script>


            <script src="/assets/js/main5877.js?t=1787585023"></script>
            <script src="/assets/modulos/homeb83e.js?t=1739812250"></script>

            <link href="/assets/plugins/owlcarousel/owl.carousel.min.css" rel="stylesheet" />
            <script src="/assets/plugins/owlcarousel/owl.carousel.min.js"></script>

            <script src="/assets/js/shop.js"></script>

        </div>
    @endverbatim
    @stack('scripts')
</body>

</html>
