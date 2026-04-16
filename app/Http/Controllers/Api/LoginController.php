<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate the user with the provided credentials.
        if (!Auth::attempt($request->only('email', 'password'))) {
            // If authentication fails, throw a validation exception.
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // If authentication is successful, create a new API token for the user.
        $token = $request->user()->createToken('api-token');

        // Return the plain-text token as a JSON response.
        // The frontend will need to save this token and send it with every subsequent API request.
        return response()->json([
            'token' => $token->plainTextToken,
        ]);
    }
}
