<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders_with_unavailable_options_marked_and_no_public_registration(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign in')
            ->assertDontSee('Register')
            ->assertSee('Continue with Google')
            ->assertSee('Continue with Microsoft')
            ->assertSee('Forgot password?')
            ->assertSee('aria-disabled="true"', false)
            ->assertSee('disabled', false);
    }

    public function test_valid_credentials_authenticate_and_rotate_the_session_id(): void
    {
        $user = User::factory()->create([
            'email' => 'person@example.test',
            'password' => Hash::make('correct horse battery staple'),
        ]);

        $this->get('/login')->assertOk();
        $oldSessionId = session()->getId();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct horse battery staple',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    public function test_invalid_credentials_do_not_authenticate_and_return_a_validation_error(): void
    {
        User::factory()->create(['email' => 'person@example.test']);

        $this->from('/login')->post('/login', [
            'email' => 'person@example.test',
            'password' => 'wrong password',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_log_in_or_use_authenticated_services(): void
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive@example.test',
            'password' => Hash::make('correct horse battery staple'),
        ]);

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'correct horse battery staple',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($user)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_logout_invalidates_the_session_and_regenerates_the_csrf_token(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['marker' => 'remove-me']);
        $sessionId = session()->getId();
        $token = session()->token();

        $this->post('/logout')->assertRedirect();

        $this->assertGuest();
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertNotSame($token, session()->token());
        $this->assertFalse(session()->has('marker'));
    }

    public function test_guests_cannot_access_administrative_routes(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/admin/campuses')->assertRedirect('/login');
        $this->get('/admin/zones')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_their_account_workspace(): void
    {
        $user = User::factory()->create([
            'name' => 'Account Review User',
            'email' => 'account@example.test',
        ]);

        $this->actingAs($user)
            ->get('/account')
            ->assertOk()
            ->assertSee('My account')
            ->assertSee('Profile details')
            ->assertSee('Access &amp; preferences', false)
            ->assertSee($user->email);
    }

    public function test_login_attempts_are_limited_per_email_and_ip(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->from('/login')->post('/login', [
                'email' => 'throttle@example.test',
                'password' => 'wrong password',
            ])->assertRedirect('/login');
        }

        $this->post('/login', [
            'email' => 'throttle@example.test',
            'password' => 'wrong password',
        ])->assertStatus(429);
    }
}
