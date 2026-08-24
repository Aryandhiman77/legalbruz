<?php

namespace Tests\Feature;

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

        $response->assertRedirect('/home');
        $this->assertDatabaseHas('users', [
            'email' => 'mobile-user@example.com',
            'mobile' => '9876543210',
        ]);

        $this->get('/home')
            ->assertOk()
            ->assertSee('Loved by Thousands');
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
