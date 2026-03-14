<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ────────────────────────────────────────────────
        User::create([
            'name'              => 'Administrateur kirefrais',
            'email'             => 'admin@kirefrais.tg',
            'phone'             => '+22890000001',
            'password'          => Hash::make('kirefrais@2026'),
            'role'              => 'admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // ── Livreurs ─────────────────────────────────────────────
        $livreurs = [
            [
                'name'  => 'Kofi Mensah',
                'email' => 'kofi.mensah@kirefrais.tg',
                'phone' => '+22891234501',
            ],
            [
                'name'  => 'Akossiwa Dossou',
                'email' => 'akossiwa.dossou@kirefrais.tg',
                'phone' => '+22891234502',
            ],
            [
                'name'  => 'Yao Agbéko',
                'email' => 'yao.agbeko@kirefrais.tg',
                'phone' => '+22891234503',
            ],
            [
                'name'  => 'Ama Fiagbé',
                'email' => 'ama.fiagbe@kirefrais.tg',
                'phone' => '+22891234504',
            ],
        ];

        foreach ($livreurs as $livreur) {
            User::create(array_merge($livreur, [
                'password'          => Hash::make('livreur@2026'),
                'role'              => 'livreur',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]));
        }

        // ── Clients test ─────────────────────────────────────────
        $clients = [
            [
                'name'  => 'Afi Koudjo',
                'email' => 'afi.koudjo@gmail.com',
                'phone' => '+22898765401',
            ],
            [
                'name'  => 'Kodjo Abalo',
                'email' => 'kodjo.abalo@gmail.com',
                'phone' => '+22898765402',
            ],
            [
                'name'  => 'Mawuli Sossou',
                'email' => 'mawuli.sossou@gmail.com',
                'phone' => '+22898765403',
            ],
            [
                'name'  => 'Efua Agbénou',
                'email' => 'efua.agbenou@gmail.com',
                'phone' => '+22898765404',
            ],
            [
                'name'  => 'Kossi Adomou',
                'email' => 'kossi.adomou@gmail.com',
                'phone' => '+22898765405',
            ],
        ];

        foreach ($clients as $client) {
            User::create(array_merge($client, [
                'password'          => Hash::make('client@2026'),
                'role'              => 'client',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]));
        }
    }
}