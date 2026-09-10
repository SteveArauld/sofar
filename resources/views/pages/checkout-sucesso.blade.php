@extends('layouts.app')

@section('title', 'Encomenda confirmada | DFP Interiores')

@section('content')
<div class="OrderDone">
    <div class="tick">✓</div>
    <h1>Obrigado, {{ \Illuminate\Support\Str::of($order->name)->before(' ') }}!</h1>
    <p>A sua encomenda <strong>{{ $order->ref }}</strong> foi registada. Enviámos a confirmação para {{ $order->email }}.</p>

    <div class="ref-box">
        @if ($order->payment_method === 'MULTIBANCO')
            <div class="row"><span>Entidade</span><span>{{ $mbEntity }}</span></div>
            <div class="row"><span>Referência</span><span>{{ $mbRef }}</span></div>
            <div class="row"><span>Montante</span><span>{{ number_format((float) $order->total, 2, ',', ' ') }}€</span></div>
            <p style="font-size:12px;color:#999;margin-top:10px;">Pague numa caixa Multibanco ou no homebanking nas próximas 72h.</p>
        @else
            <div class="row"><span>MB WAY</span><span>{{ $order->phone }}</span></div>
            <div class="row"><span>Montante</span><span>{{ number_format((float) $order->total, 2, ',', ' ') }}€</span></div>
            <p style="font-size:12px;color:#999;margin-top:10px;">Vai receber um pedido de pagamento na app MB WAY.</p>
        @endif
    </div>

    <div class="ref-box">
        @foreach ($order->items as $it)
            <div class="row">
                <span>{{ $it->name }}@if ($it->variation) <small style="color:#999;">({{ $it->variation }})</small>@endif &times; {{ $it->qty }}</span>
                <span>{{ number_format((float) $it->line_total, 2, ',', ' ') }}€</span>
            </div>
        @endforeach
        <div class="row"><span>Portes — {{ $shipping[$order->shipping_method]['label'] ?? $order->shipping_method }}</span><span>{{ $order->shipping_cost > 0 ? number_format((float) $order->shipping_cost, 2, ',', ' ').'€' : 'Grátis' }}</span></div>
        <div class="row big"><span>Total</span><span>{{ number_format((float) $order->total, 2, ',', ' ') }}€</span></div>
    </div>

    <div class="ref-box">
        <div class="row"><span>Entrega em</span><span>{{ $order->address }}, {{ $order->postal_code }} {{ $order->city }}</span></div>
        @if (! $order->billing_same && $order->billing_address)
            <div class="row"><span>Faturação</span><span>{{ $order->billing_name ?: $order->name }} — {{ $order->billing_address }}, {{ $order->billing_postal_code }} {{ $order->billing_city }}</span></div>
        @endif
        <div class="row"><span>Confirmação enviada para</span><span>{{ $order->email }}</span></div>
    </div>

    <a href="/" class="btn">Continuar a comprar</a>
</div>
@endsection
