@props(['tone' => 'ink'])
<span {{ $attributes->class(['stamp', 'stamp--'.$tone]) }}>{{ $slot }}</span>
