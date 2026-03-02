<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'      => 'Admin',
                'email'     => 'admin@teste.com',
                'password'  => Hash::make('admin@teste.com'),
                'type_user' => 'admin',
            ],
            [
                'name'      => 'Usuário',
                'email'     => 'usuario@teste.com',
                'password'  => Hash::make('usuario@teste.com'),
                'type_user' => 'usuario',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
