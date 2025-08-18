<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Domain\Enums\ClientType;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * =================================================================
     * GUÍA DE USO
     * =================================================================
     *
     * 1. CREAR EL ARCHIVO DEL SEEDER:
     * Ejecuta este comando en tu terminal para crear el archivo:
     * php artisan make:seeder UserSeeder
     *
     * 2. COPIAR Y PEGAR ESTE CÓDIGO:
     * Abre el archivo recién creado en 'database/seeders/UserSeeder.php'
     * y reemplaza su contenido con todo el código de este documento.
     *
     * 3. REGISTRAR EL SEEDER:
     * Abre 'database/seeders/DatabaseSeeder.php' y añade la siguiente
     * línea dentro del método run():
     * $this->call(UserSeeder::class);
     *
     * 4. EJECUTAR EL SEEDER:
     * Corre este comando para poblar la base de datos.
     * Se recomienda usar 'migrate:fresh' para empezar con una BD limpia.
     * php artisan migrate:fresh --seed
     *
     * =================================================================
     */
    public function run(): void
    {
        // 1. Crear el Usuario de Soporte (Oculto)
        User::updateOrCreate(
            ['email' => 'soporte@compugigas.com'],
            [
                'name' => 'Soporte Técnico',
                'password' => Hash::make('@Losamoalos3..aa'), // Usa una contraseña segura
                'role' => 'admin',
                'client_type' => null,
                'company' => null,
                'nit' => null
            ]
        );
        
        // 1. Crear el Usuario Administrador
        // ---------------------------------
        // Usamos updateOrCreate para evitar duplicados si el seeder se corre múltiples veces.
        // Busca un usuario con el email 'admin@blanket.com' y si no lo encuentra, lo crea.
        User::updateOrCreate(
            ['email' => 'admin@blanket.com'],
            [
                'name' => 'Admin Blanket',
                'password' => Hash::make('password'), // Contraseña: password
                'role' => 'admin',
                'client_type' => null, // Los admins no son de un tipo de cliente específico
                'company' => null,
                'nit' => null,
            ]
        );

        // 2. Crear un Usuario Cliente de Demostración (Persona Jurídica)
        // -----------------------------------------------------------
        User::updateOrCreate(
            ['email' => 'cliente@empresa.com'],
            [
                'name' => 'Juan Pérez (Contacto)', // Nombre del contacto en la empresa
                'password' => Hash::make('password'), // Contraseña: password
                'role' => 'cliente',
                'client_type' => ClientType::JURIDICA, // Usamos el Enum para el tipo
                'company' => 'Empresa Ejemplo S.A.S.', // Razón Social
                'nit' => '900.123.456-7', // NIT de la empresa
            ]
        );

        // Puedes añadir más clientes aquí si lo necesitas.
        // Ejemplo de un cliente Persona Natural:
        /*
        User::updateOrCreate(
            ['email' => 'ana.gomez@email.com'],
            [
                'name' => 'Ana Gómez',
                'password' => Hash::make('password'),
                'role' => 'cliente',
                'client_type' => ClientType::NATURAL,
                'company' => null,
                'nit' => null,
            ]
        );
        */
    }
}
