<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('study_program')) {
            Schema::create('study_program', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->json('name');
                $table->string('id_prodi_gerbang');
                $table->unsignedBigInteger('study_program_type_id')->nullable();
                $table->timestamps();

                $table->index('id_prodi_gerbang');
            });
        }

        if (class_exists(\Database\Seeders\StudyProgramSeeder::class)) {
            (new \Database\Seeders\StudyProgramSeeder())->run();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('study_program');
    }
};
