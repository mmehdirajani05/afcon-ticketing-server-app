<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name'        => 'Super Admin',
                'email'       => 'admin@afcon.com',
                'password'    => 'Admin@1234',
                'global_role' => 'admin',
                'is_active'   => true,
            ],
        ];

        foreach ($admins as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            $this->command->info("✓ Admin user ready: {$user->email}");
        }
    }
}
