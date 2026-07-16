@extends('layouts.app')

@section('content')
    @include('admin.discount-coupons.partials.form', [
        'title' => 'Create Discount Coupon',
        'subtitle' => 'Fill in the details below to create a new discount coupon.',
        'action' => route('admin.discount-coupons.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Coupon',
    ])
@endsection
