<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Exports\GeneralExport;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
    #[OA\Get(
        path: '/api/users',
        summary: 'Obtener todos los usuarios',
        tags: ['Users'],
        security: [
            ['bearerAuth' => []],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Respuesta con usuarios',
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
        ]
    )]
    public function index()
    {
        return response()->json(User::get());
    }

    #[OA\Post(
        path: '/api/users',
        summary: 'Almacenar nuevo registro de un usuario',
        tags: ['Users'],
        security: [
            ['bearerAuth' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user', 'password'],
                properties: [
                    new OA\Property(
                        property: 'user',
                        type: 'string',
                        format: 'email',
                        example: 'user@example.com'
                    ),
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Rodrigo Aguilar'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'secret123'
                    ),
                    new OA\Property(
                        property: 'telephone',
                        type: 'string',
                        example: '+52 2233445566'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario almacenado exitosamente',
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado',
                content: new OA\JsonContent()
            ),
        ]
    )]
    public function store(Request $request)
    {
        $data = $request->validate([
            'user' => 'required|unique:users,user',
            'name' => 'required|string',
            'telephone' => 'required|string',
            'password' => 'sometimes|string',
        ]);

        $newUser = User::create([
            'user_code' => sprintf('%s-%s', now()->format('H-i'), now()->format('Y-m')),
            'user' => $data['user'],
            'name' => $data['name'],
            'telephone' => $request->input('telephone'),
            'password' => $request->input('password', Hash::make(sprintf('%s.%s', now()->format('Y'), strtolower($data['user'])))),
        ]);

        return response()->json($newUser);
    }

    #[OA\Get(
        path: '/api/users/{user}',
        summary: 'Obtener un usuario',
        tags: ['Users'],
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'ULID del usuario',
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
                description: 'Usuario encontrado',
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado',
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado',
            ),
        ]
    )]
    public function show(User $user)
    {
        return response()->json($user);
    }

    #[OA\Put(
        path: '/api/users/{user}',
        summary: 'Actualizar registro de un usuario',
        tags: ['Users'],
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'ULID del usuario',
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
                required: ['user', 'password'],
                properties: [
                    new OA\Property(
                        property: 'user',
                        type: 'string',
                        format: 'email',
                        example: 'user@example.com'
                    ),
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Rodrigo Aguilar'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'secret123'
                    ),
                    new OA\Property(
                        property: 'telephone',
                        type: 'string',
                        example: '+52 2233445566'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario actualizado',
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado'
            ),
        ]
    )]
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'user' => ['required',Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string',
            'telephone' => 'required|string',
        ]);

        $user->update([
            'user' => $data['user'],
            'name' => $data['name'],
            'telephone' => $request->input('telephone'),
        ]);

        // TODO: almacenar foto de perfil

        return response()->json($user);
    }

    #[OA\Delete(
        path: '/api/users/{user}',
        summary: 'Eliminar registro de un usuario',
        tags: ['Users'],
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'ULID del usuario',
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
                description: 'Usuario eliminado',
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado'
            ),
        ]
    )]
    public function destroy(User $user)
    {
        $msg = sprintf('Usuario: %s eliminado', $user->name);

        $user->delete();

        return response()->json($msg);
    }

    #[OA\Get(
        path: '/api/users/export',
        summary: 'Exportar todos los usuarios',
        tags: ['Users'],
        security: [
            ['bearerAuth' => []],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Respuesta con usuarios en un xlsx',
                content: new OA\MediaType(
                    mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    schema: new OA\Schema(
                        type: 'string',
                        format: 'binary'
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
        ]
    )]
    public function export()
    {
        $headers = ['Código de usuario', 'Usuario', 'Nombre', 'Fecha de creación'];

        $users = User::get()->map(function($user){
            return [
                'user_code' => $user->user_code,
                'user' => $user->user,
                'name' => $user->name,
                'created_at' => Carbon::parse($user->created_at)->format('d/m/Y H:i'),
            ];
        })->toArray();

        return Excel::download(new GeneralExport(headers: $headers, items: $users), sprintf('users-%s.xlsx',now()->format('H:i')));
    }

    #[OA\Get(
        path: '/api/users/pdf',
        summary: 'Exportar o visualizar usuarios en formato PDF',
        tags: ['Users'],
        security: [
            ['bearerAuth' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Documento PDF generado exitosamente',
                headers: [
                    new OA\Header(
                        header: 'Content-Disposition',
                        description: 'Indica si se visualiza o se descarga el archivo',
                        schema: new OA\Schema(type: 'string', example: 'inline; filename="users-14:30.pdf"')
                    )
                ],
                content: new OA\MediaType(
                    mediaType: 'application/pdf',
                    schema: new OA\Schema(
                        type: 'string',
                        format: 'binary'
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            )
        ]
    )]
    public function pdf()
    {
        $data = [
            'title' => 'Usarios',
            'headers' => ['Código de usuario', 'Usuario', 'Nombre', 'Fecha de creación'],
            'items' => []
        ];
        $data['width'] = 100 / count($data['headers']);

        $data['items'] = User::get()->map(function($user){
            return [
                'user_code' => $user->user_code,
                'user' => $user->user,
                'name' => $user->name,
                'created_at' => Carbon::parse($user->created_at)->format('d/m/Y H:i'),
            ];
        })->toArray();

        $pdf = Pdf::loadView('pdf', compact('data'));

        return $pdf->stream(sprintf('users-%s.pdf',now()->format('H:i')));
    }
}
