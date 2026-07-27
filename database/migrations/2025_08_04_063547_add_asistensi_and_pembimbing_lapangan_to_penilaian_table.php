<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('penilaian', 'pembimbing_lapangan')) {
            return;
        }

        Schema::table('penilaian', function (Blueprint $table) {
            // Kolom acuan "presentasi_akhir" baru dibuat pada migrasi sync yang
            // berjalan lebih belakang, jadi hanya pakai ->after() bila sudah ada.
            $column = $table->decimal('pembimbing_lapangan', 5, 2)->nullable();
            if (Schema::hasColumn('penilaian', 'presentasi_akhir')) {
                $column->after('presentasi_akhir');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropColumn('pembimbing_lapangan');
        });
    }
};

