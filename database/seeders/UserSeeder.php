<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat Admin User
        $admin = User::firstOrCreate(
            [
                'email' => 'baehaqi@nutrisight.app',
            ],
            [
                'nama_depan' => 'Baehaqi',
                'password' => Hash::make('Ap4sihya#@'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');
    }
}

