<?php

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

describe('Auth', function () {
    it('can login with valid credentials', function () {
        $response = postJson('/api/login', [
            'email' => 'admin@inventory.api',
            'password' => 'admin',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'access_token', 'token_type']);
    });

    it('cannot login with invalid credentials', function () {
        $response = postJson('/api/login', [
            'email' => 'admin@inventory.api',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('cannot login with non-existent email', function () {
        $response = postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('can get own profile', function () {
        [$user, $token] = loginAsAdmin();

        $response = getJson('/api/me', authHeaders($token));

        $response->assertStatus(200)
            ->assertJson(['name' => $user->name, 'email' => $user->email]);
    });

    it('cannot get profile without authentication', function () {
        getJson('/api/me')->assertStatus(401);
    });

    it('can change password', function () {
        [, $token] = loginAsAdmin();

        postJson('/api/change-password', [
            'current_password' => 'admin',
            'new_password' => 'NewP@ss123',
            'new_password_confirmation' => 'NewP@ss123',
        ], authHeaders($token))
            ->assertStatus(200)
            ->assertJson(['message' => 'Password changed.']);

        $response = postJson('/api/login', [
            'email' => 'admin@inventory.api',
            'password' => 'NewP@ss123',
        ]);
        $response->assertStatus(200);
    });

    it('cannot change password with wrong current password', function () {
        [, $token] = loginAsAdmin();

        postJson('/api/change-password', [
            'current_password' => 'wrong-current',
            'new_password' => 'NewP@ss123',
            'new_password_confirmation' => 'NewP@ss123',
        ], authHeaders($token))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    });

    it('cannot change password with mismatched confirmation', function () {
        [, $token] = loginAsAdmin();

        postJson('/api/change-password', [
            'current_password' => 'admin',
            'new_password' => 'NewP@ss123',
            'new_password_confirmation' => 'different',
        ], authHeaders($token))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    });

    it('cannot change password without authentication', function () {
        postJson('/api/change-password', [
            'current_password' => 'admin',
            'new_password' => 'NewP@ss123',
            'new_password_confirmation' => 'NewP@ss123',
        ])->assertStatus(401);
    });

    it('can logout', function () {
        [, $token] = loginAsAdmin();

        postJson('/api/logout', [], authHeaders($token))
            ->assertStatus(200)
            ->assertJson(['message' => 'Logout.']);
    });

    it('cannot logout without authentication', function () {
        postJson('/api/logout')->assertStatus(401);
    });
});
