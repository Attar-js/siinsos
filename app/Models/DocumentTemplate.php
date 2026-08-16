<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'key',
        'title',
        'download_url',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('download_url', '!=', '');
    }

    public static function makeUniqueKey(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'template';
        $key = $base;
        $i = 2;

        while (
            static::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('key', $key)
                ->exists()
        ) {
            $key = $base . '-' . $i;
            $i++;
        }

        return $key;
    }
}
