<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use App\Services\Organisation\OrganisationService;
use Illuminate\Http\Request;

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
}
