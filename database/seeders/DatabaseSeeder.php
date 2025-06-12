<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Chatroom;
use App\Models\Project;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create the admin user first, without an organisation_id
        $admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN
        ]);

        // 2. Create the organisation, using the admin's ID
        $organisation = Organisation::factory()->create([
            'admin_id' => $admin->id,
        ]);

        // 3. Update the admin user with the new organisation_id
        $admin->organisation_id = $organisation->id;
        $admin->save();

        // 4. Create additional users for the same organisation
        User::factory(9)->create([
            'organisation_id' => $organisation->id,
            'role' => UserRole::USER
        ]);

        // 5. Create a default 'General' chatroom for the organisation
        Chatroom::factory()->create([
            'name' => 'General',
            'organisation_id' => $organisation->id,
            'type' => 'organisation', // Set type as per chatrooms migration
            'project_id' => null, // No project associated with General chatroom
        ]);

        // 6. Create a project for the organisation
        $project = Project::factory()->create([
            'name' => 'Sample Project',
            'description' => 'A sample project for testing.',
            'organisation_id' => $organisation->id,
        ]);

        // 7. Create a project-specific chatroom
        Chatroom::factory()->create([
            'name' => 'Project Discussion',
            'organisation_id' => $organisation->id,
            'type' => 'project', // Set type as per chatrooms migration
            'project_id' => $project->id, // Associate with the created project
        ]);
    }
}
