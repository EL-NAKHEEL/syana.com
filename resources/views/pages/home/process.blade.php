@php use App\Support\Copy; @endphp
@if ($process = $page->data('process'))
    <section class="section section--dark" aria-labelledby="process-title">
        <div class="container">
            <div class="section-title--center">
                <span class="eyebrow">{{ ($process['eyebrow'] ?? '') ?: 'خطوات الشغل' }}</span>
                <h2 id="process-title">{{ $process['heading'] }}</h2>
            </div>
            <ol class="steps">
                @foreach ($process['steps'] ?? [] as $step)
                    <li>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ Copy::text($step['text']) }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endif
