<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    // public function login(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'login' => 'required|string',
    //         'password' => 'required|string',
    //     ]);

    //     // Determine if the login is an email or username
    //     $user = User::where('email', $request->login)
    //     ->orWhere('name', $request->login)
    //     ->first();

    //     $credentials = [
    //         $fieldType => $request->login,
    //         'password' => $request->password,
    //     ];

    //     // Attempt login
    //     if (!Auth::attempt($credentials)) {
    //         return response()->json([
    //             'message' => 'The provided credentials are incorrect.'
    //         ], 401);
    //     }

    //     // Revoke previous tokens if desired
    //     $user->tokens()->delete();

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'message' => 'Login successful.',
    //         'token'   => $token,
    //         'user'    => $user
    //     ]);
    // }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);
    
        $user = User::where('email', $request->login)
                    ->orWhere('name', $request->login)
                    ->first();
    
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }
    
        $user->tokens()->delete();
    
        $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful.',
        'token'   => $token,
        'user'    => $user
    ]);
}


    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }
}
