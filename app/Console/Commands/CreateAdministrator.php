<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdministrator extends Command
{
    protected $signature = 'app:create-administrator {--force : Confirm creation outside local or testing environments}';

    protected $description = 'Create a system administrator interactively without a default password';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing']) && ! $this->option('force')) {
            $this->error('For non-local environments, rerun with --force after reviewing the target environment.');

            return self::FAILURE;
        }

        $name = (string) $this->ask('Administrator name');
        $email = Str::lower(trim((string) $this->ask('Administrator email')));
        $password = (string) $this->secret('Password (minimum 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', Password::min(12)],
            ],
        );

        if ($validator->fails() || ! hash_equals($password, $confirmation)) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            if (! hash_equals($password, $confirmation)) {
                $this->error('The password confirmation did not match.');
            }

            return self::FAILURE;
        }

        $role = Role::query()->where('slug', Roles::SYSTEM_ADMINISTRATOR)->first();
        if ($role === null) {
            $this->error('Role catalogue is missing. Run php artisan db:seed first.');

            return self::FAILURE;
        }

        $user = DB::transaction(function () use ($name, $email, $password, $role): User {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => true,
            ]);
            $user->roles()->sync([$role->id]);

            return $user;
        });

        $this->info("Administrator account created for {$user->email}.");
        $this->line('The password is not displayed or written to application logs.');

        return self::SUCCESS;
    }
}
