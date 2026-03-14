<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'     => 'Admin Kirefrais',
            'email'    => 'admin@Kirefrais.tg',
            'phone'    => '90000001',
            'password' => 'admin1234', // Casté en hash par le modèle
            'role'     => 'admin',
        ]);

        // Livreur
        User::create([
            'name'     => 'Koffi le Livreur',
            'email'    => 'livreur@Kirefrais.tg',
            'phone'    => '90000002',
            'password' => 'password',
            'role'     => 'livreur',
        ]);

        // Client test
        User::create([
            'name'     => 'Amédée Togolais',
            'email'    => 'client@gmail.com',
            'phone'    => '91223344',
            'password' => 'password',
            'role'     => 'client',
        ]);
    }
}
