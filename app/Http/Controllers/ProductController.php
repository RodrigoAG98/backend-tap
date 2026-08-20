<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Product::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string',
            'brand'=>'required|string',
            'price'=>'required|min:1|max:999'
        ]);

        $newProduct = Product::create([
            'product_code'=>sprintf('%s-%s',now()->format('H:i'),Str::limit($data['name'],2)),
            'name'=>$data['name'],
            'brand'=>$data['brand'],
            'price'=>$data['price'],
        ]);

        return response()->json($newProduct);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'=>'required|string',
            'brand'=>'required|string',
            'price'=>'required|min:1|max:999'
        ]);

        $product->update([
            'name'=>$data['name'],
            'brand'=>$data['brand'],
            'price'=>$data['price'],
        ]);

        return response()->json($newProduct);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $msg = sprintf('Producto: %s eliminado',$product->name);

        $product->delete();

        return response()->json($msg);
    }
}
