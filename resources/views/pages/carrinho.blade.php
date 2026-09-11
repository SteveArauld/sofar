@extends('layouts.app')

@section('title', 'Carrinho | DFP Interiores')

@section('content')
@verbatim
<div class="CartPage">
    <h1>O meu carrinho</h1>

    <div v-if="!Cart.items || Cart.items.length === 0" class="cart-empty">
        <p>O seu carrinho está vazio.</p>
        <a href="/" class="btn btn-primary">Continuar a comprar</a>
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
                    <div class="unit">${ Number(item.preco).toFixed(2) }€ / unid.</div>
                </div>
                <div class="right">
                    <div class="lt">${ Number(item.total).toFixed(2) }€</div>
                    <div class="qty">
                        <button @click="minusQtd(item)" title="Diminuir">
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
                        <button @click="plusQtd(item)" title="Aumentar">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" /><path d="M12 5v14" />
                            </svg>
                        </button>
                    </div>
                    <a href="#" @click.prevent="cartRemove(item)" style="font-size:12px;color:#b02a37;">Remover</a>
                </div>
            </div>
        </div>

        <aside class="summary">
            <h2>Resumo</h2>
            <div class="row"><span>Subtotal</span><span>${ Number(Cart.subtotal || Cart.total).toFixed(2) }€</span></div>
            <div class="row" v-if="Cart.desconto"><span>Desconto</span><span>- ${ Number(Cart.desconto).toFixed(2) }€</span></div>
            <div class="row"><span>Portes de envio</span><span>Grátis</span></div>
            <div class="row total"><span>Total</span><span>${ (Number(Cart.total) - Number(Cart.desconto || 0)).toFixed(2) }€</span></div>
            <p style="font-size:13px;color:#555;margin:8px 0 0;">Envio grátis para todo o Portugal. Entrega em 2 a 4 dias úteis (preparação 1-2 dias + transporte 1-2 dias).</p>
            <a href="/carrinho/checkout" class="cta">Finalizar encomenda</a>
            <a href="/" class="keep">Continuar a comprar</a>
        </aside>
    </div>
</div>
@endverbatim
@endsection
