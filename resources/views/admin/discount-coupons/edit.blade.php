@extends('layouts.app')

@section('content')
    @include('admin.discount-coupons.partials.form', [
        'title' => 'Edit Discount Coupon',
        'subtitle' => 'Update coupon details, availability, and applicable users.',
        'action' => route('admin.discount-coupons.update', $coupon),
        'method' => 'PUT',
        'submitLabel' => 'Update Coupon',
    ])
@endsection
