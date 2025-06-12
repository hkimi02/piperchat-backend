<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use App\Models\JoinCode;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Mail\SymfonyMailerService;
use App\Services\Organisation\OrganisationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrganisationController extends Controller
{
    protected OrganisationService $organisationService;
    public function __construct(OrganisationService $organisationService)
    {
        $this->organisationService = $organisationService;
    }

    public function getUsers(Request $request)
    {
        $users = $this->organisationService->getUsers($request->user());
        return response()->json($users);
    }

    public function inviteMember(Request $request, SymfonyMailerService $mailer)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = Auth::user();
        $organisation = Organisation::find($user->organisation_id);

        // Generate join code
        $joinCode = JoinCode::create([
            'code' => Str::random(8),
            'organisation_id' => $organisation->id,
            'expires_at' => now()->addDays(7),
        ]);

        // Send email
        $mailer->sendInvitationEmail($request->email, $organisation, $joinCode->code);

        return response()->json(['message' => 'Invitation sent successfully']);
    }

    public function generateInviteCode(Request $request)
    {
        $user = $request->user();
        $joinCode = $this->organisationService->generateJoinCode($user->organisation_id);

        return response()->json(['code' => $joinCode->code]);
    }

    public function removeUser(Request $request, User $userToRemove)
    {
        $organisation = $request->user()->organisation;

        if (!$organisation) {
            return response()->json(['message' => 'User is not in an organisation.'], 404);
        }

        $success = $this->organisationService->removeUser($organisation, $userToRemove);

        if ($success) {
            return response()->json(['message' => 'User removed successfully.']);
        } else {
            return response()->json(['message' => 'Failed to remove user. They may not be in the organisation or they are the admin.'], 400);
        }
    }
}
