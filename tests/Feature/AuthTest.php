<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

test('login succeeds with valid credentials', function () {
    $user = User::factory()->create([
        'email'    => 'operario@test.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'operario@test.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user'])
        ->assertJsonPath('user.email', 'operario@test.com')
        ->assertJsonPath('user.role', 'operario');
});

test('login fails with wrong password', function () {
    User::factory()->create([
        'email'    => 'user@test.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'user@test.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('login fails with non-existent email', function () {
    $response = $this->postJson('/api/login', [
        'email'    => 'nobody@test.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('login fails for inactive user', function () {
    User::factory()->inactive()->create([
        'email'    => 'inactive@test.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'inactive@test.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('login returns centers list for supervisor', function () {
    User::factory()->role('supervisor')->create([
        'email'    => 'supervisor@test.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'supervisor@test.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user', 'centers']);
});

test('login returns force_password_change flag', function () {
    User::factory()->forcePasswordChange()->create([
        'email'    => 'forced@test.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'forced@test.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('user.force_password_change', true);
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

test('logout revokes the current token', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/logout');

    $response->assertOk()
        ->assertJsonPath('message', 'Sesión cerrada.');
});

test('logout requires authentication', function () {
    $response = $this->postJson('/api/logout');

    $response->assertUnauthorized();
});

/*
|--------------------------------------------------------------------------
| Password Change
|--------------------------------------------------------------------------
*/

test('authenticated user can change password', function () {
    $user = User::factory()->create(['password' => 'password']);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/password/change', [
        'current_password'      => 'password',
        'new_password'          => 'new-secure-password',
        'new_password_confirmation' => 'new-secure-password',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Contraseña actualizada.');
});

test('password change fails with wrong current password', function () {
    $user = User::factory()->create(['password' => 'password']);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/password/change', [
        'current_password'      => 'wrong-password',
        'new_password'          => 'new-secure-password',
        'new_password_confirmation' => 'new-secure-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('current_password');
});

test('password change clears force_password_change flag', function () {
    $user = User::factory()->forcePasswordChange()->create(['password' => 'password']);
    Sanctum::actingAs($user);

    $this->postJson('/api/password/change', [
        'current_password'      => 'password',
        'new_password'          => 'new-secure-password',
        'new_password_confirmation' => 'new-secure-password',
    ])->assertOk();

    expect($user->fresh()->force_password_change)->toBeFalse();
});

test('password change requires authentication', function () {
    $response = $this->postJson('/api/password/change', [
        'current_password'      => 'password',
        'new_password'          => 'new-password',
        'new_password_confirmation' => 'new-password',
    ]);

    $response->assertUnauthorized();
});

test('password change validates minimum length', function () {
    $user = User::factory()->create(['password' => 'password']);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/password/change', [
        'current_password'      => 'password',
        'new_password'          => 'short',
        'new_password_confirmation' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('new_password');
});
