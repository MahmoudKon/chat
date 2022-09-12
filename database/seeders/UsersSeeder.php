<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Mahmoud',
            'email' => 'mahmoud@app.com',
            'password' => Hash::make(123)
        ]);

        User::create([
            'name' => 'Emad',
            'email' => 'emad@app.com',
            'password' => Hash::make(123)
        ]);

        User::create([
            'name' => 'Ahmed',
            'email' => 'ahmed@app.com',
            'password' => Hash::make(123)
        ]);

        User::create([
            'name' => 'Mohammed',
            'email' => 'mohammed@app.com',
            'password' => Hash::make(123)
        ]);
    }
}
