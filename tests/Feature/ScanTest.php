<?php

use App\Models\CenterUser;
use App\Models\OperationCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $admin = User::factory()->role('ti_admin')->create();
    $center = OperationCenter::create([
        'name'       => 'Centro Scan',
        'code'       => 'CS001',
        'is_active'  => true,
        'created_by' => $admin->id,
    ]);

    $this->operario = User::factory()->role('operario')->create();

    CenterUser::create([
        'center_id'   => $center->id,
        'user_id'     => $this->operario->id,
        'is_active'   => true,
        'assigned_by' => $admin->id,
    ]);

    Sanctum::actingAs($this->operario);
});

/*
|--------------------------------------------------------------------------
| Single Scan — POST /api/scans
|--------------------------------------------------------------------------
*/

test('scan with 5-digit barcode returns rf type', function () {
    $response = $this->postJson('/api/scans', ['barcode' => '12345']);

    $response->assertOk()
        ->assertJsonPath('valid', true)
        ->assertJsonPath('type', 'rf')
        ->assertJsonPath('error', null);
});

test('scan with 10-digit barcode returns toggle type', function () {
    $response = $this->postJson('/api/scans', ['barcode' => '1234567890']);

    $response->assertOk()
        ->assertJsonPath('valid', true)
        ->assertJsonPath('type', 'toggle')
        ->assertJsonPath('error', null);
});

test('scan with 11-digit barcode returns modal type', function () {
    $response = $this->postJson('/api/scans', ['barcode' => '12345678901']);

    $response->assertOk()
        ->assertJsonPath('valid', true)
        ->assertJsonPath('type', 'modal')
        ->assertJsonPath('error', null);
});

test('scan with invalid length barcode returns 422', function () {
    $response = $this->postJson('/api/scans', ['barcode' => '123']);

    $response->assertStatus(422)
        ->assertJsonPath('valid', false)
        ->assertJsonPath('type', null);
});

test('scan with non-numeric barcode returns 422', function () {
    $response = $this->postJson('/api/scans', ['barcode' => 'ABCDE']);

    $response->assertStatus(422)
        ->assertJsonPath('valid', false)
        ->assertJsonPath('type', null);
});

test('scan requires barcode field', function () {
    $response = $this->postJson('/api/scans', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('barcode');
});

test('scan with 6-digit barcode is rejected', function () {
    $response = $this->postJson('/api/scans', ['barcode' => '123456']);

    $response->assertStatus(422)
        ->assertJsonPath('valid', false);
});

test('scan with 9-digit barcode is rejected', function () {
    $response = $this->postJson('/api/scans', ['barcode' => '123456789']);

    $response->assertStatus(422)
        ->assertJsonPath('valid', false);
});

test('scan with 12-digit barcode is rejected', function () {
    $response = $this->postJson('/api/scans', ['barcode' => '123456789012']);

    $response->assertStatus(422)
        ->assertJsonPath('valid', false);
});

/*
|--------------------------------------------------------------------------
| Batch Scan — POST /api/scans/batch
|--------------------------------------------------------------------------
*/

test('batch sync processes multiple scans', function () {
    $response = $this->postJson('/api/scans/batch', [
        'scans' => [
            [
                'localId'   => 'local-1',
                'barcode'   => '12345',
                'scanType'  => 'rf',
                'scannedAt' => '2026-04-01T10:00:00Z',
            ],
            [
                'localId'   => 'local-2',
                'barcode'   => '1234567890',
                'scanType'  => 'toggle',
                'scannedAt' => '2026-04-01T10:01:00Z',
            ],
        ],
    ]);

    $response->assertOk()
        ->assertJsonCount(2, 'results')
        ->assertJsonPath('results.0.localId', 'local-1')
        ->assertJsonPath('results.0.status', 'synced')
        ->assertJsonPath('results.1.localId', 'local-2')
        ->assertJsonPath('results.1.status', 'synced');
});

test('batch sync marks invalid barcodes as failed', function () {
    $response = $this->postJson('/api/scans/batch', [
        'scans' => [
            [
                'localId'   => 'local-ok',
                'barcode'   => '12345',
                'scanType'  => 'rf',
                'scannedAt' => '2026-04-01T10:00:00Z',
            ],
            [
                'localId'   => 'local-bad',
                'barcode'   => 'INVALID',
                'scanType'  => 'unknown',
                'scannedAt' => '2026-04-01T10:00:00Z',
            ],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('results.0.status', 'synced')
        ->assertJsonPath('results.1.status', 'failed')
        ->assertJsonPath('results.1.error', 'El código de barras debe contener solo caracteres numéricos.');
});

test('batch sync requires scans array', function () {
    $response = $this->postJson('/api/scans/batch', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('scans');
});

test('batch sync validates each scan structure', function () {
    $response = $this->postJson('/api/scans/batch', [
        'scans' => [
            ['localId' => 'local-1'],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['scans.0.barcode', 'scans.0.scanType', 'scans.0.scannedAt']);
});

test('batch sync enforces max 50 scans', function () {
    $scans = collect(range(1, 51))->map(fn ($i) => [
        'localId'   => "local-{$i}",
        'barcode'   => '12345',
        'scanType'  => 'rf',
        'scannedAt' => '2026-04-01T10:00:00Z',
    ])->toArray();

    $response = $this->postJson('/api/scans/batch', ['scans' => $scans]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('scans');
});
