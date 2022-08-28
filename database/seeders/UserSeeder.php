<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        /** @var User $admin */
        $admin = User::query()->create([
            'name' => 'admin',
            'email' => 'admin@okapi.to',
            'password' => Hash::make('parola1234'),
            'email_verified_at' => Carbon::now(),
        ]);
        $admin->assignRole(Role::ADMIN_ROLE);
    }
}
