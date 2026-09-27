<x-layout class="page-cart">
    <x-page-header title="سلة المشتريات" />

    <section class="section">
        <div class="container">
            @if (session('status'))
                <p class="notice" role="status">{{ session('status') }}</p>
            @endif

            @if ($lines === [])
                <p>السلة فاضية. <a href="{{ route('store.index') }}">تصفح المتجر</a> أو <a href="tel:{{ config('site.phone.e164') }}" data-track="click_call" data-track-location="cart">اتصل بينا</a> نساعدك تختار.</p>
            @else
                <div class="table-wrap">
                    <table class="price-table cart-table">
                        <thead>
                            <tr><th scope="col">المنتج</th><th scope="col">السعر</th><th scope="col">الكمية</th><th scope="col">الإجمالي</th><th scope="col"><span class="visually-hidden">حذف</span></th></tr>
                        </thead>
                        <tbody>
                            @foreach ($lines as $line)
                                <tr>
                                    <th scope="row"><a href="{{ $line['product']->url() }}">{{ $line['product']->descriptiveName() }}</a></th>
                                    <td>{{ number_format($line['product']->currentPrice()) }} جنيه</td>
                                    <td>
                                        <form method="post" action="{{ route('cart.update', $line['product']->id) }}">
                                            @csrf @method('PATCH')
                                            <label class="visually-hidden" for="qty-{{ $line['product']->id }}">الكمية</label>
                                            <input id="qty-{{ $line['product']->id }}" name="qty" type="number" min="0" max="{{ \App\Commerce\Cart::MAX_QTY }}" value="{{ $line['qty'] }}" inputmode="numeric">
                                            <button class="btn btn-dark" type="submit">تحديث</button>
                                        </form>
                                    </td>
                                    <td><strong>{{ number_format($line['line_total']) }} جنيه</strong></td>
                                    <td>
                                        <form method="post" action="{{ route('cart.remove', $line['product']->id) }}">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-dark" type="submit" aria-label="احذف {{ $line['product']->descriptiveName(false) }}">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="cart-summary stack-top">
                    <p>المجموع: <span class="cart-summary__total">{{ number_format($subtotal) }} جنيه</span></p>
                    <p>التوصيل والتركيب: هنأكد معاك التكلفة والميعاد في مكالمة التأكيد.</p>
                    <div class="btn-row">
                        <a class="btn btn-primary btn-lg" href="{{ route('checkout') }}" data-track="begin_checkout">كمّل الطلب</a>
                        <a class="btn btn-dark" href="{{ route('store.index') }}">كمّل تسوق</a>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layout>
