<?php

namespace App\Services\Organisation;

use App\Models\JoinCode;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Str;

class OrganisationService
{
    public function create(array $data): Organisation
    {
        return Organisation::create($data);
    }

    public function generateJoinCode(int $organisationId, int $daysValid = 7): JoinCode
    {
        return JoinCode::create([
            'code' => Str::random(8),
            'organisation_id' => $organisationId,
            'expires_at' => now()->addDays($daysValid),
        ]);
    }

    public function validateJoinCode(string $code): ?JoinCode
    {
        $joinCode = JoinCode::where('code', $code)
            ->where('expires_at', '>', now())
            ->first();
        return $joinCode;
    }

    public function getUsers(\Illuminate\Contracts\Auth\Authenticatable $user): \Illuminate\Database\Eloquent\Collection
    {
        $organisation = $user->organisation;

        if (!$organisation) {
            return collect();
        }

        return $organisation->users;
    }

    public function removeUser(Organisation $organisation, User $userToRemove): bool
    {
        // Ensure the user to remove belongs to the organisation
        if ($userToRemove->organisation_id !== $organisation->id) {
            return false;
        }

        // Prevent admin from being removed
        if ($organisation->admin_id === $userToRemove->id) {
            return false;
        }

        $userToRemove->organisation_id = null;
        return $userToRemove->save();
    }
}
