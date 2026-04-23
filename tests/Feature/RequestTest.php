<?php

use App\Models\CenterUser;
use App\Models\ClientRequest;
use App\Models\OperationCenter;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->role('ti_admin')->create();
    $this->center = OperationCenter::create([
        'name'       => 'Centro Requests',
        'code'       => 'CR001',
        'is_active'  => true,
        'created_by' => $this->admin->id,
    ]);

    $this->operario = User::factory()->role('operario')->create();

    CenterUser::create([
        'center_id'   => $this->center->id,
        'user_id'     => $this->operario->id,
        'is_active'   => true,
        'assigned_by' => $this->admin->id,
    ]);

    $this->route = Route::create([
        'center_id'    => $this->center->id,
        'route_number' => 'R001',
        'description'  => 'Ruta de prueba',
        'is_active'    => true,
        'created_by'   => $this->admin->id,
    ]);

    Sanctum::actingAs($this->operario);
});

/*
|--------------------------------------------------------------------------
| Create Request — POST /api/requests
|--------------------------------------------------------------------------
*/

test('operario can create a new_client request', function () {
    $response = $this->postJson('/api/requests', [
        'request_type' => 'new_client',
        'route_id'     => $this->route->id,
        'request_data' => [
            'name'    => 'Nuevo Cliente',
            'address' => 'Calle Falsa 123',
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('request_type', 'new_client')
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('requested_by', $this->operario->id)
        ->assertJsonPath('center_id', $this->center->id);

    $this->assertDatabaseHas('client_requests', [
        'request_type' => 'new_client',
        'requested_by' => $this->operario->id,
        'status'       => 'pending',
    ]);
});

test('operario can create an update_client request', function () {
    $response = $this->postJson('/api/requests', [
        'request_type' => 'update_client',
        'route_id'     => $this->route->id,
        'request_data' => [
            'client_code' => '12345',
            'new_address' => 'Calle Real 456',
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('request_type', 'update_client')
        ->assertJsonPath('status', 'pending');
});

test('operario can create a scan_reset request', function () {
    $response = $this->postJson('/api/requests', [
        'request_type' => 'scan_reset',
        'route_id'     => $this->route->id,
        'request_data' => [
            'reason' => 'Escaneo duplicado por error',
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('request_type', 'scan_reset');
});

test('request creation fails with invalid request_type', function () {
    $response = $this->postJson('/api/requests', [
        'request_type' => 'invalid_type',
        'request_data' => ['foo' => 'bar'],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('request_type');
});

test('request creation fails without request_data', function () {
    $response = $this->postJson('/api/requests', [
        'request_type' => 'new_client',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('request_data');
});

test('request creation accepts null route_id', function () {
    $response = $this->postJson('/api/requests', [
        'request_type' => 'scan_reset',
        'route_id'     => null,
        'request_data' => ['reason' => 'Test'],
    ]);

    $response->assertCreated()
        ->assertJsonPath('route_id', null);
});

/*
|--------------------------------------------------------------------------
| List Own Requests — GET /api/requests/mine
|--------------------------------------------------------------------------
*/

test('operario can list their own requests', function () {
    ClientRequest::create([
        'request_type' => 'new_client',
        'status'       => 'pending',
        'requested_by' => $this->operario->id,
        'center_id'    => $this->center->id,
        'route_id'     => $this->route->id,
        'request_data' => ['name' => 'Cliente A'],
    ]);

    ClientRequest::create([
        'request_type' => 'update_client',
        'status'       => 'approved',
        'requested_by' => $this->operario->id,
        'center_id'    => $this->center->id,
        'request_data' => ['name' => 'Cliente B'],
    ]);

    $response = $this->getJson('/api/requests/mine');

    $response->assertOk()
        ->assertJsonPath('total', 2);
});

test('operario does not see other users requests', function () {
    $otherOperario = User::factory()->role('operario')->create();

    CenterUser::create([
        'center_id'   => $this->center->id,
        'user_id'     => $otherOperario->id,
        'is_active'   => true,
        'assigned_by' => $this->admin->id,
    ]);

    ClientRequest::create([
        'request_type' => 'new_client',
        'status'       => 'pending',
        'requested_by' => $otherOperario->id,
        'center_id'    => $this->center->id,
        'request_data' => ['name' => 'Not mine'],
    ]);

    $response = $this->getJson('/api/requests/mine');

    $response->assertOk()
        ->assertJsonPath('total', 0);
});

test('requests mine returns paginated results', function () {
    $response = $this->getJson('/api/requests/mine');

    $response->assertOk()
        ->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
        ]);
});
