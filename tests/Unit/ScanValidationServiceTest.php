<?php

use App\Services\ScanValidationService;

beforeEach(function () {
    $this->service = new ScanValidationService();
});

/*
|--------------------------------------------------------------------------
| Valid barcodes
|--------------------------------------------------------------------------
*/

test('5-digit barcode is valid with type rf', function () {
    $result = $this->service->validate('12345');

    expect($result)
        ->valid->toBeTrue()
        ->type->toBe('rf')
        ->error->toBeNull();
});

test('10-digit barcode is valid with type toggle', function () {
    $result = $this->service->validate('1234567890');

    expect($result)
        ->valid->toBeTrue()
        ->type->toBe('toggle')
        ->error->toBeNull();
});

test('11-digit barcode is valid with type modal', function () {
    $result = $this->service->validate('12345678901');

    expect($result)
        ->valid->toBeTrue()
        ->type->toBe('modal')
        ->error->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Invalid lengths
|--------------------------------------------------------------------------
*/

test('1-digit barcode is invalid', function () {
    $result = $this->service->validate('1');

    expect($result)
        ->valid->toBeFalse()
        ->type->toBeNull()
        ->error->toContain('1 dígitos');
});

test('4-digit barcode is invalid', function () {
    $result = $this->service->validate('1234');

    expect($result)
        ->valid->toBeFalse()
        ->type->toBeNull()
        ->error->toContain('4 dígitos');
});

test('6-digit barcode is invalid', function () {
    $result = $this->service->validate('123456');

    expect($result)
        ->valid->toBeFalse()
        ->type->toBeNull()
        ->error->toContain('6 dígitos');
});

test('9-digit barcode is invalid', function () {
    $result = $this->service->validate('123456789');

    expect($result)
        ->valid->toBeFalse()
        ->type->toBeNull()
        ->error->toContain('9 dígitos');
});

test('12-digit barcode is invalid', function () {
    $result = $this->service->validate('123456789012');

    expect($result)
        ->valid->toBeFalse()
        ->type->toBeNull()
        ->error->toContain('12 dígitos');
});

test('empty string is invalid', function () {
    $result = $this->service->validate('');

    expect($result)->valid->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Non-numeric input
|--------------------------------------------------------------------------
*/

test('alphabetic string is invalid', function () {
    $result = $this->service->validate('ABCDE');

    expect($result)
        ->valid->toBeFalse()
        ->type->toBeNull()
        ->error->toContain('numéricos');
});

test('mixed alphanumeric string is invalid', function () {
    $result = $this->service->validate('123AB');

    expect($result)
        ->valid->toBeFalse()
        ->type->toBeNull()
        ->error->toContain('numéricos');
});

test('string with spaces is invalid', function () {
    $result = $this->service->validate('123 5');

    expect($result)
        ->valid->toBeFalse()
        ->type->toBeNull()
        ->error->toContain('numéricos');
});

test('string with special characters is invalid', function () {
    $result = $this->service->validate('12-45');

    expect($result)
        ->valid->toBeFalse()
        ->type->toBeNull()
        ->error->toContain('numéricos');
});

test('string with decimal point is invalid', function () {
    $result = $this->service->validate('12.45');

    expect($result)
        ->valid->toBeFalse()
        ->type->toBeNull()
        ->error->toContain('numéricos');
});

/*
|--------------------------------------------------------------------------
| Edge cases with valid lengths but non-numeric
|--------------------------------------------------------------------------
*/

test('5-char alphabetic string is non-numeric, not length error', function () {
    $result = $this->service->validate('ABCDE');

    expect($result)
        ->valid->toBeFalse()
        ->error->toContain('numéricos');
});

test('10-char alphanumeric string is invalid', function () {
    $result = $this->service->validate('123456789A');

    expect($result)
        ->valid->toBeFalse()
        ->error->toContain('numéricos');
});

test('11-char alphanumeric string is invalid', function () {
    $result = $this->service->validate('1234567890A');

    expect($result)
        ->valid->toBeFalse()
        ->error->toContain('numéricos');
});

/*
|--------------------------------------------------------------------------
| Boundary values
|--------------------------------------------------------------------------
*/

test('all-zeros 5-digit barcode is valid', function () {
    $result = $this->service->validate('00000');

    expect($result)
        ->valid->toBeTrue()
        ->type->toBe('rf');
});

test('all-nines 10-digit barcode is valid', function () {
    $result = $this->service->validate('9999999999');

    expect($result)
        ->valid->toBeTrue()
        ->type->toBe('toggle');
});

test('all-zeros 11-digit barcode is valid', function () {
    $result = $this->service->validate('00000000000');

    expect($result)
        ->valid->toBeTrue()
        ->type->toBe('modal');
});
