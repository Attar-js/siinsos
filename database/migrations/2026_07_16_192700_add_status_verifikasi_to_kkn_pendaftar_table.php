<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kkn_pendaftar')) {
            return;
        }

        if (!Schema::hasColumn('kkn_pendaftar', 'status_verifikasi')) {
            Schema::table('kkn_pendaftar', function (Blueprint $table) {
                $table->enum('status_verifikasi', ['pending', 'diterima', 'ditolak'])
                    ->default('pending')
                    ->after('status');
            });

            // Salin nilai dari kolom status lama jika cocok.
            DB::table('kkn_pendaftar')
                ->whereIn('status', ['pending', 'diterima', 'ditolak'])
                ->update([
                    'status_verifikasi' => DB::raw('status'),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kkn_pendaftar') && Schema::hasColumn('kkn_pendaftar', 'status_verifikasi')) {
            Schema::table('kkn_pendaftar', function (Blueprint $table) {
                $table->dropColumn('status_verifikasi');
            });
        }
    }
};
