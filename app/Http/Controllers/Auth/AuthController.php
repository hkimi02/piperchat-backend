<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Chatroom;
use App\Models\FCMToken;
use App\Services\Auth\AuthService;
use App\Services\Organisation\OrganisationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected OrganisationService $organisationService;

    public function __construct(AuthService $authService, OrganisationService $organisationService)
    {
        $this->authService = $authService;
        $this->organisationService = $organisationService;
    }



public function register(RegisterRequest $request): \Illuminate\Http\JsonResponse
{
    $validatedData = $request->validated();
    try {
        $userData = [
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'role' => UserRole::USER->value,
        ];

        if (isset($validatedData['organisation_name'])) {
            // Set user as admin and create user first
            $userData['role'] = UserRole::ADMIN->value;
            $user = $this->authService->register($userData);

            // Create organisation with user as admin
            $organisation = $this->organisationService->create([
                'name' => $validatedData['organisation_name'],
                'slug' => Str::slug($validatedData['organisation_name']),
                'admin_id' => $user->id,
            ]);
            //create organisation general chat room
            Chatroom::create([
            'name' => 'General',
              'type'=>'organisation',
                'organisation_id' => $organisation->id,
            ]);
            // Update user's organisation_id
            $user->organisation_id = $organisation->id;
            $user->save();

            // Generate initial join code, valid for 7 days
            $joinCode = $this->organisationService->generateJoinCode($organisation->id, 7);
            $responseData = [
                'user' => $user,
                'organisation' => $organisation,
                'join_code' => $joinCode,
            ];
        } elseif (isset($validatedData['join_code'])) {
            // Join existing organisation with join code
            $joinCode = $this->organisationService->validateJoinCode($validatedData['join_code']);
            if (!$joinCode) {
                throw new \Exception('Invalid or expired join code', 400);
            }
            $userData['organisation_id'] = $joinCode->organisation_id;
            $user = $this->authService->register($userData);
            $responseData = [
                'user' => $user,
                'organisation' => $joinCode->organisation,
            ];
        } else {
            throw new \Exception('Either organisation_name or join_code is required', 400);
        }

            return response()->json([
                'message' => 'User registered successfully',
                'data' => $responseData,
                'statusCode' => 201,
                'status' => 'ok'
            ], 201);
        } catch (\Exception $exception) {
            $statusCode = (int) ($exception->getCode() ?: 500);
            return response()->json([
                'message' => $exception->getMessage(),
                'statusCode' => $statusCode,
                'status' => 'error'
            ], $statusCode);
        }
    }


    public function verifyEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|integer'
        ]);
        try {
            $this->authService->verifyEmail($request->email, $request->verification_code);
            return response()->json([
                'message' => 'Email verified successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function resendVerificationCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        try {
            $this->authService->resendVerificationCode($request->email);
            return response()->json([
                'message' => 'Verification code resent successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function forgetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        try {
            $this->authService->forgetPassword($request->email);
            return response()->json([
                'message' => 'Password reset link sent successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^])[A-Za-z\d@$!%*?&#^]{8,}$/'],
            'pin' => 'required|digits:4',
        ]);
        try {
            $this->authService->resetPassword($request->email, $request->password, $request->pin);
            return response()->json([
                'message' => 'Password reset successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function verifyResetPin(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'pin' => 'required|digits:4',
        ]);
        try {
            $this->authService->verifyResetPin($request->email, $request->pin);
            return response()->json([
                'message' => 'Verification pin is valid',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function resendForgetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        try {
            $this->authService->resendForgetPassword($request->email);
            return response()->json([
                'message' => 'Verification code resent successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'fcm_token' => 'sometimes|string',
        ]);

        try {
            $credentials = $request->only('email', 'password');
            return $this->authService->login($credentials, $request->input('fcm_token', null));
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function loginAdmin(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        try {
            $credentials = $request->only('email', 'password');
            return $this->authService->loginAdmin($credentials);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function me(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            return response()->json([
                'message' => 'User retrieved successfully',
                'data' => $user,
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function refreshToken(): \Illuminate\Http\JsonResponse
    {
        try {
            $token = JWTAuth::parseToken()->refresh();
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'fcm_token' => 'sometimes|string',
        ]);
        try {
            $user = Auth::user();
            if ($request->has('fcm_token') && $user->role == UserRole::USER->value) {
                FCMToken::where('fcm_token', $request->input('fcm_token'))
                    ->where('user_id', $user->id)
                    ->delete();
            }
            Auth::logout();
            return response()->json([
                'message' => 'User logged out successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }
}
