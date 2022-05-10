<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::query()->create([
            'name' => Role::ADMIN_ROLE,
            'slug' => Str::slug(Role::ADMIN_ROLE),
            'guard_name' => 'web',
        ]);

        Role::query()->create([
            'name' => Role::PUBLIC_ROLE,
            'slug' => Str::slug(Role::PUBLIC_ROLE),
            'guard_name' => 'web',
        ]);
    }
}
