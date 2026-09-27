{{-- Decorative LCD readout. Never a heading; the meaning must also exist as real text elsewhere. --}}
@props(['value', 'label' => null])
<span {{ $attributes->class(['lcd'])->merge(['aria-hidden' => 'true']) }}>
    <span class="lcd__value">{{ $value }}</span>
    @if ($label)<span class="lcd__label">{{ $label }}</span>@endif
</span>
