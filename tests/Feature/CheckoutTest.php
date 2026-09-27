<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrder;
use Filament\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    publishCorePages();
});

it('adds, updates and removes cart items in the session', function () {
    ['products' => $products] = publishCatalog();

    $this->post('/cart/items', ['product_id' => $products[0]->id, 'qty' => 2])->assertRedirect('/cart')->assertCookie('cart_count', '2', false);
    $this->post('/cart/items', ['product_id' => $products[1]->id]);

    $doc = html((string) $this->get('/cart')->assertOk()->getContent());
    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow')
        ->and($doc->querySelector('.cart-summary__total')->textContent)->toBe('64,000 جنيه');

    $this->patch('/cart/items/'.$products[0]->id, ['qty' => 1]);
    $this->delete('/cart/items/'.$products[1]->id);
    expect(session('cart'))->toBe([$products[0]->id => 1]);
});

it('refuses unpublished or out-of-stock products', function () {
    ['products' => $products] = publishCatalog();
    $draft = Product::factory()->create(['brand_id' => $products[0]->brand_id]);
    $products[1]->update(['stock_status' => 'out_of_stock']);

    $this->post('/cart/items', ['product_id' => $draft->id])->assertNotFound();
    $this->post('/cart/items', ['product_id' => $products[1]->id])->assertStatus(422);
});

it('places a cash-on-delivery order with server-side prices and notifies the team', function () {
    Notification::fake();
    config(['site.notifications.email' => 'team@example.com']);
    $admin = User::factory()->create();
    ['products' => $products] = publishCatalog();

    $this->post('/cart/items', ['product_id' => $products[0]->id, 'qty' => 2]);
    $this->get('/checkout')->assertOk()->assertSee('الدفع عند الاستلام');

    $this->post('/checkout', [
        'name' => 'أحمد',
        'phone' => '+20 105 520 7525',
        'area_text' => 'مدينة نصر',
        'address' => 'شارع عباس العقاد، عمارة 5',
        'payment_method' => 'cod',
        'subtotal' => 1, // ignored: prices always come from the database
    ])->assertRedirect(route('thank-you', ['type' => 'order']));

    $order = Order::query()->with('items')->sole();
    expect($order->phone)->toBe('01055207525')
        ->and((float) $order->total)->toBe(42000.0)
        ->and($order->items->sole()->qty)->toBe(2)
        ->and($order->number)->toStartWith('NK-')
        ->and(session('cart'))->toBe([]);

    Notification::assertSentOnDemand(NewOrder::class);
    Notification::assertSentTo($admin, DatabaseNotification::class);

    $doc = html((string) $this->get(route('thank-you', ['type' => 'order']))->getContent());
    expect($doc->querySelector('[data-track-onload="purchase"]'))->not->toBeNull();
});

it('sends an empty cart back from checkout', function () {
    $this->get('/checkout')->assertRedirect('/cart');
    $this->post('/checkout', ['name' => 'أحمد', 'phone' => '01055207525', 'address' => 'عنوان طويل', 'area_text' => 'مدينة نصر', 'payment_method' => 'cod'])
        ->assertRedirect('/cart');
    expect(Order::query()->count())->toBe(0);
});

it('validates checkout fields in Arabic', function () {
    ['products' => $products] = publishCatalog(1);
    $this->post('/cart/items', ['product_id' => $products[0]->id]);

    $this->from('/checkout')->post('/checkout', ['name' => '', 'phone' => '123', 'payment_method' => 'card'])
        ->assertSessionHasErrors(['name', 'phone', 'address', 'payment_method']);
});
