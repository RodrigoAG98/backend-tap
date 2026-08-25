<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Sino hay registros los creamos
        if(Profile::get()->isEmpty()) {
            //Perfiles base
            $profiles = ['Administrador','Empleado'];
            //Recorremos array para crear perfiles
            foreach($profiles as $profile){
                Profile::create([
                    'profile_code' => bin2hex(random_bytes(5)),
                    'name' => $profile,
                    'sections' => [],
                ]);
            }
        }
        
    }
}
