<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kkn_anggota')) {
            return;
        }

        Schema::create('kkn_anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kkn_pendaftar_id')->constrained('kkn_pendaftar')->cascadeOnDelete();
            $table->string('nama');
            $table->string('nim');
            $table->string('program_studi');
            $table->enum('peran', ['Ketua', 'Anggota']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kkn_anggota');
    }
};
