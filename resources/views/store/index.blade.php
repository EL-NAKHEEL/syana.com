<x-layout class="page-store">
    <x-hub-header :hub="$hub" title="متجر التكييفات" intro="كل الموديلات بأسعار واضحة. اطلب أونلاين وادفع عند الاستلام، أو كلّمنا نساعدك تختار." />

    <section class="section">
        <div class="container">
            <x-store.calculator />
        </div>
        <div class="container store-layout">
            <x-store.filters :action="$formAction" :filters="$filters" :brand-options="$brandOptions" />
            <x-store.results :products="$products" :chips="$chips" />
        </div>
    </section>

    <section class="section section--light">
        <div class="container">
            <x-store.facet-links :links="$facetLinks" />
        </div>
    </section>
    @if ($products->currentPage() === 1 && ! request()->query())
        <x-hub-extra :hub="$hub" />
    @endif
</x-layout>
