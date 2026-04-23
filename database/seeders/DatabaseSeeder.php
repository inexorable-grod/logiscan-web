<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\CenterUser;
use App\Models\OperationCenter;
use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Create users (one per role)
        $tiAdmin = User::factory()->create([
            'name'                  => 'Admin TI',
            'email'                 => 'admin@logiscan.local',
            'role'                  => 'ti_admin',
            'force_password_change' => false,
        ]);

        $supervisor = User::factory()->create([
            'name'                  => 'Supervisor General',
            'email'                 => 'supervisor@logiscan.local',
            'role'                  => 'supervisor',
            'force_password_change' => false,
        ]);

        $gerente = User::factory()->create([
            'name'                  => 'Gerente de Operaciones',
            'email'                 => 'gerente@logiscan.local',
            'role'                  => 'gerente_ops',
            'force_password_change' => false,
        ]);

        $operario1 = User::factory()->create([
            'name'                  => 'Juan Operario',
            'email'                 => 'operario1@logiscan.local',
            'role'                  => 'operario',
            'force_password_change' => false,
        ]);

        $operario2 = User::factory()->create([
            'name'                  => 'María Operaria',
            'email'                 => 'operario2@logiscan.local',
            'role'                  => 'operario',
            'force_password_change' => false,
        ]);

        // 2. Create operation centers
        $centerBogota = OperationCenter::create([
            'name'       => 'Centro Bogotá Norte',
            'code'       => 'BOG-N',
            'address'    => 'Calle 100 #15-20, Bogotá',
            'created_by' => $tiAdmin->id,
        ]);

        $centerMedellin = OperationCenter::create([
            'name'       => 'Centro Medellín',
            'code'       => 'MDE-1',
            'address'    => 'Carrera 43A #1-50, Medellín',
            'created_by' => $tiAdmin->id,
        ]);

        // 3. Assign operarios to centers
        CenterUser::create([
            'center_id'   => $centerBogota->id,
            'user_id'     => $operario1->id,
            'is_active'   => true,
            'assigned_by' => $tiAdmin->id,
        ]);

        CenterUser::create([
            'center_id'   => $centerMedellin->id,
            'user_id'     => $operario2->id,
            'is_active'   => true,
            'assigned_by' => $tiAdmin->id,
        ]);

        // 4. Create routes per center
        $routesBogota = [];
        foreach (['R-001' => 'Zona Norte', 'R-002' => 'Zona Chapinero'] as $number => $desc) {
            $routesBogota[] = Route::create([
                'center_id'    => $centerBogota->id,
                'route_number' => $number,
                'description'  => $desc,
                'created_by'   => $tiAdmin->id,
            ]);
        }

        $routesMedellin = [];
        foreach (['R-010' => 'El Poblado', 'R-011' => 'Laureles'] as $number => $desc) {
            $routesMedellin[] = Route::create([
                'center_id'    => $centerMedellin->id,
                'route_number' => $number,
                'description'  => $desc,
                'created_by'   => $tiAdmin->id,
            ]);
        }

        // 5. Create clients per route
        $clientNames = [
            'Almacén El Éxito', 'Droguería Colsubsidio', 'Restaurante El Corral',
            'Tienda D1', 'Supermercado Ara', 'Panadería La Especial',
            'Ferretería Central', 'Papelería Nacional', 'Carnicería Don José',
            'Farmacia Pasteur',
        ];

        $clientIndex = 0;
        foreach (array_merge($routesBogota, $routesMedellin) as $route) {
            for ($i = 0; $i < 3; $i++) {
                $name = $clientNames[$clientIndex % count($clientNames)];
                Client::create([
                    'route_id'    => $route->id,
                    'client_code' => 'CLI-' . str_pad($clientIndex + 1, 4, '0', STR_PAD_LEFT),
                    'name'        => $name,
                    'address'     => 'Calle ' . ($clientIndex + 1) . ' #' . rand(1, 99) . '-' . rand(1, 99),
                    'phone'       => '3' . str_pad((string) rand(0, 999999999), 9, '0', STR_PAD_LEFT),
                    'created_by'  => $tiAdmin->id,
                ]);
                $clientIndex++;
            }
        }

        $this->command->info('Seeded: 5 users, 2 centers, 4 routes, 12 clients.');
        $this->command->info('Login: admin@logiscan.local / password');
    }
}
