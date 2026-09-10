<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private WishlistService $wishlist)
    {
    }

    public function toggle(Request $request)
    {
        $id = CartService::resolveProductId((string) $request->input('product'));
        abort_if($id === null, 422, 'Produto inválido.');

        $added = $this->wishlist->toggle($id);

        return response()->json([
            'added' => $added,
            'count' => $this->wishlist->count(),
        ]);
    }

    public function remove(Request $request)
    {
        $id = CartService::resolveProductId((string) $request->input('product'));
        if ($id !== null) {
            $this->wishlist->remove($id);
        }

        return response()->json(['count' => $this->wishlist->count()]);
    }

    public function page()
    {
        $products = Product::with('images')
            ->whereIn('id', $this->wishlist->ids() ?: [0])
            ->get();

        return view('pages.wishlist', ['products' => $products]);
    }
}
