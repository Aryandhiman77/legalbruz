@extends('layouts.app')

@section('content')
    @include('admin.faqs.partials.form', [
        'title' => 'Create FAQ',
        'action' => route('admin.faqs.store'),
        'method' => 'POST',
        'submitLabel' => 'Create FAQ',
    ])
@endsection
