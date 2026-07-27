<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom-kolom tambahan pada tabel users (username, nim, dsb.)
 * lebih awal, sebelum migrasi lain yang bergantung pada kolom tersebut.
 * Idempoten: memakai Schema::hasColumn sehingga aman dijalankan berulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('users', 'nim')) {
                $table->string('nim', 30)->nullable()->unique()->after('username');
            }
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip', 30)->nullable()->after('nim');
            }
            if (!Schema::hasColumn('users', 'program_studi')) {
                $table->string('program_studi', 100)->nullable()->after('nip');
            }
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('program_studi');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 30)->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type', 30)->default('user')->after('phone_number');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('mahasiswa')->after('user_type');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 30)->default('active')->after('role');
            }
        });
    }

    public function down(): void
    {
        // Deployment sync — tidak di-rollback otomatis.
    }
};
