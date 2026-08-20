<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    #[OA\Get(
        path: '/api/profiles',
        summary: 'Obtener todos los perfiles',
        tags: ['Profiles'],
        security: [
            ['bearerAuth' => []],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Respuesta con pefiles'
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
        ]
    )]
    public function index()
    {
        return response()->json(Profile::get());
    }

    #[OA\Post(
        path: '/api/profiles',
        summary: 'Almacenar un nuevo perfil',
        tags: ['Profiles'],
        security: [
            ['bearerAuth' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'sections'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        format: 'email',
                        example: 'user@example.com'
                    ),
                    new OA\Property(
                        property: 'sections',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: '[]'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Perfil almacenado exitosamente'
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
        ]
    )]
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'sections' => 'required|array',
        ]);

        $newProfile = Profile::create([
            'profile_code' => sprintf('%s-%s', now('H:i'), Str::limit($data['name'])),
            'name' => $data['name'],
            'sections' => $data['sections'],
        ]);

        return response()->json($newProfile);
    }

    #[OA\Get(
        path: '/api/profiles/{profile}',
        summary: 'Obtener un perfil',
        tags: ['Profiles'],
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [
            new OA\Parameter(
                name: 'profile',
                description: 'ULID del perfil',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'ulid',
                    example: '550e8400-e29b-41d4-a716-446655440000'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Perfil encontrado'
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
            new OA\Response(
                response: 404,
                description: 'Perfil no encontrado'
            ),
        ]
    )]
    public function show(Profile $profile)
    {
        return response()->json($profile);
    }

    #[OA\Put(
        path: '/api/profiles/{profile}',
        summary: 'Actualizar registro de un perfil',
        tags: ['Profiles'],
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [
            new OA\Parameter(
                name: 'profile',
                description: 'ULID del perfil',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: '01ARZ3NDEKTSV4RRFFQ69G5FAV'
                )
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'sections'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        format: 'email',
                        example: 'user@example.com'
                    ),
                    new OA\Property(
                        property: 'sections',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: '[]'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Perfil actualizado'
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
            new OA\Response(
                response: 404,
                description: 'Perfil no encontrado'
            ),
        ]
    )]
    public function update(Request $request, Profile $profile)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'sections' => 'required|array',
        ]);

        $profile->update([
            'name' => $data['name'],
            'sections' => $data['sections'],
        ]);

        return response()->json($profile);
    }

    #[OA\Delete(
        path: '/api/profiles/{profile}',
        summary: 'Eliminar registro de un perfil',
        tags: ['Profiles'],
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [
            new OA\Parameter(
                name: 'profile',
                description: 'ULID del perfil',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: '01ARZ3NDEKTSV4RRFFQ69G5FAV'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Perfil eliminado'
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
            new OA\Response(
                response: 404,
                description: 'Perfil no encontrado'
            ),
        ]
    )]
    public function destroy(Profile $profile)
    {
        $msg = sprintf('Perfil: %s eliminado');

        $profile->delete();

        return response()->json($msg);
    }
}
