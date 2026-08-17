@extends('layouts.app')

@section('content')
    @include('admin.careers.jobs.partials.form', [
        'title' => 'Create Job Role',
        'action' => route('admin.career-jobs.store'),
        'method' => 'POST',
        'submitLabel' => 'Publish Job Role',
    ])
@endsection
