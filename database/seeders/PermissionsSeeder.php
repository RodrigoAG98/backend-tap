<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Sino hay permisos los generamos
        if (Permission::get()->isEmpty()) {
            /*
            * Creacion de permisos
            * Sección o modulo mas el permiso
            * Ej. users:create
            */
            $permisos = ['create','read','update','delete','export']; 
            $modulos =['users','profiles','products'];
            foreach($modulos as $modulo) {
                foreach ($permisos as $permiso) {
                    Permission::create(['name' => sprintf('%s:%s', $modulo, $permiso), 'guard_name' => 'api']);
                }
            }
        }
    }
}
