@extends('layouts.app')

@section('content')
    <style>
        .admin-opp{max-width:1240px;margin:-12px auto 32px;padding:0 18px}.admin-opp-head{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:16px}.admin-opp h1{font-size:1.6rem;margin:0;color:#08245a}.admin-card{background:#fff;border:1px solid #dfe8f4;border-radius:9px;box-shadow:0 12px 26px rgba(8,36,90,.06);padding:18px}.admin-filter{display:flex;gap:10px;align-items:center}.admin-input{min-height:38px;border:1px solid #cad6e8;border-radius:7px;padding:7px 10px}.admin-btn{min-height:38px;border:0;border-radius:7px;background:#2563eb;color:#fff;font-weight:900;padding:0 14px;text-decoration:none;display:inline-flex;align-items:center}.admin-table th{font-size:.78rem;text-transform:uppercase;color:#65728a}.admin-status{display:inline-flex;border-radius:999px;padding:5px 10px;background:#eaf1ff;color:#174ea6;font-weight:900;font-size:.78rem}@media(max-width:767px){.admin-opp-head{display:block}.admin-filter{margin-top:12px;display:grid}}
    </style>
    <div class="admin-opp">
        <div class="admin-opp-head">
            <div>
                <h1>Trademark Opposition Defence Cases</h1>
                <p class="text-muted mb-0">Flow A: Defend My Trademark</p>
            </div>
            <form class="admin-filter" method="GET">
                <select class="admin-input" name="status">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button class="admin-btn" type="submit">Filter</button>
            </form>
        </div>
        <div class="admin-card table-responsive">
            <table class="table admin-table align-middle">
                <thead><tr><th>Case</th><th>User</th><th>Trademark</th><th>Deadline</th><th>Status</th><th>Payment</th><th></th></tr></thead>
                <tbody>
                    @forelse ($cases as $case)
                        <tr>
                            <td><strong>{{ $case->case_number }}</strong><br><small>{{ $case->application_number }}</small></td>
                            <td>{{ $case->applicant_name }}<br><small>{{ $case->email }}</small></td>
                            <td>{{ $case->trademark_name }}<br><small>Class {{ $case->trademark_class }}</small></td>
                            <td>{{ $case->counter_statement_deadline->format('d M Y') }}<br><small>{{ $case->deadline_status_label }}</small></td>
                            <td><span class="admin-status">{{ $case->current_admin_status }}</span></td>
                            <td>{{ ucfirst($case->payment_status) }}</td>
                            <td><a class="admin-btn" href="{{ route('admin.trademark-opposition.show', $case) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No opposition defence cases found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $cases->links() }}
        </div>
    </div>
@endsection
