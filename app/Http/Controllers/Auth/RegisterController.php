<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Throwable;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct(private readonly EmailOtpService $otpService)
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\pL\pM .'-]+$/u"],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'regex:/^(?:(?:\+?91)|0)?[6-9][0-9]{9}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'name.regex' => 'The name may only contain letters, spaces, apostrophes, periods, and hyphens.',
            'mobile.required' => 'An Indian mobile number is required.',
            'mobile.regex' => 'Enter a valid Indian mobile number starting with 6, 7, 8, or 9.',
        ]);
    }

    public function register(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
            'mobile' => preg_replace('/[\s()-]+/', '', (string) $request->input('mobile')),
        ]);

        $this->validator($request->all())->validate();
        $user = $this->create($request->all());

        try {
            $otp = $this->otpService->issue($user, 'register');
        } catch (Throwable $exception) {
            report($exception);
            $user->delete();

            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'We could not send a verification code. Your account was not created; please try again.']);
        }

        $request->session()->put('auth_otp', [
            'user_id' => $user->id,
            'otp_id' => $otp->id,
            'purpose' => 'register',
            'remember' => false,
            'started_at' => now()->timestamp,
            'last_sent_at' => now()->timestamp,
        ]);

        return redirect()->route('auth.otp.show')
            ->with('status', 'Your account is almost ready. Enter the code sent to your email.');
    }

    protected function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $this->normalizeIndianMobile($data['mobile']),
            'password' => Hash::make($data['password']),
        ]);
    }

    private function normalizeIndianMobile(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        return $digits;
    }
}
