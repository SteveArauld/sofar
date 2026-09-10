<x-mail::message>
# Nova encomenda {{ $order->ref }}

Recebida a {{ $order->created_at->format('d/m/Y \à\s H:i') }} — total **{{ $order->money($order->total) }}**.

<x-mail::table>
| Artigo | Qt. | P. unit. | Total |
| :----- | :-: | -------: | ----: |
@foreach ($order->items as $it)
| {{ $it->name }}{{ $it->variation ? ' ('.$it->variation.')' : '' }} | {{ $it->qty }} | {{ $order->money($it->unit_price) }} | {{ $order->money($it->line_total) }} |
@endforeach
| **Subtotal** | | | {{ $order->money($order->subtotal) }} |
| **Portes** ({{ $order->shippingLabel() }}) | | | {{ $order->shipping_cost > 0 ? $order->money($order->shipping_cost) : 'Grátis' }} |
| **Total** | | | **{{ $order->money($order->total) }}** |
</x-mail::table>

## Cliente

| | |
| :-- | :-- |
| Nome | {{ $order->name }} |
| Email | {{ $order->email }} |
| Telemóvel | {{ $order->phone }} |
| NIF | {{ $order->nif ?: '—' }} |
| Conta | {{ $order->user_id ? 'Registada (#'.$order->user_id.')' : 'Convidado' }} |

## Entrega — {{ $order->shippingLabel() }}

{{ $order->address }}
{{ $order->postal_code }} {{ $order->city }}

## Faturação

@if ($order->billing_same || ! $order->billing_address)
_Igual à morada de entrega._
@else
{{ $order->billing_name ?: $order->name }}
{{ $order->billing_address }}
{{ $order->billing_postal_code }} {{ $order->billing_city }}
@endif

## Pagamento

{{ $order->paymentLabel() }}

@if ($order->notes)
## Notas do cliente

{{ $order->notes }}
@endif

<x-mail::button :url="url('/carrinho/checkout/sucesso/'.$order->ref)">
Abrir a encomenda
</x-mail::button>

Estado atual: **{{ $order->status }}**
</x-mail::message>
