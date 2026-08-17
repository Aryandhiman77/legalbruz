@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1" style="color: #1D3557;">Discount Coupons</h1>
                <p class="text-muted mb-0">Manage coupon codes, status, and applicable users.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
                <a href="{{ route('admin.discount-coupons.create') }}" class="btn btn-primary btn-sm"
                    style="background-color: #2A9D8F; border: none;">
                    <i class="fas fa-plus"></i> Create Coupon
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.discount-coupons.index') }}" class="d-flex gap-2 flex-wrap">
                    <div class="flex-grow-1" style="min-width: 220px;">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search by coupon code or title" value="{{ request('search') }}">
                    </div>
                    <select name="status" class="form-select form-select-sm" style="max-width:180px">
                        <option value="">All statuses</option>
                        @foreach (['active' => 'Active', 'scheduled' => 'Scheduled', 'expired' => 'Expired', 'inactive' => 'Inactive'] as $value => $label)
                            <x-admin-status-option :value="$value" :label="$label"
                                :selected="request('status') === $value" />
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm" style="background-color: #2A9D8F; border: none;">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.discount-coupons.index') }}" class="btn btn-outline-secondary btn-sm">
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if ($coupons->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle admin-list-table">
                            <thead style="background-color: #f8f9fa; border-bottom: 2px solid #2A9D8F;">
                                <tr>
                                    <th style="color: #1D3557;">Code</th>
                                    <th style="color: #1D3557;">Title</th>
                                    <th style="color: #1D3557;">Discount</th>
                                    <th style="color: #1D3557;">Applies To</th>
                                    <th style="color: #1D3557;">Users</th>
                                    <th style="color: #1D3557;">Status</th>
                                    <th style="color: #1D3557;">Validity</th>
                                    <th style="color: #1D3557;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($coupons as $coupon)
                                    <tr>
                                        <td><span class="badge bg-dark">{{ $coupon->code }}</span></td>
                                        <td>
                                            <strong>{{ $coupon->title }}</strong>
                                            @if ($coupon->description)
                                                <br><small class="text-muted">{{ Str::limit($coupon->description, 60) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $coupon->discount_label }}</td>
                                        <td>{{ $coupon->applies_to_label }}</td>
                                        <td>
                                            {{ $coupon->applicable_users === 'specific_users' ? count($coupon->selected_user_ids ?? []) . ' selected' : 'All Users' }}
                                        </td>
                                        <td>
                                            <x-admin-status :status="$coupon->status_label" />
                                        </td>
                                        <td>
                                            <small>
                                                {{ $coupon->starts_at?->format('d M Y') ?? 'Now' }}
                                                -
                                                {{ $coupon->ends_at?->format('d M Y') ?? 'No end' }}
                                            </small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.discount-coupons.edit', $coupon) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $coupons->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">%</div>
                        <h5 style="color: #1D3557;">No discount coupons found</h5>
                        <p class="text-muted">Create your first coupon to show it here.</p>
                        <a href="{{ route('admin.discount-coupons.create') }}" class="btn btn-primary"
                            style="background-color: #2A9D8F; border: none;">
                            Create Coupon
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
