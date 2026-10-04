<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'name'          => 'Super Admin',
            'email'         => 'admin@admin.com',
            'mobile_number' => '0123456789',
            'password'      => Hash::make('admin123'),
            'photo'         => null, 
        ]);
    }
}
