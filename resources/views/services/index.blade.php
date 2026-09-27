@php use App\Support\Copy; @endphp
<x-layout class="page-services">
    <x-page-header title="خدماتنا" intro="كل اللي تكييفك محتاجه في مكان واحد: من التأسيس والتركيب لحد الصيانة والتنظيف وعقود الصيانة." />

    <section class="section" aria-labelledby="services-list">
        <div class="container">
            <h2 id="services-list" class="visually-hidden">خدمات التكييف</h2>
            <ul class="service-grid">
                @foreach ($services as $service)
                    <li class="service-card">
                        @if ($service->image)
                            <x-picture class="service-card__img" :name="$service->image" :alt="$service->name" sizes="106px" />
                        @endif
                        <h3><a href="{{ $service->url() }}">{{ $service->name }}</a></h3>
                        <p>{{ Copy::text($service->summary) }}</p>
                        <a class="btn" href="{{ $service->url() }}">التفاصيل</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <x-booking-form />
</x-layout>
