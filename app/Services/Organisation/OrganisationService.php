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
}
