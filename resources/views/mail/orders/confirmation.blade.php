<x-mail::message>
# Obrigado pela sua encomenda, {{ \Illuminate\Support\Str::of($order->name)->before(' ') }}!

Recebemos a sua encomenda **{{ $order->ref }}** e está a ser processada.
Vai receber uma nova mensagem assim que for expedida.

<x-mail::panel>
**Encomenda {{ $order->ref }}** &nbsp;·&nbsp; {{ $order->created_at->format('d/m/Y H:i') }}
</x-mail::panel>

## Resumo

<x-mail::table>
| Artigo | Qt. | Total |
| :----- | :-: | ----: |
@foreach ($order->items as $it)
| {{ $it->name }}{{ $it->variation ? ' ('.$it->variation.')' : '' }} | {{ $it->qty }} | {{ $order->money($it->line_total) }} |
@endforeach
| **Subtotal** | | {{ $order->money($order->subtotal) }} |
| **Portes** ({{ $order->shippingLabel() }}) | | {{ $order->shipping_cost > 0 ? $order->money($order->shipping_cost) : 'Grátis' }} |
| **Total** | | **{{ $order->money($order->total) }}** |
</x-mail::table>

## Pagamento

**Método:** {{ $order->paymentLabel() }}
@if ($order->payment_method === 'MULTIBANCO')
Vai receber os dados de pagamento (entidade e referência) para pagar numa caixa Multibanco ou por homebanking.
@else
Vai receber um pedido de pagamento na aplicação **MB WAY** no número {{ $order->phone }}.
@endif

## Entrega

{{ $order->name }}
{{ $order->address }}
{{ $order->postal_code }} {{ $order->city }}
{{ $order->phone }}

@if (! $order->billing_same && $order->billing_address)
## Faturação

{{ $order->billing_name ?: $order->name }}
{{ $order->billing_address }}
{{ $order->billing_postal_code }} {{ $order->billing_city }}
@endif

<x-mail::button :url="url('/carrinho/checkout/sucesso/'.$order->ref)">
Ver a minha encomenda
</x-mail::button>

Qualquer questão? Responda a este email ou contacte o nosso apoio ao cliente.

Obrigado,<br>
Equipa **DFP Interiores**

<x-mail::subcopy>
Este email foi enviado para {{ $order->email }} na sequência da encomenda {{ $order->ref }}.
</x-mail::subcopy>
</x-mail::message>
