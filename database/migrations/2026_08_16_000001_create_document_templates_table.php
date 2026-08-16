<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('document_templates')) {
            Schema::create('document_templates', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('title');
                $table->text('download_url');
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (DB::table('document_templates')->count() > 0) {
            return;
        }

        $now = now();

        DB::table('document_templates')->insert([
            [
                'key' => 'proposal',
                'title' => 'Template Proposal Kegiatan',
                'download_url' => 'https://docs.google.com/document/d/1FVxC0QixhmCwKX78PpzR110-NhHEggZg/export?format=pdf',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'laporan',
                'title' => 'Template Laporan Akhir',
                'download_url' => 'https://docs.google.com/document/d/1ra0XgVvPsRDDPLetoXOrSEoyi5CNuQEF/export?format=pdf',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'logbook',
                'title' => 'Template Logbook',
                'download_url' => 'https://docs.google.com/document/d/1qTWObNVRjsQ8jHW5iw8FvdBAZrKd9bxy/export?format=pdf',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'artikel',
                'title' => 'Template Artikel',
                'download_url' => 'https://docs.google.com/document/d/1ra0XgVvPsRDDPLetoXOrSEoyi5CNuQEF/export?format=pdf',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'cpmk',
                'title' => 'Template Form Kesesuaian CPMK',
                'download_url' => 'https://docs.google.com/document/d/17MBG9qzPB3EkYPRj4K-Jk3xFQf3AU70O/export?format=pdf',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
