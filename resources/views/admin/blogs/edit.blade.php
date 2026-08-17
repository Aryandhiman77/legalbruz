@extends('layouts.app')
@section('content')
    @include('admin.blogs.partials.form', [
        'title' => 'Edit Blog Post',
        'action' => route('admin.blogs.update', $blog),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
@endsection
