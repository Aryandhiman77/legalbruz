<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeNotification;
use App\Models\EmailOtp;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class EmailOtpController extends Controller
{
    public function __construct(private readonly EmailOtpService $otpService)
    {
        $this->middleware('guest');
    }

    public function show(Request $request): View|RedirectResponse
    {
        $challenge = $this->challenge($request);

        if (! $challenge) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your verification session has expired. Please sign in again.',
            ]);
        }

        $user = User::find($challenge['user_id']);

        if (! $user) {
            $request->session()->forget('auth_otp');

            return redirect()->route('login');
        }

        return view('auth.otp', [
            'maskedEmail' => $this->maskEmail($user->email),
            'purpose' => $challenge['purpose'],
            'resendAfter' => max(0, EmailOtpService::RESEND_SECONDS - (now()->timestamp - $challenge['last_sent_at'])),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Enter the 6-digit code sent to your email.',
            'otp.digits' => 'The verification code must be exactly 6 digits.',
        ]);

        $challenge = $this->challenge($request);

        if (! $challenge) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your verification session has expired. Please sign in again.',
            ]);
        }

        $otp = EmailOtp::query()
            ->whereKey($challenge['otp_id'])
            ->where('user_id', $challenge['user_id'])
            ->where('purpose', $challenge['purpose'])
            ->first();

        if (! $otp) {
            return back()->withErrors(['otp' => 'This code is no longer valid. Request a new code.']);
        }

        $result = $this->otpService->verify($otp, $validated['otp']);

        if ($result !== 'valid') {
            $messages = [
                'invalid' => 'That code is incorrect. Please check it and try again.',
                'expired' => 'That code has expired. Request a new code.',
                'used' => 'That code has already been used. Request a new code.',
                'locked' => 'Too many incorrect attempts. Request a new code.',
            ];

            return back()->withErrors(['otp' => $messages[$result]]);
        }

        $user = User::findOrFail($challenge['user_id']);

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::guard('web')->login($user, (bool) $challenge['remember']);
        $request->session()->forget('auth_otp');
        $request->session()->regenerate();

        if ($challenge['purpose'] === 'register') {
            try {
                Mail::to($user->email)->send(new WelcomeNotification($user));
            } catch (Throwable $exception) {
                Log::warning('Welcome email failed after OTP verification.', [
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()->intended('/home')->with(
            'status',
            $challenge['purpose'] === 'register'
                ? 'Your email is verified and your account is ready.'
                : 'Login verified successfully.',
        );
    }

    public function resend(Request $request): RedirectResponse
    {
        $challenge = $this->challenge($request);

        if (! $challenge) {
            return redirect()->route('login');
        }

        $secondsRemaining = EmailOtpService::RESEND_SECONDS - (now()->timestamp - $challenge['last_sent_at']);

        if ($secondsRemaining > 0) {
            return back()->withErrors([
                'otp' => "Please wait {$secondsRemaining} seconds before requesting another code.",
            ]);
        }

        $user = User::find($challenge['user_id']);

        if (! $user) {
            $request->session()->forget('auth_otp');

            return redirect()->route('login');
        }

        try {
            $otp = $this->otpService->issue($user, $challenge['purpose']);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['otp' => 'We could not send a new code right now. Please try again.']);
        }

        $challenge['otp_id'] = $otp->id;
        $challenge['last_sent_at'] = now()->timestamp;
        $request->session()->put('auth_otp', $challenge);

        return back()->with('status', 'A new verification code has been sent.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget('auth_otp');

        return redirect()->route('login');
    }

    private function challenge(Request $request): ?array
    {
        $challenge = $request->session()->get('auth_otp');

        if (! is_array($challenge)
            || ! isset($challenge['user_id'], $challenge['otp_id'], $challenge['purpose'], $challenge['last_sent_at'])
            || ! in_array($challenge['purpose'], ['login', 'register'], true)
            || now()->timestamp - ($challenge['started_at'] ?? 0) > 1800) {
            $request->session()->forget('auth_otp');

            return null;
        }

        return $challenge;
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible.str_repeat('•', max(3, mb_strlen($local) - mb_strlen($visible))).'@'.$domain;
    }
}
