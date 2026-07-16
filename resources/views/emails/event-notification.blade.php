@extends('emails.layouts.simple')

@php
    $bodyMessage = $notificationMessage;
    $adminNote = null;
    $noteLabel = 'Admin Note';

    if (str_contains($notificationMessage, "\n\nAdmin note: ")) {
        [$bodyMessage, $adminNote] = explode("\n\nAdmin note: ", $notificationMessage, 2);
    } elseif (str_contains($notificationMessage, "\n\nRequest note: ")) {
        [$bodyMessage, $adminNote] = explode("\n\nRequest note: ", $notificationMessage, 2);
        $noteLabel = 'Request Note';
    }
@endphp

@section('email_title', $title)
@section('status_icon', '✓')
@section('heading')
    {{ $title }}
@endsection

@section('body')
    <p>Dear {{ $user->name }},</p>

    <p>{!! nl2br(e($bodyMessage)) !!}</p>

    @if (!empty($mailAttachments))
        <div class="info-box">
            The document{{ count($mailAttachments) === 1 ? '' : 's' }} shared by the admin team {{ count($mailAttachments) === 1 ? 'is' : 'are' }} attached to this email.
        </div>
    @endif

    @if (filled($adminNote))
        <div class="admin-note">
            <strong>{{ $noteLabel }}:</strong>
            <div>{!! nl2br(e($adminNote)) !!}</div>
        </div>
    @endif

    @if (!empty($actionUrl))
        <div class="cta-wrap">
            <a href="{{ $actionUrl }}" class="primary-button">{{ $actionText ?? 'Open Application Status' }}</a>
        </div>
    @endif
@endsection
