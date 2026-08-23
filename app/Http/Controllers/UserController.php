<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Exports\GeneralExport;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class UserController extends Controller
{
    #[OA\Get(
        path: '/api/users',
        summary: 'Obtener todos los usuarios',
        tags: ['Users'],
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'string para realizar búsqueda',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'juanito'
                )
            ),
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
    public function index(Request $request)
    {
        $users = User::query();

        if($request->query('search')){
            $users->where('user','like',"%".$request->query('search')."%");
        }

        return response()->json($users->get());
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
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['foto', 'user', 'name'],
                    properties: [
                        new OA\Property(
                            property: 'foto',
                            description: 'Imagen de perfil del usuario (máx. 2MB)',
                            type: 'string',
                            format: 'binary'
                        ),
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
                            property: 'telephone',
                            type: 'string',
                            nullable: true,
                            example: '+52 2233445566'
                        ),
                        new OA\Property(
                            property: 'password',
                            type: 'string',
                            format: 'password',
                            nullable: true,
                            example: 'secret123'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario almacenado exitosamente',
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación en los campos enviados',
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
            'foto' => 'required|file|max:2048',
            'user' => 'required|unique:users,user',
            'name' => 'required|string',
            'telephone' => 'nullable|phone:INTERNATIONAL',
            'password' => 'sometimes|string',
        ],[
            'user.required'=>'El usuario es obligatorio.',
            'name.required'=>'El nombre es obligatorio.',
            'telephone.phone'=>'El número es invalido.  Ej. +55 11 22..',
        ]);
        //Almacenamos foto
        $image = $request->file('foto')->store('avatar','public');

        $newUser = User::create([
            'user_code' => bin2hex(random_bytes(5)),
            'photo_path' => $image,
            'user' => $data['user'],
            'name' => $data['name'],
            'telephone' => $request->input('telephone'),
            'password' => $request->input('password', Hash::make(sprintf('%s.%s', now()->format('Y'), strtolower($data['user'])))),
        ]);

        $this->processImage($newUser->photo_path);

        return response()->json('Usuario Almacenado exitosamente');
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

    #[OA\Post(
        path: '/api/users/{user}',
        summary: 'Actualizar registro de un usuario (usando POST spoofing para multipart)',
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
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['_method', 'user', 'name'],
                    properties: [
                        new OA\Property(
                            property: '_method',
                            description: 'Simulación de método HTTP para que Laravel procese la petición como PUT',
                            type: 'string',
                            example: 'PUT'
                        ),
                        new OA\Property(
                            property: 'foto',
                            description: 'Nueva foto de perfil (opcional al actualizar, máx. 2MB)',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),
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
                            property: 'telephone',
                            type: 'string',
                            nullable: true,
                            example: '+52 2233445566'
                        ),
                        new OA\Property(
                            property: 'password',
                            type: 'string',
                            format: 'password',
                            nullable: true,
                            example: 'secret123'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario actualizado exitosamente',
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación en los campos',
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
            'foto' => 'nullable|file|max:2048',
            'user' => ['required',Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string',
            'telephone' => 'nullable|phone:INTERNATIONAL',
        ],[
            'user.required'=>'El usuario es obligatorio.',
            'name.required'=>'El nombre es obligatorio.'
        ]);

        $user->update([
            'user' => $data['user'],
            'name' => $data['name'],
            'telephone' => $request->input('telephone'),
        ]);

        if($request->file('foto')){
            $image = $request->file('foto')->store('avatar','public');

            $this->processImage($user->photo_path);
        }

        return response()->json('Usuario actualizado');
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
                'created_at' => $user->created_at,
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
                'created_at' => $user->created_at,
            ];
        })->toArray();

        $pdf = Pdf::loadView('pdf', compact('data'));

        return $pdf->stream(sprintf('users-%s.pdf',now()->format('H:i')));
    }

    #[OA\Get(
        path: '/api/users/{user}/avatar',
        summary: 'Obtener el avatar de un usuario',
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
                description: 'Retorna avatar del usuario en base64',
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
    public function avatar(User $user)
    {
        $mime = Storage::disk('public')->mimeType($user->photo_path);
        $base64 = 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($user->photo_path));
        return response()->json($base64);
    }
    //Función para optimizar imagen
    private function processImage(string $path)
    {
        $image = Image::read(Storage::disk('public')->get($path));
        //Reducimos su tamaño a máximo 150px de ancho
        $image->scaleDown(width: 150);

        Storage::disk('public')->put(
            $path,
            (string) $image->encode()
        );
    }
}
