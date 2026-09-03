@extends('emails.layouts.simple')

@section('email_title', $purpose === 'register' ? 'Verify your account' : 'Login verification')
@section('status_icon', '🔐')
@section('heading')
    {{ $purpose === 'register' ? 'Verify your email' : 'Confirm your login' }}
@endsection

@section('body')
    <p>Hello {{ $user->name }},</p>

    <p>
        {{ $purpose === 'register'
            ? 'Use the verification code below to finish creating your Legal Bruz account.'
            : 'Use the verification code below to complete your Legal Bruz login.' }}
    </p>

    <div style="margin: 28px 0; padding: 20px; border-radius: 12px; background: #f1fbf9; text-align: center;">
        <div style="margin-bottom: 8px; color: #667085; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;">One-time code</div>
        <div style="color: #071f48; font-size: 34px; font-weight: 800; letter-spacing: .24em;">{{ $code }}</div>
    </div>

    <p>This code expires in {{ $expiresInMinutes }} minutes and can only be used once.</p>
    <p>If you did not request this code, you can safely ignore this email. Never share this code with anyone.</p>
@endsection
