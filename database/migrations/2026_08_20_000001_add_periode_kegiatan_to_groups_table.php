<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            if (! Schema::hasColumn('groups', 'semester')) {
                $table->unsignedTinyInteger('semester')->nullable()->after('judul_kegiatan');
            }
            if (! Schema::hasColumn('groups', 'tahun_kegiatan')) {
                $table->unsignedSmallInteger('tahun_kegiatan')->nullable()->after('semester');
            }
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            if (Schema::hasColumn('groups', 'tahun_kegiatan')) {
                $table->dropColumn('tahun_kegiatan');
            }
            if (Schema::hasColumn('groups', 'semester')) {
                $table->dropColumn('semester');
            }
        });
    }
};
