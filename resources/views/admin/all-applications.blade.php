@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0" style="color: #1D3557;">📋 All Applications</h1>
                        <p class="text-muted mb-0">Complete list of trademark applications</p>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.all-applications') }}" class="d-flex gap-2 flex-wrap">
                            <div class="flex-grow-1" style="min-width: 200px;">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search by name, email, trademark..." value="{{ request('search') }}">
                            </div>
                            <select name="status" class="form-select form-select-sm" style="max-width: 150px;">
                                <option value="">All Status</option>
                                <option value="UNDER_REVIEW" {{ request('status') == 'UNDER_REVIEW' ? 'selected' : '' }}>
                                    ⏳ Under Review
                                </option>
                                <option value="ONBOARDING_PENDING" {{ request('status') == 'ONBOARDING_PENDING' ? 'selected' : '' }}>
                                    📦 Onboarding
                                </option>
                                <option value="AWAITING_APPROVAL" {{ request('status') == 'AWAITING_APPROVAL' ? 'selected' : '' }}>
                                    ✅ Awaiting Approval
                                </option>
                                <option value="FILED" {{ request('status') == 'FILED' ? 'selected' : '' }}>
                                    📁 Filed
                                </option>
                                <option value="REGISTERED" {{ request('status') == 'REGISTERED' ? 'selected' : '' }}>
                                    ® Registered
                                </option>
                                <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>
                                    ❌ Rejected
                                </option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm"
                                style="background-color: #2A9D8F; border: none;">
                                🔍 Filter
                            </button>
                            @if (request('search') || request('status'))
                                <a href="{{ route('admin.all-applications') }}" class="btn btn-outline-secondary btn-sm">
                                    ✕ Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✅ Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Applications Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if ($applications->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead style="background-color: #f8f9fa; border-bottom: 2px solid #2A9D8F;">
                                <tr>
                                    <th style="color: #1D3557; font-weight: 600;">ID</th>
                                    <th style="color: #1D3557; font-weight: 600;">Applicant</th>
                                    <th style="color: #1D3557; font-weight: 600;">Trademark</th>
                                    <th style="color: #1D3557; font-weight: 600;">Status</th>
                                    <th style="color: #1D3557; font-weight: 600;">Submitted</th>
                                    <th style="color: #1D3557; font-weight: 600;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($applications as $app)
                                    <tr>
                                        <td>
                                            <span class="badge" style="background-color: #2A9D8F;">
                                                #{{ $app->id }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $app->user->name ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $app->user->email ?? 'N/A' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-truncate">{{ $app->brand_name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $adminStatusLabel = ($app->registry_status === \App\Support\TrademarkWorkflow::REGISTRY_REGISTERED || filled($app->registered_at))
                                                    ? 'Registered'
                                                    : $app->status_label;
                                            @endphp
                                            <span class="badge bg-light text-dark border">{{ $adminStatusLabel }}</span>
                                        </td>
                                        <td>
                                            <small>{{ $app->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.review-application', $app->id) }}"
                                                class="btn btn-sm btn-primary"
                                                style="background-color: #2A9D8F; border: none;">
                                                👁️ View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $applications->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                        <h5 style="color: #1D3557;">No applications found</h5>
                        <p class="text-muted mb-0">Try adjusting your filters or search criteria</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

        .btn-primary {
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(42, 157, 143, 0.3);
        }
    </style>
@endsection
