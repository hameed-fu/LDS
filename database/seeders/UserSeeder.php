<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'username' => 'admin',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
            ],
            [
                'name' => 'teacher User',
                'email' => 'teacher@gmail.com',
                'username' => 'teacher',
                'password' => Hash::make('12345678'),
                'role' => 'teacher',
            ],
            [
                'name' => 'Student User',
                'email' => 'student@gmail.com',
                'username' => 'student',
                'password' => Hash::make('12345678'),
                'role' => 'student',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
