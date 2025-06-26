<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Generate API token for user
     */
    public function generateToken(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        $id = $request->id;
        
        // Find user by ID
        $user = User::find($id);
        
        // Check if user exists
        if (!$user) {
            return response()->json([
                'error' => 'User not exists',
                'message' => 'User with ID ' . $id . ' does not exist'
            ], 404);
        }
        
        // Generate token
        $token = $user->createToken('api-token')->plainTextToken;
        
        return response()->json([
            'message' => 'Token generated successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Revoke current user's tokens
     */
    public function revokeTokens(Request $request)
    {
        $request->user()->tokens()->delete();
        
        return response()->json([
            'message' => 'All tokens revoked successfully'
        ]);
    }
} 