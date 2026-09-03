<?php

namespace App\Services;

use App\Mail\EmailOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailOtpService
{
    public const EXPIRY_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_SECONDS = 60;

    public function issue(User $user, string $purpose): EmailOtp
    {
        $code = (string) random_int(100000, 999999);

        $otp = DB::transaction(function () use ($user, $purpose, $code) {
            EmailOtp::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            return EmailOtp::create([
                'user_id' => $user->id,
                'purpose' => $purpose,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            ]);
        });

        Mail::to($user->email)->send(new EmailOtpMail(
            $user,
            $code,
            $purpose,
            self::EXPIRY_MINUTES,
        ));

        return $otp;
    }

    public function verify(EmailOtp $otp, string $code): string
    {
        return DB::transaction(function () use ($otp, $code) {
            $otp = EmailOtp::query()->lockForUpdate()->find($otp->id);

            if (! $otp || $otp->consumed_at !== null) {
                return 'used';
            }

            if ($otp->expires_at->isPast()) {
                return 'expired';
            }

            if ($otp->attempts >= self::MAX_ATTEMPTS) {
                return 'locked';
            }

            if (! Hash::check($code, $otp->code_hash)) {
                $otp->increment('attempts');

                return $otp->attempts >= self::MAX_ATTEMPTS ? 'locked' : 'invalid';
            }

            $otp->forceFill(['consumed_at' => now()])->save();

            return 'valid';
        });
    }
}
