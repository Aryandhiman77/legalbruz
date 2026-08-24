@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1" style="color:#1D3557;">Registered Users</h1>
                <p class="text-muted mb-0">View customers who have created an account on the website.</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge rounded-pill text-bg-light border px-3 py-2">{{ number_format($totalUsers) }} total</span>
                <span class="badge rounded-pill px-3 py-2" style="color:#11796f;background:#e6f7f4;">{{ number_format($verifiedUsers) }} verified</span>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2">
                    <div class="col-md-7">
                        <input name="search" class="form-control form-control-sm" value="{{ request('search') }}"
                            placeholder="Search name, email, or mobile number">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All verification statuses</option>
                            <x-admin-status-option value="verified" label="Verified" :selected="request('status') === 'verified'" />
                            <x-admin-status-option value="unverified" label="Unverified" :selected="request('status') === 'unverified'" />
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-grow-1" style="background:#2A9D8F;border:0;">Filter</button>
                        @if (request()->hasAny(['search', 'status']))
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if ($users->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle admin-list-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Mobile</th>
                                    <th>Email status</th>
                                    <th>Applications</th>
                                    <th>Registered</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>
                                            <strong style="color:#1D3557;">{{ $user->name }}</strong>
                                            <div class="small text-muted">{{ $user->email }}</div>
                                        </td>
                                        <td>{{ $user->mobile ?: 'Not provided' }}</td>
                                        <td><x-admin-status :status="$user->email_verified_at ? 'Verified' : 'Unverified'" /></td>
                                        <td>{{ number_format($user->applications_count) }}</td>
                                        <td><x-admin-date-time :value="$user->created_at" /></td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.all-applications', ['search' => $user->email]) }}" class="btn btn-sm btn-outline-primary">
                                                View applications
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $users->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-people fs-2 text-muted"></i>
                        <h5 class="mt-3">No registered users found</h5>
                        <p class="text-muted mb-0">Try changing the search or verification filter.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
