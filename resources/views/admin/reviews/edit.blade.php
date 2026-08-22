@extends('layouts.app')

@section('content')
    @include('admin.reviews.partials.form', [
        'title' => 'Edit Review',
        'action' => route('admin.reviews.update', $review),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
@endsection
