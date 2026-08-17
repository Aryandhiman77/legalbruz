@extends('layouts.app')

@section('content')
    @include('admin.careers.jobs.partials.form', [
        'title' => 'Edit Job Role',
        'action' => route('admin.career-jobs.update', $job),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
@endsection
