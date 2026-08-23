<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Exports\GeneralExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfileController extends Controller
{
    #[OA\Get(
        path: '/api/profiles',
        summary: 'Obtener todos los perfiles',
        tags: ['Profiles'],
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
                    example: 'administrador'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Respuesta con pefiles',
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
        $profiles = Profile::query();

        if($request->query('search')) {
            $profiles->where('profile_code','like', '%'.$request->query('search').'%')
                ->orWhere('name','like', '%'.$request->query('search').'%');
        }

        return response()->json($profiles->get());
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
                        example: 'Administrador'
                    ),
                    new OA\Property(
                        property: 'sections',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['Users','Products','Profiles']
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Perfil almacenado exitosamente',
                content: new OA\JsonContent()
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
            'sections' => 'nullable|array',
        ]);

        $newProfile = Profile::create([
            'profile_code' => bin2hex(random_bytes(5)),
            'name' => $data['name'],
            'sections' => $data['sections'],
        ]);

        return response()->json('Usuario almacenado exitosamente');
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
                description: 'Perfil encontrado',
                content: new OA\JsonContent()
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
                        example: 'Administrador'
                    ),
                    new OA\Property(
                        property: 'sections',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['Users','Products','Profiles']
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Perfil actualizado',
                content: new OA\JsonContent()
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
            'sections' => 'nullable|array',
        ]);

        $profile->update([
            'name' => $data['name'],
            'sections' => $data['sections'],
        ]);

        return response()->json('Perfil actualizado');
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
                description: 'Perfil eliminado',
                content: new OA\JsonContent()
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

    #[OA\Get(
        path: '/api/profiles/export',
        summary: 'Exportar todos los perfiles',
        tags: ['Profiles'],
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
        $headers = ['Código del perfil', 'Nombre', 'Fecha de creación'];

        $profiles = Profile::get()->map(function($profile){
            return [
                'profile_code' => $profile->profile_code,
                'name' => $profile->name,
                'created_at' => $profile->created_at,
            ];
        })->toArray();

        return Excel::download(new GeneralExport($headers, $profiles), sprintf('perfiles-%s.xlsx',now()->format('H:i')));
    }

    #[OA\Get(
        path: '/api/profiles/pdf',
        summary: 'Exportar o visualizar perfiles en formato PDF',
        tags: ['Profiles'],
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
            'title' => 'Perfiles',
            'headers' => ['Código del perfil', 'Nombre', 'Fecha de creación'],
            'items' => []
        ];
        $data['width'] = 100 / count($data['headers']);

         $data['items'] = Profile::get()->map(function($profile){
            return [
                'profile_code' => $profile->profile_code,
                'name' => $profile->name,
                'created_at' => $profile->created_at,
            ];
        })->toArray();

        $pdf = Pdf::loadView('pdf', compact('data'));

        return $pdf->stream(sprintf('perfiles-%s.pdf',now()->format('H:i')));
    }
}
