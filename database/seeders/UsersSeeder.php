<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'user' => 'admin@tap.com',
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'isAdmin' => true,
            ],
            [
                'user' => 'empleado@tap.com',
                'name' => 'Empleado',
                'password' => Hash::make('password'),
                'isAdmin' => false,
            ],
        ];
        //Si no hay usuarios los cramos
        if(User::get()->isEmpty()) {
            //Obtenemos los ids de los perfiles
            $profilesIds = Profile::get()->pluck('id')->toArray();
            //Obtenemos el modelo del perfil de empleado
            $empleado = Profile::where('name','Empleado')->first();
            foreach ($users as $usuario) {
               //Creamos y asignamos perfiles al administrador
               //Si es administrador se le asignan todos los perfiles de otro modo el de empleado
                User::create([
                    'user_code' => bin2hex(random_bytes(5)),
                    'user' => $usuario['user'],
                    'name' => $usuario['name'],
                    'password' => Hash::make('password'),
                    'profiles' => $usuario['isAdmin'] ? $profilesIds : [$empleado->id]
                ]);
            }
        }
    }
}
