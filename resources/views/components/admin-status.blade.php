@props(['status', 'label' => null])

<span {{ $attributes->class('admin-status-pill')->merge([
    'style' => \App\Support\AdminStatusPalette::style((string) $status),
    'title' => \App\Support\AdminStatusPalette::schemeLabel((string) $status),
]) }}><i aria-hidden="true"></i>{{ $label ?? \Illuminate\Support\Str::headline((string) $status) }}</span>
