<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Sino hay productos los generamos, con esto evitamos duplicar registros
        if(Product::get()->isEmpty()) {
            //Productos de prueba
            $products = [
                [
                    'name' => 'Camioneta 4Runner',
                    'brand' => 'Toyota',
                    'price' => 800,
                ],
                [
                    'name' => 'Camioneta RAV4',
                    'brand' => 'Toyota',
                    'price' => 600,
                ],
                [
                    'name' => 'Sedan Aveo',
                    'brand' => 'Chevrolet',
                    'price' => 180,
                ],
            ];
            //Recorremos array para crear perfiles
            foreach($products as $product) {
                //creamos nuevo registro
                $newProduct = Product::create([
                    'product_code' => bin2hex(random_bytes(5)),
                    'name' => $product['name'],
                    'brand' => $product['brand'],
                    'price' => $product['price'],
                ]);
            }
        }
        
    }
}
