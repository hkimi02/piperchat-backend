<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\FCMToken;
use App\Models\User;
use App\Services\Mail\SymfonyMailerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    protected SymfonyMailerService $symfonyMailerService;
    public function __construct(SymfonyMailerService $symfonyMailerService)
    {
        $this->symfonyMailerService = $symfonyMailerService;
    }

    /**
     * @throws \Exception
     */
    public function register($validated)
    {
        try {
            $user = User::where('email', $validated['email'])->first();
            if ($user && !$user->email_verified_at) {
                $this->symfonyMailerService->SendEmailVerification($user);
                throw new \Exception('user already exists please verify your email', 409);
            }
            if ($user && $user->email_verified_at) {
                throw new \Exception('user already exists please login', 401);
            }
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'] ?? UserRole::USER->value,
                'is_enabled' => true,
                'verification_pin' => rand(1000, 9999),
                'organisation_id' => $validated['organisation_id'] ?? null,
            ]);
            if (!$user) {
                throw new \Exception('there was an error while creating the user');
            }

            $this->symfonyMailerService->SendEmailVerification($user);
            return $user;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Failed to send email verification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @throws \Exception
     */
    public function verifyEmail($email, $verification_code)
    {
        try{
            $user = User::where('email', $email)->where('verification_pin', $verification_code)->first();
            if (!$user) {
                throw new \Exception('Invalid verification code or email');
            }
            if ($user->email_verified_at) {
                throw new \Exception('Email already verified');
            }
            $user->email_verified_at = now();
            $user->save();
            return $user;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function resendVerificationCode($email)
    {
        try{
            $user = User::where('email', $email)->first();
            if (!$user) {
                throw new \Exception('User not found');
            }
            $user->verification_pin = rand(1000, 9999);
            $user->save();
            $this->symfonyMailerService->SendEmailVerification($user);
            return $user;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        } catch (TransportExceptionInterface $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function forgetPassword($email)
    {
        try{
            $user = User::where('email', $email)->first();
            if (!$user) {
                throw new \Exception('User not found');
            }
            $user->verification_pin = rand(1000, 9999);
            $user->save();
            $this->symfonyMailerService->sendEmailResetPassword($user);
            return $user;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        } catch (TransportExceptionInterface $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function resetPassword($email, $password, $pin)
    {
        try{
            $user = User::where('email', $email)->where('verification_pin', $pin)->first();
            if (!$user) {
                throw new \Exception('Invalid verification code or email');
            }
            if(!$user->email_verified_at) {
                $email_verified_at = now();
            }
            $user->password = Hash::make($password);
            $user->save();
            return $user;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function verifyResetPin($email, $pin)
    {
        try{
            $user = User::where('email', $email)->where('verification_pin', $pin)->first();
            if (!$user) {
                throw new \Exception('Invalid verification code or email');
            }
            return $user;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function resendForgetPassword($email)
    {
        try{
            $user = User::where('email', $email)->first();
            if (!$user) {
                throw new \Exception('User not found');
            }
            $user->verification_pin = rand(1000, 9999);
            $user->save();
            $this->symfonyMailerService->sendEmailResetPassword($user);
            return $user;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        } catch (TransportExceptionInterface $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */

    public function login($credentials,$fcm_token=null): \Illuminate\Http\JsonResponse
    {
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid credentials',
                    'statusCode' => 401,
                    'status' => 'error'
                ], 401);
            }

            $user = Auth::user();

            if (!$user->email_verified_at) {
                $pin = mt_rand(1000, 9999);
                $user->verification_pin = $pin;
                $user->save();
                $this->symfonyMailerService->SendEmailVerification($user);
                return response()->json([
                    'message' => 'Email not verified. Please verify your email before logging in.',
                    'statusCode' => 403,
                    'status' => 'error'
                ], 403);
            }

            if (!$user->is_enabled) {
                return response()->json([
                    'message' => 'Account is disabled. Please contact support.',
                    'statusCode' => 422,
                    'status' => 'error'
                ], 422);
            }
            if($user->role===UserRole::USER->value && $fcm_token){
                $this->updateFcmToken($user,$fcm_token);
            }
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);

        } catch (TransportExceptionInterface $e) {
            return response()->json([
                'message' => 'Failed to send email verification: ' . $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred: ' . $e->getMessage(),
                'statusCode' => 500,
                'status' => 'error'
            ], 500);
        }
    }


    public function loginAdmin($credentials): \Illuminate\Http\JsonResponse|string
    {
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new JWTException('Invalid credentials');
            }
            $user = Auth::user();
            if ($user->role !== UserRole::ADMIN->value) {
                throw new JWTException('You are not authorized to access this resource');
            }
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'statusCode' => 200,
                'status' => 'ok'
            ], 200);
        } catch (JWTException $e) {
            throw new \Exception($e->getMessage());
        } catch (TransportExceptionInterface $e) {
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function updateFcmToken($user,$fcmToken)
    {
        FCMToken::where('fcm_token', $fcmToken)->delete();
        $user->fcmTokens()->Create(
            [
                'fcm_token' => $fcmToken,
            ]
        );
    }
}
