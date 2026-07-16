@extends('layouts.app')

@section('content')
    <style>
        .admin-err{max-width:1280px;margin:-10px auto 36px;padding:0 18px;color:#22324a}.admin-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:18px}.admin-head h1{font-size:1.55rem;font-weight:900;color:#102a4c;margin:0}.admin-head p{margin:6px 0 0;color:#607089;font-weight:700}.admin-btn{border:0;border-radius:7px;background:#2a9d8f;color:#fff!important;text-decoration:none;font-weight:900;min-height:40px;padding:0 14px;display:inline-flex;align-items:center}.admin-card{background:#fff;border:1px solid #dfe8f4;border-radius:9px;box-shadow:0 12px 28px rgba(8,36,90,.06);overflow:hidden;padding:0!important}.admin-card-body{padding:20px}.admin-filter{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}.admin-input{min-height:40px;border:1px solid #d9e0ea;border-radius:7px;padding:8px 10px}.admin-table{width:100%;border-collapse:collapse}.admin-table th{background:#f7f9fc;color:#202633;font-size:.78rem;text-transform:uppercase;font-weight:900;text-align:left;padding:12px}.admin-table td{border-top:1px solid #e6ebf2;padding:12px;color:#3f4b5d}.badge{display:inline-flex;border-radius:999px;padding:7px 11px;font-weight:900;font-size:.78rem}.badge.blue{background:#eaf1ff;color:#174ea6}.badge.green{background:#dcfce7;color:#15803d}.badge.yellow{background:#fef3c7;color:#92400e}.badge.red{background:#fee2e2;color:#b91c1c}@media(max-width:780px){.admin-table{display:block;overflow-x:auto;white-space:nowrap}.admin-head{display:block}}
    </style>

    <div class="admin-err">
        <div class="admin-head">
            <div>
                <h1>Examination Report Reply Cases</h1>
                <p>Trademark Objection Reply workflow</p>
            </div>
            <a class="admin-btn" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
        </div>

        <section class="admin-card">
            <div class="admin-card-body">
                <form class="admin-filter" method="GET">
                    <select class="admin-input" name="status">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button class="admin-btn" type="submit">Filter</button>
                </form>
                <table class="admin-table">
                    <thead><tr><th>Case</th><th>Trademark</th><th>Client</th><th>Deadline</th><th>Status</th><th>Payment</th><th>Action</th></tr></thead>
                    <tbody>
                    @forelse ($cases as $case)
                        @php
                            $displayStatus = $case->current_admin_status;
                            if (
                                $case->current_admin_status === \App\Support\ExaminationReportReplyWorkflow::ADMIN_EVIDENCE_SUBMITTED
                                && $case->documents->where('uploaded_by', 'client')->where('review_status', 'reuploaded')->isNotEmpty()
                            ) {
                                $displayStatus = 'Evidences Reuploaded';
                            }
                        @endphp
                        <tr>
                            <td><strong>{{ $case->case_number }}</strong><br><small>{{ $case->application_number }}</small></td>
                            <td>{{ $case->trademark_name }}<br><small>Class {{ $case->trademark_class }}</small></td>
                            <td>{{ $case->applicant_name }}</td>
                            <td><span class="badge {{ $case->deadline_status }}">{{ $case->reply_deadline->format('d M Y') }}</span></td>
                            <td><span class="badge blue">{{ $displayStatus }}</span></td>
                            <td>{{ ucfirst($case->payment_status) }}</td>
                            <td><a class="admin-btn" href="{{ route('admin.examination-reply.show', $case) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No Examination Report Reply cases yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="mt-3">{{ $cases->links() }}</div>
            </div>
        </section>
    </div>
@endsection
