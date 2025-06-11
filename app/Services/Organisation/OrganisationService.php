<?php

namespace App\Services\Organisation;

use App\Models\JoinCode;
use App\Models\Organisation;
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
}
