@props(['value', 'label' => null, 'selected' => false])

@php
    $displayLabel = $label ?? \Illuminate\Support\Str::headline((string) $value);
    $colors = \App\Support\AdminStatusPalette::colors((string) $displayLabel);
@endphp

<option value="{{ $value }}" @selected($selected)
    data-status-label="{{ $displayLabel }}"
    data-status-scheme="{{ $colors['label'] }}"
    data-status-bg="{{ $colors['background'] }}"
    data-status-border="{{ $colors['border'] }}"
    data-status-text="{{ $colors['text'] }}"
    data-status-dot="{{ $colors['dot'] }}">
    {{ $displayLabel }} — {{ $colors['label'] }}
</option>
