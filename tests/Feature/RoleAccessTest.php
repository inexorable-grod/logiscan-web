<?php

use App\Models\CenterUser;
use App\Models\OperationCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helper: create an operario with an active center assignment
|--------------------------------------------------------------------------
*/

function createOperarioWithCenter(): User
{
    $admin = User::factory()->role('ti_admin')->create();
    $center = OperationCenter::create([
        'name'       => 'Centro Test',
        'code'       => 'CT001',
        'is_active'  => true,
        'created_by' => $admin->id,
    ]);

    $operario = User::factory()->role('operario')->create();

    CenterUser::create([
        'center_id'   => $center->id,
        'user_id'     => $operario->id,
        'is_active'   => true,
        'assigned_by' => $admin->id,
    ]);

    return $operario;
}

/*
|--------------------------------------------------------------------------
| Operario access
|--------------------------------------------------------------------------
*/

test('operario can access /api/routes', function () {
    $operario = createOperarioWithCenter();
    Sanctum::actingAs($operario);

    $this->getJson('/api/routes')->assertOk();
});

test('operario can access /api/clients', function () {
    $operario = createOperarioWithCenter();
    Sanctum::actingAs($operario);

    $center = $operario->centerAssignment->center;
    $route = \App\Models\Route::create([
        'center_id'    => $center->id,
        'route_number' => 'R999',
        'is_active'    => true,
    ]);

    $this->getJson('/api/clients?route_id=' . $route->id)->assertOk();
});

test('operario can access /api/requests/mine', function () {
    $operario = createOperarioWithCenter();
    Sanctum::actingAs($operario);

    $this->getJson('/api/requests/mine')->assertOk();
});

test('operario cannot access admin routes', function () {
    $operario = createOperarioWithCenter();
    Sanctum::actingAs($operario);

    $this->getJson('/api/admin/users')->assertForbidden();
    $this->getJson('/api/admin/centers')->assertForbidden();
    $this->getJson('/api/admin/routes')->assertForbidden();
});

test('operario cannot access supervisor routes', function () {
    $operario = createOperarioWithCenter();
    Sanctum::actingAs($operario);

    $this->getJson('/api/supervisor/centers')->assertForbidden();
    $this->getJson('/api/supervisor/dashboard')->assertForbidden();
});

test('operario cannot access shared routes', function () {
    $operario = createOperarioWithCenter();
    Sanctum::actingAs($operario);

    $this->getJson('/api/shared/requests')->assertForbidden();
    $this->getJson('/api/shared/clients')->assertForbidden();
});

test('operario without center assignment is denied', function () {
    $operario = User::factory()->role('operario')->create();
    Sanctum::actingAs($operario);

    $this->getJson('/api/routes')->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Supervisor access
|--------------------------------------------------------------------------
*/

test('supervisor can access supervisor routes', function () {
    $supervisor = User::factory()->role('supervisor')->create();
    Sanctum::actingAs($supervisor);

    $this->getJson('/api/supervisor/centers')->assertOk();
});

test('supervisor cannot access admin routes', function () {
    $supervisor = User::factory()->role('supervisor')->create();
    Sanctum::actingAs($supervisor);

    $this->getJson('/api/admin/users')->assertForbidden();
    $this->getJson('/api/admin/centers')->assertForbidden();
    $this->postJson('/api/admin/users', [])->assertForbidden();
});

test('supervisor cannot access operario routes', function () {
    $supervisor = User::factory()->role('supervisor')->create();
    Sanctum::actingAs($supervisor);

    $this->getJson('/api/routes')->assertForbidden();
    $this->postJson('/api/scans', [])->assertForbidden();
    $this->getJson('/api/requests/mine')->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| TI Admin access
|--------------------------------------------------------------------------
*/

test('ti_admin can access admin routes', function () {
    $admin = User::factory()->role('ti_admin')->create();
    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/users')->assertOk();
    $this->getJson('/api/admin/centers')->assertOk();
    $this->getJson('/api/admin/routes')->assertOk();
});

test('ti_admin cannot access operario routes', function () {
    $admin = User::factory()->role('ti_admin')->create();
    Sanctum::actingAs($admin);

    $this->getJson('/api/routes')->assertForbidden();
    $this->postJson('/api/scans', [])->assertForbidden();
    $this->getJson('/api/requests/mine')->assertForbidden();
});

test('ti_admin cannot access supervisor-only routes', function () {
    $admin = User::factory()->role('ti_admin')->create();
    Sanctum::actingAs($admin);

    $this->getJson('/api/supervisor/dashboard')->assertForbidden();
    $this->postJson('/api/supervisor/select-center', [])->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Gerente Ops access
|--------------------------------------------------------------------------
*/

test('gerente_ops cannot access admin routes', function () {
    $gerente = User::factory()->role('gerente_ops')->create();
    Sanctum::actingAs($gerente);

    $this->getJson('/api/admin/users')->assertForbidden();
    $this->getJson('/api/admin/centers')->assertForbidden();
});

test('gerente_ops cannot access operario routes', function () {
    $gerente = User::factory()->role('gerente_ops')->create();
    Sanctum::actingAs($gerente);

    $this->getJson('/api/routes')->assertForbidden();
    $this->postJson('/api/scans', [])->assertForbidden();
});

test('gerente_ops cannot access supervisor-only routes', function () {
    $gerente = User::factory()->role('gerente_ops')->create();
    Sanctum::actingAs($gerente);

    $this->getJson('/api/supervisor/centers')->assertForbidden();
    $this->getJson('/api/supervisor/dashboard')->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Unauthenticated access
|--------------------------------------------------------------------------
*/

test('unauthenticated users cannot access protected routes', function () {
    $this->getJson('/api/routes')->assertUnauthorized();
    $this->getJson('/api/admin/users')->assertUnauthorized();
    $this->getJson('/api/supervisor/dashboard')->assertUnauthorized();
    $this->postJson('/api/scans', [])->assertUnauthorized();
});

/*
|--------------------------------------------------------------------------
| Force password change middleware
|--------------------------------------------------------------------------
*/

test('operario with force_password_change is blocked from operario routes', function () {
    $admin = User::factory()->role('ti_admin')->create();
    $center = OperationCenter::create([
        'name'       => 'Centro Test',
        'code'       => 'CT002',
        'is_active'  => true,
        'created_by' => $admin->id,
    ]);

    $operario = User::factory()->role('operario')->forcePasswordChange()->create();

    CenterUser::create([
        'center_id'   => $center->id,
        'user_id'     => $operario->id,
        'is_active'   => true,
        'assigned_by' => $admin->id,
    ]);

    Sanctum::actingAs($operario);

    $this->getJson('/api/routes')
        ->assertForbidden()
        ->assertJsonPath('action', 'force_password_change');
});
