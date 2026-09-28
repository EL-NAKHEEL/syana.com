@php use App\Support\Copy; @endphp
<x-layout class="page-areas">
    <x-hub-header :hub="$hub" title="مناطق الخدمة" intro="اختار منطقتك واعرف الخدمات المتاحة فيها ووقت الاستجابة." />

    <section class="section" aria-labelledby="areas-list">
        <div class="container">
            <h2 id="areas-list" class="visually-hidden">المناطق</h2>
            <ul class="area-grid">
                @foreach ($areas as $area)
                    <li class="area-card">
                        <h3><a href="{{ $area->url() }}">صيانة وتركيب تكييفات في {{ $area->name_ar }}</a></h3>
                        @if ($area->response_time_note)
                            <p>{{ Copy::text($area->response_time_note) }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <x-hub-extra :hub="$hub" />

    <x-booking-form />
</x-layout>
