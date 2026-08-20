<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/products',
        summary: 'Obtener todos los productos',
        tags: ['Products'],
        security: [
            ['bearerAuth' => []],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Respuesta con productos'
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado'
            ),
        ]
    )]
    public function index()
    {
        return response()->json(Product::get());
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
                response: 201,
                description: 'Producto almacenado exitosamente'
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
            'brand' => 'required|string',
            'price' => 'required|min:1|max:999',
        ]);

        $newProduct = Product::create([
            'product_code' => sprintf('%s-%s', now()->format('H:i'), Str::limit($data['name'], 2)),
            'name' => $data['name'],
            'brand' => $data['brand'],
            'price' => $data['price'],
        ]);

        return response()->json($newProduct);
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
                description: 'Producto encontrado'
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
                description: 'Usuario actualizado'
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
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'brand' => 'required|string',
            'price' => 'required|min:1|max:999',
        ]);

        $product->update([
            'name' => $data['name'],
            'brand' => $data['brand'],
            'price' => $data['price'],
        ]);

        return response()->json($newProduct);
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
        $msg = sprintf('Producto: %s eliminado', $product->name);

        $product->delete();

        return response()->json($msg);
    }
}
