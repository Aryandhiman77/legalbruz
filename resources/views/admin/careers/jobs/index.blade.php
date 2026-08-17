@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1" style="color:#1D3557;">Career Job Roles</h1>
                <p class="text-muted mb-0">Create and manage opportunities displayed on the careers page.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('careers.index') }}" target="_blank" class="btn btn-outline-secondary btn-sm">View Careers Page</a>
                <a href="{{ route('admin.career-jobs.create') }}" class="btn btn-primary btn-sm" style="background:#2A9D8F;border:0;">
                    <i class="fas fa-plus"></i> Add Job Role
                </a>
            </div>
        </div>

        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" class="d-flex gap-2 flex-wrap">
                    <input name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Search job title or location">
                    <select name="status" class="form-select form-select-sm" style="max-width:180px">
                        <option value="">All statuses</option>
                        <x-admin-status-option value="open" label="Open" :selected="request('status') === 'open'" />
                        <x-admin-status-option value="closed" label="Closed" :selected="request('status') === 'closed'" />
                    </select>
                    <button class="btn btn-primary btn-sm" style="background:#2A9D8F;border:0;">Search &amp; filter</button>
                    @if (request()->hasAny(['search', 'status'])) <a href="{{ route('admin.career-jobs.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a> @endif
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if ($jobs->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle admin-list-table">
                            <thead><tr><th>Role</th><th>Location</th><th>Type</th><th>Applications</th><th>Status</th><th>Updated</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                                @foreach ($jobs as $job)
                                    <tr>
                                        <td><strong style="color:#1D3557;">{{ $job->title }}</strong><br><small class="text-muted">{{ Str::limit($job->summary, 70) }}</small></td>
                                        <td>{{ $job->location }}</td>
                                        <td><small>{{ $job->employment_type }}<br>{{ $job->workplace_type }}</small></td>
                                        <td><a href="{{ route('admin.career-applications.index', ['job' => $job->id]) }}" class="badge bg-info text-dark text-decoration-none">{{ $job->applications_count }}</a></td>
                                        <td>
                                            <x-admin-status :status="$job->is_open ? 'Open' : 'Closed'" />
                                            @if ($job->application_deadline)<br><small class="text-muted">Until {{ $job->application_deadline->format('d M Y') }}</small>@endif
                                        </td>
                                        <td><x-admin-date-time :value="$job->updated_at" /></td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('admin.career-jobs.edit', $job) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                                @if ($job->is_open)<a href="{{ route('careers.show', $job) }}" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>@endif
                                                <form method="POST" action="{{ route('admin.career-jobs.destroy', $job) }}"
                                                    data-swal-confirm data-swal-title="Delete this job role?"
                                                    data-swal-text="Roles with applications cannot be deleted." data-swal-icon="warning"
                                                    data-swal-confirm-text="Yes, delete">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $jobs->links() }}
                @else
                    <div class="text-center py-5"><h5>No job roles found</h5><p class="text-muted">Create your first job role to publish it on the careers page.</p></div>
                @endif
            </div>
        </div>
    </div>
@endsection
