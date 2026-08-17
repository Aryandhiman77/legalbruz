@props([
    'value',
    'label' => null,
])

@php
    $displayDateTime = $value
        ? \Illuminate\Support\Carbon::parse($value)->timezone('Asia/Kolkata')
        : null;
@endphp

<small {{ $attributes->class(['text-muted', 'd-block', 'text-nowrap']) }}>
    @if ($displayDateTime)
        <time datetime="{{ $displayDateTime->toIso8601String() }}">{{ $label ? $label . ' ' : '' }}{{ $displayDateTime->format('d M Y, h:i A') }}</time>
    @else
        —
    @endif
</small>
