<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create the admin user first, without an organisation_id
        $admin = \App\Models\User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN
        ]);

        // 2. Create the organisation, using the admin's ID
        $organisation = \App\Models\Organisation::factory()->create([
            'admin_id' => $admin->id,
        ]);

        // 3. Update the admin user with the new organisation_id
        $admin->organisation_id = $organisation->id;
        $admin->save();

        // 4. Create additional users for the same organisation
        \App\Models\User::factory(9)->create([
            'organisation_id' => $organisation->id,
            'role' => UserRole::USER
        ]);

        // 5. Create a default 'General' chatroom for the organisation
        \App\Models\Chatroom::factory()->create([
            'name' => 'General',
            'organisation_id' => $organisation->id,
        ]);
    }
}
