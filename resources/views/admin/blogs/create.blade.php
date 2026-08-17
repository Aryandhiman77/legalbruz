@extends('layouts.app')
@section('content')
    @include('admin.blogs.partials.form', [
        'title' => 'Create Blog Post',
        'action' => route('admin.blogs.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Blog Post',
    ])
@endsection
