<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Exports\GeneralExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/products',
        summary: 'Obtener todos los productos',
        tags: ['Products'],
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
                description: 'Respuesta con productos',
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
        //obtenemos data filtrada según petición
        $products = $this->getFilteredData($request);
        //retornamos colección filtrada
        return response()->json($products);
    }

    #[OA\Post(
        path: '/api/products',
        summary: 'Almacenar un nuevo producto',
        tags: ['Products'],
        security: [
            ['bearerAuth' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'brand','price'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        format: 'email',
                        example: 'Camioneta RAV4'
                    ),
                    new OA\Property(
                        property: 'brand',
                        type: 'string',
                        format: 'email',
                        example: 'Toyota'
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'integer',
                        example: '550'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Producto almacenado exitosamente',
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
        //validamos la data
        $data = $request->validate([
            'name' => 'required|string',
            'brand' => 'required|string',
            'price' => 'required|min:1|max:999',
        ]);
        //creamos nuevo registro
        $newProduct = Product::create([
            'product_code' => bin2hex(random_bytes(5)),
            'name' => $data['name'],
            'brand' => $data['brand'],
            'price' => $data['price'],
        ]);
        //retornamos mensaje
        return response()->json('Producto almacenado exitosamente');
    }

    #[OA\Get(
        path: '/api/products/{product}',
        summary: 'Obtener un producto',
        tags: ['Products'],
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [
            new OA\Parameter(
                name: 'product',
                description: 'ULID del producto',
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
                description: 'Producto encontrado',
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
            new OA\Response(
                response: 404,
                description: 'Producto no encontrado'
            ),
        ]
    )]
    public function show(Product $product)
    {
        //retornamos modelo
        return response()->json($product);
    }

    #[OA\Put(
        path: '/api/products/{product}',
        summary: 'Actualizar registro de un producto',
        tags: ['Products'],
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [
            new OA\Parameter(
                name: 'product',
                description: 'ULID del prodcto',
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
                required: ['name', 'brand','price'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        format: 'email',
                        example: 'Camioneta RAV4'
                    ),
                    new OA\Property(
                        property: 'brand',
                        type: 'string',
                        format: 'email',
                        example: 'Toyota'
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'integer',
                        example: '550'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Producto actualizado',
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
            new OA\Response(
                response: 404,
                description: 'Producto no encontrado'
            ),
        ]
    )]
    public function update(Request $request, Product $product)
    {
        //validación de la data
        $data = $request->validate([
            'name' => 'required|string',
            'brand' => 'required|string',
            'price' => 'required|min:1|max:999',
        ]);
        //actualización del registro
        $product->update([
            'name' => $data['name'],
            'brand' => $data['brand'],
            'price' => $data['price'],
        ]);
        //retornamos mensaje
        return response()->json('Producto actualizado');
    }

    #[OA\Delete(
        path: '/api/products/{product}',
        summary: 'Eliminar registro de un producto',
        tags: ['Products'],
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [
            new OA\Parameter(
                name: 'product',
                description: 'ULID del producto',
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
                description: 'Producto eliminado'
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
            new OA\Response(
                response: 404,
                description: 'Producto no encontrado'
            ),
        ]
    )]
    public function destroy(Product $product)
    {
        //guardamos mensaje antes de eliminar
        $msg = sprintf('Producto: %s eliminado', $product->name);
        //borrrado lógico del modelo
        $product->delete();

        return response()->json($msg);
    }

    #[OA\Get(
        path: '/api/products/export',
        summary: 'Exportar todos los productos',
        tags: ['Products'],
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
                description: 'Respuesta con productos en un xlsx',
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
    public function export(Request $request)
    {
        //Establecemos encabezados
        $headers = ['Código del producto', 'Nombre del producto', 'Precio', 'Fecha de creación'];
        //Obtenemos data filtrada y mapeamos para poder leerla en el blade
        $products = $this->getFilteredData($request)->map(function($product){
            return [
                'product_code' => $product->product_code,
                'name' => $product->name,
                'price' => $product->price,
                'created_at' => Carbon::parse($product->created_at)->format('d/m/Y H:i'),
            ];
        })->toArray();
        //retornamos excel
        return Excel::download(new GeneralExport($headers, $products), sprintf('productos-%s.xlsx',now()->format('H:i')));
    }

    #[OA\Get(
        path: '/api/products/pdf',
        summary: 'Exportar o visualizar productos en formato PDF',
        tags: ['Products'],
        security: [
            ['bearerAuth' => []]
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
    public function pdf(Request $request)
    {
        //Armamos data para lectura en el blade del pdf
        $data = [
            'title' => 'Productos',
            'headers' => ['Código del producto', 'Nombre del producto', 'Precio', 'Fecha de creación'],
            'items' => []
        ];
        //Ancho de encabezados según encabezados
        $data['width'] = 100 / count($data['headers']);
        //obtenemos data filtrada y mapeamos a array
        $data['items'] = $this->getFilteredData($request)->map(function($product){
            return [
                'product_code' => $product->product_code,
                'name' => $product->name,
                'price' => $product->price,
                'created_at' => Carbon::parse($product->created_at)->format('d/m/Y H:i'),
            ];
        })->toArray();
        //cramos pdf y le pasamos la data
        $pdf = Pdf::loadView('pdf', compact('data'));
        //retornamos pdf
        return $pdf->stream(sprintf('productos-%s.pdf',now()->format('H:i')));
    }

    //Función que retorna una colección filtrandola
    private function getFilteredData($request)
    {
        //Creamos nueva query
        $products = Product::query();
        //Si hay query en la petición filtramos
        if($request->query('search')){
            $products->where('product_code', 'like', $request->query('search'))
                ->orWhere('name', 'like', $request->query('search'))
                ->orWhere('brand', 'like', $request->query('search'));
        }
        //retornamos colección filtrada
        return $products->get();
    }
}
