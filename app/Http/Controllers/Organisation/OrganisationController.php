<?php

namespace App\Http\Controllers\Organisation;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\JoinCode;
use App\Models\Organisation;
use App\Services\Mail\SymfonyMailerService;
use App\Services\Organisation\OrganisationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
