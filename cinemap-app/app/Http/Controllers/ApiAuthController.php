<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Identifiants invalides'], 401);
        }

        $user = Auth::guard('api')->user();

        if (! $user->subscribed('default')) {
            Auth::guard('api')->logout();

            return response()->json(['error' => 'Abonnement requis'], 403);
        }

        return response()->json(['token' => $token]);
    }
}
