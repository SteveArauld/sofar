<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function add(Request $request)
    {
        $id = CartService::resolveProductId((string) $request->input('product'));
        abort_if($id === null, 422, 'Produto inválido.');

        $variation = $request->filled('variation') ? (int) $request->input('variation') : null;
        $qtd = (int) $request->input('qtd', 1);

        $this->cart->add($id, $variation, $qtd);

        return response()->json([
            'cart' => $this->cart->toArray(),
            'dl'   => $this->cart->dataLayer('add_to_cart'),
        ]);
    }

    public function update(Request $request)
    {
        $this->cart->update((string) $request->input('id'), (int) $request->input('qtd'));

        return response()->json([
            'cart' => $this->cart->toArray(),
            'dl'   => $this->cart->dataLayer('update_cart'),
        ]);
    }

    public function remove(Request $request)
    {
        $this->cart->remove((string) $request->input('id'));

        return response()->json([
            'cart' => $this->cart->toArray(),
            'dl'   => $this->cart->dataLayer('remove_from_cart'),
        ]);
    }

    public function json()
    {
        return response()->json(['cart' => $this->cart->toArray()]);
    }
}
