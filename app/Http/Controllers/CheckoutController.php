<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Mail\OrderReceivedAdmin;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public const SHIPPING = [
        'LEVANTAMENTO' => ['label' => 'Levantamento em loja', 'desc' => 'Grátis — pronto em 24-72h', 'cost' => 0.0],
        'ENTREGA'      => ['label' => 'Entrega ao domicílio', 'desc' => 'Portugal Continental', 'cost' => 45.0],
    ];

    public const PAYMENT = [
        'MULTIBANCO' => ['label' => 'Referência Multibanco', 'desc' => 'Pague na caixa MB ou homebanking'],
        'MBWAY'      => ['label' => 'MB WAY', 'desc' => 'Pagamento pelo telemóvel'],
    ];

    public function __construct(private CartService $cart)
    {
    }

    public function show()
    {
        $cart = $this->cart->toArray();
        if (empty($cart['items'])) {
            return redirect('/carrinho');
        }

        $user = auth()->user();

        return view('pages.checkout', [
            'cart'      => $cart,
            'prefill'   => [
                'name'  => old('name', $user->name ?? ''),
                'email' => old('email', $user->email ?? ''),
                'phone' => old('phone', $user->phone ?? ''),
            ],
        ]);
    }

    public function place(Request $request)
    {
        $cart = $this->cart->toArray();
        if (empty($cart['items'])) {
            return redirect('/carrinho');
        }

        $billingSame = ! $request->boolean('billing_different');

        $rules = [
            'name'        => ['required', 'string', 'min:2', 'max:255'],
            'email'       => ['required', 'email', 'max:255'],
            'phone'       => ['required', 'string', 'max:32'],
            'nif'         => ['nullable', 'string', 'max:32'],
            'address'     => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'city'        => ['required', 'string', 'max:255'],
            'notes'       => ['nullable', 'string', 'max:2000'],
            'terms'       => ['accepted'],
        ] + ($billingSame ? [] : [
            'billing_name'        => ['required', 'string', 'min:2', 'max:255'],
            'billing_address'     => ['required', 'string', 'max:255'],
            'billing_postal_code' => ['nullable', 'string', 'max:16'],
            'billing_city'        => ['required', 'string', 'max:255'],
        ]);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // toujours revenir sur la page checkout avec erreurs + saisie
            return redirect()->route('checkout')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Livraison et paiement fixes (plus de choix sur le checkout)
        $shipMethod = 'LEVANTAMENTO';
        $payMethod = 'MULTIBANCO';
        $shipCost = self::SHIPPING[$shipMethod]['cost'];
        $subtotal = (float) $cart['subtotal'];
        $total = $subtotal + $shipCost;

        $order = Order::create([
            'ref'             => $this->makeRef(),
            'user_id'         => auth()->id(),
            'name'            => $data['name'],
            'email'           => $data['email'],
            'phone'           => $data['phone'],
            'nif'             => $data['nif'] ?? null,
            'address'         => $data['address'],
            'postal_code'     => $data['postal_code'] ?? '',
            'city'            => $data['city'],
            'billing_same'    => $billingSame,
            'billing_name'    => $billingSame ? null : $data['billing_name'],
            'billing_address' => $billingSame ? null : $data['billing_address'],
            'billing_postal_code' => $billingSame ? null : ($data['billing_postal_code'] ?? null),
            'billing_city'    => $billingSame ? null : $data['billing_city'],
            'notes'           => $data['notes'] ?? null,
            'shipping_method' => $shipMethod,
            'shipping_cost'   => $shipCost,
            'payment_method'  => $payMethod,
            'subtotal'        => $subtotal,
            'total'           => $total,
            'status'          => 'AGUARDAR',
            'items_snapshot'  => $cart['items'],
        ]);

        foreach ($cart['items'] as $it) {
            $order->items()->create([
                'product_id' => null,
                'name'       => $it['nome'],
                'variation'  => $it['variation'] ? collect($it['variation'])->map(fn ($v) => implode(': ', [array_key_first($v), reset($v)]))->implode(', ') : null,
                'unit_price' => $it['preco'],
                'qty'        => $it['qtd'],
                'line_total' => $it['total'],
            ]);
        }

        $this->cart->clear();
        session(['last_order_ref' => $order->ref]);

        // Emails : confirmation client + notification admin (n'interrompt pas la commande)
        try {
            Mail::to($order->email)->send(new OrderConfirmation($order));
            Mail::to(config('mail.admin_address'))->send(new OrderReceivedAdmin($order));
        } catch (\Throwable $e) {
            Log::error('Envoi email commande '.$order->ref.' échoué : '.$e->getMessage());
        }

        return redirect()->route('checkout.success', $order->ref);
    }

    public function success(string $ref)
    {
        $order = Order::with('items')->where('ref', $ref)->firstOrFail();

        // n'affiche le détail que si la commande vient d'être passée dans cette session
        abort_unless(session('last_order_ref') === $ref || auth()->id() === $order->user_id, 403);

        return view('pages.checkout-sucesso', [
            'order'    => $order,
            'shipping' => self::SHIPPING,
            'payment'  => self::PAYMENT,
            'mbRef'    => str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
            'mbEntity' => '21' . random_int(100, 999),
        ]);
    }

    private function makeRef(): string
    {
        do {
            $ref = 'DFP-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (Order::where('ref', $ref)->exists());

        return $ref;
    }
}
