<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Product;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Perfiles base
        $profiles = ['Administrador','Empleado'];
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
        foreach($profiles as $profile){
            Profile::create([
                'profile_code' => bin2hex(random_bytes(5)),
                'name' => $profile,
                'sections' => [],
            ]);
        }
        //Recorremos array para crear perfiles
        foreach($products as $product){
            //creamos nuevo registro
            $newProduct = Product::create([
                'product_code' => bin2hex(random_bytes(5)),
                'name' => $product['name'],
                'brand' => $product['brand'],
                'price' => $product['price'],
            ]);
        }
        //Obtenemos los ids de los perfiles
        $profilesIds = Profile::get()->pluck('id')->toArray();
        //Creamos y asignamos perfiles al administrador
        User::create([
            'user_code' => bin2hex(random_bytes(5)),
            'user' => 'admin@tap.com',
            'name' => 'Administrador',
            'password' => Hash::make('password'),
            'profiles' => $profilesIds
        ]);
    }
}
