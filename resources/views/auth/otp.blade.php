@extends('layouts.app')

@section('title', 'Verify Email Code | Legal Bruz')
@section('body_class', 'auth-body')
@section('head')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<section class="auth-page">
    <div class="auth-shell auth-shell--otp">
        @include('auth.partials.panel', ['mode' => 'otp'])

        <div class="auth-form-panel">
            <div class="auth-form-wrap">
                <header class="auth-heading">
                    <span class="auth-heading-icon"><i class="bi bi-envelope-check"></i></span>
                    <div>
                        <h2>Check your email</h2>
                        <p>We sent a 6-digit code to {{ $maskedEmail }}</p>
                    </div>
                </header>

                @if (session('status'))
                    <div class="auth-alert" role="status"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('auth.otp.verify') }}" data-auth-form>
                    @csrf
                    <div class="auth-field">
                        <label for="otp">Verification Code</label>
                        <input id="otp" type="text" class="auth-input otp-input @error('otp') is-invalid @enderror"
                            name="otp" value="{{ old('otp') }}" required inputmode="numeric" autocomplete="one-time-code"
                            maxlength="6" pattern="[0-9]{6}" autofocus aria-describedby="otp-help"
                            placeholder="000000">
                        <small id="otp-help" class="auth-help">The code expires after 10 minutes and allows five attempts.</small>
                        @error('otp')<span class="auth-error" role="alert">{{ $message }}</span>@enderror
                    </div>

                    <button type="submit" class="auth-submit" data-loading-text="Verifying…">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                        <span data-submit-label>{{ $purpose === 'register' ? 'Verify & create account' : 'Verify & login' }}</span>
                    </button>
                </form>

                <div class="otp-actions">
                    <form method="POST" action="{{ route('auth.otp.resend') }}">
                        @csrf
                        <button id="resend-code" type="submit" class="auth-link otp-resend" data-seconds="{{ $resendAfter }}" {{ $resendAfter > 0 ? 'disabled' : '' }}>
                            Resend code<span id="resend-countdown">{{ $resendAfter > 0 ? ' in '.$resendAfter.'s' : '' }}</span>
                        </button>
                    </form>
                    <span aria-hidden="true">•</span>
                    <form method="POST" action="{{ route('auth.otp.cancel') }}">
                        @csrf
                        <button type="submit" class="auth-link">Back to login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@include('auth.partials.scripts')
<script>
    const otpInput = document.getElementById('otp');
    otpInput.addEventListener('input', () => otpInput.value = otpInput.value.replace(/\D/g, '').slice(0, 6));

    const resendButton = document.getElementById('resend-code');
    const countdown = document.getElementById('resend-countdown');
    let seconds = Number(resendButton.dataset.seconds);
    if (seconds > 0) {
        const timer = window.setInterval(() => {
            seconds -= 1;
            countdown.textContent = seconds > 0 ? ` in ${seconds}s` : '';
            if (seconds <= 0) {
                window.clearInterval(timer);
                resendButton.disabled = false;
            }
        }, 1000);
    }
</script>
@endsection
