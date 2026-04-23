<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Register or update an Expo push token for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string|max:500',
            'platform' => 'required|in:android,ios',
        ]);

        // Deactivate this token if it belongs to another user
        DeviceToken::where('token', $request->token)
            ->where('user_id', '!=', $request->user()->id)
            ->update(['is_active' => false]);

        DeviceToken::updateOrCreate(
            ['user_id' => $request->user()->id, 'token' => $request->token],
            ['platform' => $request->platform, 'is_active' => true]
        );

        return response()->json(['message' => 'Token registrado.'], 201);
    }
}
