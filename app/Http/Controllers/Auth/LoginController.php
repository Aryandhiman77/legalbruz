<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct(private readonly EmailOtpService $otpService)
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request)
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->credentials($request);
        $provider = Auth::guard('web')->getProvider();
        $user = $provider->retrieveByCredentials($credentials);

        if (! $user || ! $provider->validateCredentials($user, $credentials)) {
            $this->incrementLoginAttempts($request);

            throw ValidationException::withMessages([
                $this->username() => [trans('auth.failed')],
            ]);
        }

        $this->clearLoginAttempts($request);

        try {
            $otp = $this->otpService->issue($user, 'login');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'We could not send a verification code right now. Please try again.']);
        }

        $this->storePendingChallenge($request, $user, $otp->id);

        return redirect()->route('auth.otp.show')
            ->with('status', 'We sent a 6-digit verification code to your email.');
    }

    protected function validateLogin(Request $request): void
    {
        $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);
    }

    private function storePendingChallenge(Request $request, User $user, int $otpId): void
    {
        $request->session()->put('auth_otp', [
            'user_id' => $user->id,
            'otp_id' => $otpId,
            'purpose' => 'login',
            'remember' => $request->boolean('remember'),
            'started_at' => now()->timestamp,
            'last_sent_at' => now()->timestamp,
        ]);
    }
}
