<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    protected $table = 'study_program';

    protected $fillable = [
        'department_id',
        'name',
        'id_prodi_gerbang',
        'study_program_type_id',
    ];

    protected $casts = [
        'name' => 'array',
        'department_id' => 'integer',
        'study_program_type_id' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'study_program_id');
    }

    public static function findIdByLegacyName(?string $name): ?int
    {
        $needle = mb_strtolower(trim((string) $name));
        if ($needle === '') {
            return null;
        }

        return static::query()
            ->where(function ($q) use ($needle) {
                $q->whereRaw('LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(name, "$.id")))) = ?', [$needle])
                    ->orWhereRaw('LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en")))) = ?', [$needle]);
            })
            ->value('id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->id,
            'department_id' => $this->department_id !== null ? (int) $this->department_id : null,
            'name' => $this->name,
            'id_prodi_gerbang' => $this->id_prodi_gerbang,
            'study_program_type_id' => $this->study_program_type_id !== null ? (int) $this->study_program_type_id : null,
        ];
    }
}
