<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
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
                        new OA\Property(property: 'access_token', type: 'string', example: '1|laravel_sanctum_token_here'),
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

        $permissions = collect();
        //obtener perfiles del usuario
        $profiles = Profile::whereIn('id',$user->profiles)->get();
        if ($profiles->isNotEmpty) {
            //obtenemos los id de la colección y convertimos a array
            $permissions = $profiles->pluck('sections')->toArray();
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'permissions' => $permissions
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
