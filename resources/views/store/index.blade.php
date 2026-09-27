<x-layout class="page-store">
    <x-page-header title="متجر التكييفات" intro="كل الموديلات بأسعار واضحة. اطلب أونلاين وادفع عند الاستلام، أو كلّمنا نساعدك تختار." />

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
</x-layout>
