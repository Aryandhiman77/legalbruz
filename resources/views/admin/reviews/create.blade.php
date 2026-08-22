@extends('layouts.app')

@section('content')
    @include('admin.reviews.partials.form', [
        'title' => 'Create Review',
        'action' => route('admin.reviews.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Review',
    ])
@endsection
