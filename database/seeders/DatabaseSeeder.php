<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Menyiapkan data minimum agar aplikasi gabungan bisa langsung dipakai
     * di database baru mana pun: role Spatie + akun admin/mahasiswa/dosen.
     */
    public function run(): void
    {
        // Role Spatie (dipakai halaman manajemen role/permission di modul admin).
        foreach (['admin', 'user'] as $roleName) {
            DB::table('roles')->updateOrInsert(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        // Akun admin (login modul admin: /admin/dashboard).
        User::updateOrCreate(
            ['email' => 'admin@insos.test'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'first_name' => 'Admin',
                'last_name' => 'INSOS',
                'password' => Hash::make('password123'),
                'user_type' => 'admin',
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // Daftar akun mahasiswa untuk pengujian (login pakai NIM / email).
        $mahasiswa = [
            ['nama' => 'Muhammad Fachrurrozi Attar', 'nim' => '10221051'],
            ['nama' => 'Nur Fadillah', 'nim' => '10221019'],
            ['nama' => 'Adji Muhammad', 'nim' => '10221012'],
            ['nama' => 'Maria Siregar', 'nim' => '10221004'],
            ['nama' => 'Taufik Ilham', 'nim' => '10221081'],
            ['nama' => 'Hardi Wira', 'nim' => '10221049'],
            ['nama' => 'Qudus', 'nim' => '10221048'],
            ['nama' => 'Faiq Athari', 'nim' => '10221052'],
            ['nama' => 'Nandha Aulia', 'nim' => '10221071'],
            ['nama' => 'Muhammad Aulia', 'nim' => '10221055'],
            ['nama' => 'Sheva Aryo', 'nim' => '10221088'],
        ];

        foreach ($mahasiswa as $m) {
            $parts = preg_split('/\s+/', trim($m['nama']));
            $firstName = $parts[0];
            $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

            User::updateOrCreate(
                ['nim' => $m['nim']],
                [
                    'name' => $m['nama'],
                    'username' => $m['nim'],
                    'email' => $m['nim'] . '@student.itk.ac.id',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'program_studi' => 'Informatika',
                    'password' => Hash::make('password123'),
                    'user_type' => 'user',
                    'role' => 'mahasiswa',
                    'status' => 'active',
                ]
            );
        }

        // Akun dosen contoh.
        User::updateOrCreate(
            ['email' => 'dosen@insos.test'],
            [
                'name' => 'Dosen Contoh',
                'username' => 'dosen01',
                'nip' => '199001012020011001',
                'first_name' => 'Dosen',
                'last_name' => 'Contoh',
                'program_studi' => 'Informatika',
                'password' => Hash::make('password123'),
                'user_type' => 'user',
                'role' => 'dosen',
                'status' => 'active',
            ]
        );

        // Akun tim MK penciri (dashboard verifikasi: /tim-penciri/dashboard).
        User::updateOrCreate(
            ['email' => 'penciri@insos.test'],
            [
                'name' => 'Tim MK Penciri',
                'username' => 'penciri',
                'first_name' => 'Tim',
                'last_name' => 'Penciri',
                'program_studi' => 'Informatika',
                'password' => Hash::make('password123'),
                'user_type' => 'user',
                'role' => 'tim_penciri',
                'status' => 'active',
            ]
        );
    }
}
