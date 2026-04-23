<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create
                            {--name=    : Admin full name}
                            {--email=   : Admin email address}
                            {--password=: Admin password}';

    protected $description = 'Create or update a super-admin user';

    public function handle(): int
    {
        $name     = $this->option('name')     ?: $this->ask('Full name', 'Super Admin');
        $email    = $this->option('email')    ?: $this->ask('Email address', 'admin@afcon.com');
        $password = $this->option('password') ?: $this->secret('Password (leave blank to auto-generate)');

        if (empty($password)) {
            $password = $this->generatePassword();
            $this->line("  Generated password: <comment>{$password}</comment>");
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'        => $name,
                'password'    => $password,
                'global_role' => 'admin',
                'is_active'   => true,
            ]
        );

        $action = $user->wasRecentlyCreated ? 'Created' : 'Updated';

        $this->newLine();
        $this->components->twoColumnDetail("<info>{$action} admin</info>", $user->email);
        $this->components->twoColumnDetail('Name', $user->name);
        $this->components->twoColumnDetail('Role', $user->global_role);
        $this->newLine();
        $this->components->info('Login at /admin/login');

        return self::SUCCESS;
    }

    private function generatePassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
