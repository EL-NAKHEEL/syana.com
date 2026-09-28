@php use App\Support\Copy; @endphp
@if ($why = $page->data('why'))
    <section class="section" aria-labelledby="why-title">
        <div class="container two-col">
            <x-image :media="$page->getMedia('gallery')->get(2)" fallback="technician-on-ladder-servicing-ac" alt="فني على سلم بيصين تكييف على واجهة مبنى" sizes="(min-width: 992px) 600px, 100vw" />
            <div>
                <span class="eyebrow">{{ ($why['eyebrow'] ?? '') ?: 'ميزاتنا' }}</span>
                <h2 id="why-title">{{ $why['heading'] }}</h2>
                <ul class="features">
                    @foreach ($why['items'] ?? [] as $item)
                        <li class="feature">
                            <span class="icon-circle"><x-icon.check /></span>
                            <div>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ Copy::text($item['text']) }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <x-call-button size="lg" location="why" label="اضغط للاتصال" />
            </div>
        </div>
    </section>
@endif
