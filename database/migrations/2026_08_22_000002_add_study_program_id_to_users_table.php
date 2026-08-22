<?php

use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'study_program_id')) {
                $table->unsignedBigInteger('study_program_id')->nullable()->after('program_studi');
                $table->foreign('study_program_id')->references('id')->on('study_program')->nullOnDelete();
            }
        });

        if (Schema::hasTable('study_program') && Schema::hasColumn('users', 'program_studi')) {
            User::query()
                ->whereNull('study_program_id')
                ->whereNotNull('program_studi')
                ->where('program_studi', '!=', '')
                ->each(function (User $user) {
                    $id = StudyProgram::findIdByLegacyName($user->program_studi);
                    if ($id) {
                        $user->forceFill(['study_program_id' => $id])->saveQuietly();
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'study_program_id')) {
                $table->dropForeign(['study_program_id']);
                $table->dropColumn('study_program_id');
            }
        });
    }
};
