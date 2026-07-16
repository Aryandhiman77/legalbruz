@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <!-- Admin Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h2><i class="fas fa-cog"></i> Admin Dashboard</h2>
                <p class="text-muted">Manage Trademark Applications</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <a href="{{ route('admin.applications') }}" class="text-decoration-none">
                    <div class="card text-center shadow-sm border-left-primary">
                        <div class="card-body">
                            <h3 class="text-warning">{{ $pendingCount }}</h3>
                            <p class="text-muted mb-0">Pending Review</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.all-applications') }}" class="text-decoration-none">
                    <div class="card text-center shadow-sm border-left-success">
                        <div class="card-body">
                            <h3 class="text-success">{{ $approvedCount }}</h3>
                            <p class="text-muted mb-0">Approved</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm border-left-info">
                    <div class="card-body">
                        <h3 class="text-info">{{ $filedCount }}</h3>
                        <p class="text-muted mb-0">Filed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.all-applications', ['status' => \App\Support\TrademarkWorkflow::REGISTRY_REGISTERED]) }}" class="text-decoration-none">
                    <div class="card text-center shadow-sm border-left-success">
                        <div class="card-body">
                            <h3 class="text-success">{{ $registeredCount ?? 0 }}</h3>
                            <p class="text-muted mb-0">Registered</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header admin-dashboard-section-head">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="admin-quick-actions">
                        <a href="{{ route('admin.applications') }}" class="btn btn-primary admin-quick-action-btn">
                            <i class="fas fa-list"></i> View Pending Applications
                        </a>
                        <a href="{{ route('admin.all-applications') }}" class="btn btn-info admin-quick-action-btn">
                            <i class="fas fa-chart-bar"></i> View All Applications
                        </a>
                        <a href="{{ route('admin.stuck-trademark.index') }}" class="btn btn-dark admin-quick-action-btn">
                            <i class="fas fa-life-ring"></i> View Recovery Cases
                        </a>
                        <a href="{{ route('admin.trademark-opposition.index') }}" class="btn btn-success admin-quick-action-btn">
                            <i class="fas fa-shield-alt"></i> View Defence Cases
                        </a>
                        <a href="{{ route('admin.trademark-opposition.oppose.index') }}" class="btn btn-secondary admin-quick-action-btn">
                            <i class="fas fa-gavel"></i> View Oppose Cases
                        </a>
                        <a href="{{ route('admin.examination-reply.index') }}" class="btn btn-primary admin-quick-action-btn">
                            <i class="fas fa-file-signature"></i> View Objection Reply Cases
                        </a>
                        <a href="{{ route('admin.discount-coupons.index') }}" class="btn btn-warning text-dark admin-quick-action-btn">
                            <i class="fas fa-tags"></i> Discount Coupons
                        </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow Info -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header admin-dashboard-section-head">
                        <h5 class="mb-0">Admin Workflow</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>1. Review Application</h6>
                                <p class="text-muted">Check applicant details, brand information, and usage type.</p>
                            </div>
                            <div class="col-md-4">
                                <h6>2. Verify Documents</h6>
                                <p class="text-muted">Ensure all KYC documents are valid, clear, and properly uploaded.</p>
                            </div>
                            <div class="col-md-4">
                                <h6>3. Approve & File</h6>
                                <p class="text-muted">Generate affidavit & POA, approve, and file with trademark registry.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Pending -->
        @if ($pendingCount > 0)
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-white admin-recent-pending-head">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-circle"></i>
                                Recent Pending Applications
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <!-- This would be populated from query -->
                                <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <div>
                                        <h6 class="mb-1">Awaiting applications</h6>
                                        <small class="text-muted">{{ $pendingCount }} applications pending your
                                            review</small>
                                    </div>
                                    <a href="{{ route('admin.applications') }}" class="btn btn-sm btn-primary">Review</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .border-left-primary {
            border-left: 4px solid #007bff !important;
        }

        .border-left-success {
            border-left: 4px solid #28a745 !important;
        }

        .border-left-info {
            border-left: 4px solid #17a2b8 !important;
        }

        .border-left-secondary {
            border-left: 4px solid #6c757d !important;
        }

        .admin-dashboard-section-head {
            background: #294d78 !important;
            color: #ffffff !important;
            border-bottom: 0;
            padding: 18px 24px;
        }

        .admin-dashboard-section-head h5 {
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: 0;
        }

        .admin-recent-pending-head,
        .admin-recent-pending-head h5,
        .admin-recent-pending-head i {
            color: #ffffff !important;
        }

        .admin-quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px 16px;
            align-items: center;
        }

        .admin-quick-action-btn {
            min-height: 42px;
            padding: 9px 18px;
            border-radius: 7px;
            font-size: 0.92rem;
            font-weight: 700;
            line-height: 1.15;
        }
    </style>
@endsection
