@extends('layouts.app')

@section('title', 'Login | Legal Bruz')
@section('body_class', 'auth-body')
@section('head')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<section class="auth-page">
    <div class="auth-shell auth-shell--login">
        @include('auth.partials.panel', ['mode' => 'login'])

        <div class="auth-form-panel">
            <div class="auth-form-wrap">
                <header class="auth-heading">
                    <span class="auth-heading-icon"><i class="bi bi-box-arrow-in-right"></i></span>
                    <div>
                        <h2>Login</h2>
                        <p>Sign in to your account to continue</p>
                    </div>
                </header>

                @if (session('status'))
                    <div class="auth-alert" role="status"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" data-auth-form>
                    @csrf

                    <div class="auth-field">
                        <label for="email">Email Address</label>
                        <div class="auth-input-wrap">
                            <i class="bi bi-envelope auth-input-icon" aria-hidden="true"></i>
                            <input id="email" type="email" class="auth-input @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required maxlength="255"
                                autocomplete="email" inputmode="email" autofocus placeholder="Enter your email">
                        </div>
                        @error('email')<span class="auth-error" role="alert">{{ $message }}</span>@enderror
                    </div>

                    <div class="auth-field">
                        <label for="password">Password</label>
                        <div class="auth-input-wrap">
                            <i class="bi bi-lock auth-input-icon" aria-hidden="true"></i>
                            <input id="password" type="password" class="auth-input @error('password') is-invalid @enderror"
                                name="password" required autocomplete="current-password" placeholder="Enter your password">
                            <button type="button" class="auth-toggle-password" data-password-toggle="password" aria-label="Show password">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password')<span class="auth-error" role="alert">{{ $message }}</span>@enderror
                    </div>

                    <div class="auth-options">
                        <label class="auth-check" for="remember">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Remember me</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a class="auth-link" href="{{ route('password.request') }}">Forgot password?</a>
                        @endif
                    </div>

                    <button type="submit" class="auth-submit" data-loading-text="Sending code…">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                        <span data-submit-label>Login securely</span>
                    </button>

                    <div class="auth-separator">or</div>
                    <p class="auth-switch">New to Legal Bruz? <a class="auth-link" href="{{ route('register') }}">Create an account</a></p>
                </form>
            </div>
        </div>
    </div>
</section>

@include('auth.partials.scripts')
@endsection
