<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProvisionAdmin extends Command
{
    protected $signature = 'shipfast:provision-admin {--stdin : Read setup JSON from standard input instead of interactive prompts}';

    protected $description = 'Create the first hosted administrator without default credentials or resetting an account';

    public function handle(): int
    {
        if (User::where('role', 'super_admin')->exists()) {
            $this->error('A super administrator already exists. Use account management; this command never resets passwords.');

            return self::FAILURE;
        }
        try {
            if ($this->option('stdin')) {
                $data = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
            } else {
                $data = ['email' => $this->ask('Administrator email'), 'first_name' => $this->ask('Administrator first name'), 'last_name' => $this->ask('Administrator last name'), 'password' => $this->secret('Choose a new administrator password (16–72 characters)'), 'password_confirmation' => $this->secret('Confirm the new password')];
            }
            if (! is_array($data)) {
                throw new \RuntimeException('Invalid setup input.');
            }
            $data['email'] = strtolower(trim($data['email'] ?? ''));
            $validator = Validator::make($data, ['email' => ['required', 'email', 'max:255', 'unique:users,email'], 'first_name' => ['required', 'string', 'max:100'], 'last_name' => ['nullable', 'string', 'max:100'], 'password' => ['required', 'string', 'min:16', 'max:72', 'confirmed']]);
            if ($validator->fails()) {
                $this->error('Setup validation failed: '.implode(' ', $validator->errors()->all()));

                return self::FAILURE;
            }
            User::create(['email' => $data['email'], 'first_name' => $data['first_name'], 'last_name' => $data['last_name'] ?? null, 'password' => Hash::make($data['password']), 'role' => 'super_admin', 'status' => 'active', 'approved_at' => now()]);
            $this->info('HOSTED_ADMIN_CREATED');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Administrator setup could not finish. Check input and database access. No password is printed.');

            return self::FAILURE;
        }
    }
}
