<?php

namespace App\Http\Controllers;

use App\Commerce\Cart;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrder;
use App\Payments\PaymentGateways;
use App\Seo\Seo;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckoutController extends Controller
{
    public function show(Cart $cart, Seo $seo, PaymentGateways $gateways): View|RedirectResponse
    {
        $contents = $cart->contents();

        if ($contents['lines'] === []) {
            return redirect()->route('cart');
        }

        $seo->title('إتمام الطلب')
            ->description('اكتب بياناتك وعنوانك لإتمام طلب التكييف من النخيل كوول بالدفع عند الاستلام، وهنكلمك نأكد الطلب وميعاد التوصيل. للمساعدة اتصل 01055207525.')
            ->canonical(route('checkout'))
            ->noindex()
            ->breadcrumbs([['name' => 'السلة', 'url' => route('cart')], ['name' => 'إتمام الطلب', 'url' => route('checkout')]]);

        return view('store.checkout', $contents + ['gateways' => $gateways->available()]);
    }

    public function store(CheckoutRequest $request, Cart $cart, PaymentGateways $gateways): RedirectResponse
    {
        $contents = $cart->contents();

        if ($contents['lines'] === []) {
            return redirect()->route('cart')->withErrors(['cart' => 'السلة فاضية أو المنتجات بقت مش متوفرة.']);
        }

        $gateway = $gateways->get($request->validated('payment_method'));
        abort_if($gateway === null, 422);

        // Prices are recomputed from the database at order time; the snapshot keeps them on the order.
        $order = DB::transaction(function () use ($request, $contents, $gateway): Order {
            $order = Order::query()->create([
                ...$request->safe()->only(['name', 'phone', 'area_id', 'area_text', 'address', 'notes']),
                'number' => Order::nextNumber(),
                'subtotal' => $contents['subtotal'],
                'shipping_fee' => 0,
                'total' => $contents['subtotal'],
                'payment_method' => $gateway->key(),
                'utm' => array_filter($request->only(['utm_source', 'utm_medium', 'utm_campaign', 'gclid'])) ?: null,
            ]);

            foreach ($contents['lines'] as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'name' => $line['product']->descriptiveName(false),
                    'model_number' => $line['product']->model_number,
                    'unit_price' => $line['product']->currentPrice(),
                    'qty' => $line['qty'],
                    'line_total' => $line['line_total'],
                ]);
            }

            return $order;
        });

        $cart->clear();
        $order->load(['items', 'area']);

        FilamentNotification::make()
            ->title('طلب جديد '.$order->number)
            ->body($order->name.' — '.number_format((float) $order->total).' جنيه')
            ->sendToDatabase(User::all());

        if ($email = config('site.notifications.email')) {
            Notification::route('mail', $email)->notify(new NewOrder($order));
        }

        $redirect = $gateway->initiate($order);

        return $redirect !== null
            ? redirect()->away($redirect)
            : redirect()->route('thank-you', ['type' => 'order'])->with('order', ['number' => $order->number, 'total' => (float) $order->total]);
    }
}
