<?php

namespace App\Http\Controllers;

use App\Commerce\Cart;
use App\Models\Product;
use App\Seo\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Cart $cart, Seo $seo): View
    {
        $seo->title('سلة المشتريات')
            ->description('راجع التكييفات اللي في السلة وكمّل الطلب بالدفع عند الاستلام، أو اتصل بالنخيل كوول على 01055207525 لو محتاج مساعدة في الاختيار أو التركيب.')
            ->canonical(route('cart'))
            ->noindex()
            ->breadcrumbs([['name' => 'السلة', 'url' => route('cart')]]);

        return view('store.cart', $cart->contents());
    }

    public function add(Request $request, Cart $cart): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:'.Cart::MAX_QTY],
        ]);

        $product = Product::query()->live()->with('brand')->findOrFail($data['product_id']);
        abort_unless($product->canBeOrdered(), 422);

        $cart->add($product, (int) ($data['qty'] ?? 1));

        return redirect()->route('cart')->with('status', 'اتضاف '.$product->descriptiveName(false).' للسلة.');
    }

    public function update(Request $request, Cart $cart, int $product): RedirectResponse
    {
        $data = $request->validate(['qty' => ['required', 'integer', 'min:0', 'max:'.Cart::MAX_QTY]]);
        $cart->update($product, (int) $data['qty']);

        return redirect()->route('cart');
    }

    public function remove(Cart $cart, int $product): RedirectResponse
    {
        $cart->update($product, 0);

        return redirect()->route('cart');
    }
}
