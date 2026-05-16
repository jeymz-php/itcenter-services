<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['admin_id' => 'ADMIN001'],
            [
                'admin_id' => 'ADMIN001',
                'email'    => 'itcenter@ucc-caloocan.edu.ph',
                'campus'   => 'main',
                'password' => Hash::make('Admin@1234'),
                'role'     => 'super_admin',
            ]
        );
    }
}