<?php

namespace Tests\Feature;

use App\Mail\EmailOtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_and_normalizes_an_indian_mobile_number(): void
    {
        Mail::fake();

        $response = $this->post(route('register'), [
            'name' => 'Mobile User',
            'email' => 'mobile-user@example.com',
            'mobile' => '+919876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('auth.otp.show'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'mobile-user@example.com',
            'mobile' => '9876543210',
        ]);

        $code = null;
        Mail::assertSent(EmailOtpMail::class, function (EmailOtpMail $mail) use (&$code) {
            $code = $mail->code;

            return $mail->purpose === 'register';
        });

        $this->post(route('auth.otp.verify'), ['otp' => $code])
            ->assertRedirect('/home');

        $this->assertAuthenticated();
        $this->assertNotNull(auth()->user()->email_verified_at);
    }

    public function test_registration_rejects_missing_or_invalid_indian_mobile_numbers(): void
    {
        Mail::fake();

        $this->from(route('register'))->post(route('register'), [
            'name' => 'Missing Mobile',
            'email' => 'missing-mobile@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register'))->assertSessionHasErrors('mobile');

        $this->from(route('register'))->post(route('register'), [
            'name' => 'Invalid Mobile',
            'email' => 'invalid-mobile@example.com',
            'mobile' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register'))->assertSessionHasErrors('mobile');

        $this->assertDatabaseMissing('users', ['email' => 'missing-mobile@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'invalid-mobile@example.com']);
    }
}
