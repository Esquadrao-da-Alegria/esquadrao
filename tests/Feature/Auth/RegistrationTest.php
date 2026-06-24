<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_redirects_to_login()
    {
        $response = $this->get(route('register'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Novos cadastros são realizados somente por convite de um administrador.');
    }

    public function test_new_users_cannot_bypass_invitation_flow_with_public_registration()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
