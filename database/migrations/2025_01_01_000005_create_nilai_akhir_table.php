<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nilai_akhir')) {
            return;
        }

        Schema::create('nilai_akhir', function (Blueprint $table) {
            $table->id();
            $table->string('mahasiswa_nim', 20);
            $table->unsignedBigInteger('dosen_id');
            $table->decimal('nilai_akhir', 5, 2);
            $table->decimal('proposal_kegiatan', 5, 2)->nullable();
            $table->decimal('peer_review', 5, 2)->nullable();
            $table->decimal('laporan_akhir', 5, 2)->nullable();
            $table->decimal('presentasi_akhir', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_penilaian')->useCurrent();
            $table->timestamps();
            $table->unique(['mahasiswa_nim', 'dosen_id']);
            $table->index('mahasiswa_nim');
            $table->index('dosen_id');
            $table->index('nilai_akhir');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_akhir');
    }
};
