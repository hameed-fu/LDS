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
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Instructor User',
                'email' => 'instructor@gmail.com',
                'username' => 'instructor',
                'password' => Hash::make('password'),
                'role' => 'instructor',
            ],
            [
                'name' => 'Student User',
                'email' => 'student@gmail.com',
                'username' => 'student',
                'password' => Hash::make('password'),
                'role' => 'student',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
