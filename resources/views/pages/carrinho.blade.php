@extends('layouts.app')

@section('title', __('Carrinho').' | DFP Interiores')

@section('content')
@verbatim
<div class="CartPage">
    <h1>@endverbatim{{ __('O meu carrinho') }}@verbatim</h1>

    <div v-if="!Cart.items || Cart.items.length === 0" class="cart-empty">
        <p>@endverbatim{{ __('O seu carrinho está vazio.') }}@verbatim</p>
        <a href="/" class="btn btn-primary">@endverbatim{{ __('Continuar a comprar') }}@verbatim</a>
    </div>

    <div v-else class="cart-grid">
        <div class="cart-lines">
            <div v-for="item in Cart.items" :key="item.linhaid" class="cart-line">
                <div class="thumb">
                    <a :href="'/' + item.seo">
                        <img :src="'/images/200-200/' + (item.images && item.images[0])" :alt="item.nome">
                    </a>
                </div>
                <div class="info">
                    <a class="name" :href="'/' + item.seo">${ item.nome }</a>
                    <div v-if="item.variation" class="opts">
                        <span v-for="(v, k) in item.variation" :key="k">
                            ${ Object.keys(v)[0] }: <b>${ Object.values(v)[0] }</b>
                        </span>
                    </div>
                    <div class="unit">${ Number(item.preco).toFixed(2) }€ @endverbatim{{ __('/ unid.') }}@verbatim</div>
                </div>
                <div class="right">
                    <div class="lt">${ Number(item.total).toFixed(2) }€</div>
                    <div class="qty">
                        <button @click="minusQtd(item)" title="@endverbatim{{ __('Diminuir') }}@verbatim">
                            <svg v-if="item.qtd == 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /><path d="M10 11v6" /><path d="M14 11v6" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" />
                            </svg>
                        </button>
                        <span class="n">${ item.qtd }</span>
                        <button @click="plusQtd(item)" title="@endverbatim{{ __('Aumentar') }}@verbatim">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" /><path d="M12 5v14" />
                            </svg>
                        </button>
                    </div>
                    <a href="#" @click.prevent="cartRemove(item)" style="font-size:12px;color:#b02a37;">@endverbatim{{ __('Remover') }}@verbatim</a>
                </div>
            </div>
        </div>

        <aside class="summary">
            <h2>@endverbatim{{ __('Resumo') }}@verbatim</h2>
            <div class="row"><span>@endverbatim{{ __('Subtotal') }}@verbatim</span><span>${ Number(Cart.subtotal || Cart.total).toFixed(2) }€</span></div>
            <div class="row" v-if="Cart.desconto"><span>@endverbatim{{ __('Desconto') }}@verbatim</span><span>- ${ Number(Cart.desconto).toFixed(2) }€</span></div>
            <div class="row"><span>@endverbatim{{ __('Portes de envio') }}@verbatim</span><span>@endverbatim{{ __('Grátis') }}@verbatim</span></div>
            <div class="row total"><span>@endverbatim{{ __('Total') }}@verbatim</span><span>${ (Number(Cart.total) - Number(Cart.desconto || 0)).toFixed(2) }€</span></div>
            <p style="font-size:13px;color:#555;margin:8px 0 0;">@endverbatim{{ __('Envio grátis para todo o Portugal. Entrega em 1 a 3 dias úteis (preparação: 1 dia útil).') }}@verbatim</p>
            <a href="/carrinho/checkout" class="cta">@endverbatim{{ __('Finalizar encomenda') }}@verbatim</a>
            <a href="/" class="keep">@endverbatim{{ __('Continuar a comprar') }}@verbatim</a>
        </aside>
    </div>
</div>
@endverbatim
@endsection
