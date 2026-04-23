<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'                  => 'User ' . Str::random(5),
            'email'                 => Str::random(8) . '@logiscan.local',
            'password'              => bcrypt('password'),
            'role'                  => 'operario',
            'is_active'             => true,
            'force_password_change' => false,
        ];
    }

    public function role(string $role): static
    {
        return $this->state(fn () => ['role' => $role]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function forcePasswordChange(): static
    {
        return $this->state(fn () => ['force_password_change' => true]);
    }
}
