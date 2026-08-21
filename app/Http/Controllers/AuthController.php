<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        summary: 'Iniciar sesión',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user', 'password'],
                properties: [
                    new OA\Property(
                        property: 'user',
                        type: 'string',
                        format: 'email',
                        example: 'admin@tap.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'password'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token generado con éxito',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: '1|laravel_sanctum_token_here'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Credenciales inválidas'
            ),
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'user' => 'required|exists:users,user',
            'password' => 'required',
        ]);

        $user = User::where('user', $request->user)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Cerrar sesión',
        tags: ['Authentication'],
        security: [
            ['bearerAuth' => []],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión cerrada',
                content: new OA\JsonContent()
            ),
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }
}
