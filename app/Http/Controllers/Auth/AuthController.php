<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\FCMToken;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @throws \Exception
     */
    public function register(RegisterRequest $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validated();
        try {
            $user = $this->authService->register($validatedData);
            return response()->json([
                'message' => 'User registered successfully',
                'data' => $user,
                'statusCode' => 201,
                'status' => 'ok'
            ], 201);
        } catch (\Exception $exception) {
            $statusCode = $exception->getCode() ?: 500;
            return response()->json([
                'message' => $exception->getMessage(),
                'statusCode' => $statusCode,
                'status' => 'error'
            ], $statusCode);
        }
    }

    public function verifyEmail(Request $request): \Illuminate\Http\JsonResponse{
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|integer'
        ]);
        try{
            $this->authService->verifyEmail($request->email, $request->verification_code);
            return response()->json([
                'message' => 'Email verified successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        }catch (\Exception $e){
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
        try{
            $this->authService->resendVerificationCode($request->email);
            return response()->json([
                'message' => 'Verification code resent successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        }catch (\Exception $e){
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
        try{
            $this->authService->forgetPassword($request->email);
            return response()->json([
                'message' => 'Password reset link sent successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        }catch (\Exception $e){
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
            'password' => ['required','string','min:8','confirmed','regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^])[A-Za-z\d@$!%*?&#^]{8,}$/'],
            'pin' => 'required|digits:4',
        ]);
        try{
            $this->authService->resetPassword($request->email, $request->password, $request->pin);
            return response()->json([
                'message' => 'Password reset successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        }catch (\Exception $e){
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
        try{
            $this->authService->verifyResetPin($request->email, $request->pin);
            return response()->json([
                'message' => 'Verification pin is valid',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        }catch (\Exception $e){
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
        try{
            $this->authService->resendForgetPassword($request->email);
            return response()->json([
                'message' => 'Verification code resent successfully',
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        }catch (\Exception $e){
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

    public function loginAdmin(Request $request): \Illuminate\Http\JsonResponse{
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        try{
            $credentials = $request->only('email', 'password');
            return $this->authService->loginAdmin($credentials);
        }catch (\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }

    public function me(): \Illuminate\Http\JsonResponse
    {
        try{
            $user = Auth::user();
            return response()->json([
                'message' => 'User retrieved successfully',
                'data' => $user,
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        }catch (\Exception $e){
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
            $user=Auth::user();
            if($request->has('fcm_token') && $user->role==UserRole::USER->value) {
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
