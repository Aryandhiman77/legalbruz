@extends('layouts.app')

@section('content')
    @include('admin.faqs.partials.form', [
        'title' => 'Edit FAQ',
        'action' => route('admin.faqs.update', $faq),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
@endsection
