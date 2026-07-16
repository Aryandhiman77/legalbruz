@extends('emails.layouts.simple')

@section('email_title')
    @if ($decision === 'approved')
        Application Approved
    @elseif ($decision === 'changes_requested')
        Application Changes Requested
    @else
        Application Review Update
    @endif
@endsection

@section('status_icon', $decision === 'approved' ? '✓' : '!')

@section('heading')
    @if ($decision === 'approved')
        Application <strong>Approved!</strong>
    @elseif ($decision === 'changes_requested')
        Application <strong>Changes Requested</strong>
    @else
        Application <strong>Review Update</strong>
    @endif
@endsection

@section('body')
    <p>Dear {{ $user->name }},</p>

    @if ($decision === 'approved')
        <p>Your trademark application for <strong>{{ $application->brand_name ?? 'your mark' }}</strong> has been approved by our administrator. Please review the onboarding documents and complete the required actions from your application status page.</p>
        <div class="info-box">
            The onboarding documents shared by the admin team are attached to this email for your review.
        </div>
    @else
        <p>Our team has requested changes for your trademark application for <strong>{{ $application->brand_name ?? 'your mark' }}</strong>. Please review the note below and submit the corrected details.</p>
    @endif

    <div class="summary-card">
        <div class="summary-title">Application Summary</div>
        <table class="summary-table">
            <tr>
                <th>Trademark Name</th>
                <td>{{ $application->brand_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Application ID</th>
                <td>#{{ $application->id }}</td>
            </tr>
            <tr>
                <th>Applicant</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    @if ($decision === 'approved')
                        <span class="status-pill">Approved</span>
                    @elseif ($decision === 'changes_requested')
                        <span class="status-pill warning">Changes Requested</span>
                    @else
                        <span class="status-pill warning">Review Update</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @if (!empty($note))
        <div class="admin-note">
            <strong>Admin Note:</strong>
            <div>{{ $note }}</div>
        </div>
    @endif

    <div class="cta-wrap">
        <a href="{{ route('trademark.status', $application->id) }}" class="primary-button">Open Application Status</a>
    </div>
@endsection
