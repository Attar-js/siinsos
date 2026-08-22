<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'nim',
        'nip',
        'username',
        'program_studi',
        'study_program_id',
        'first_name',
        'last_name',
        'phone_number',
        'user_type',
        'role',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'study_program_id' => 'integer',
        ];
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is mahasiswa
     */
    public function isMahasiswa()
    {
        return $this->hasRole('mahasiswa');
    }

    /**
     * Check if user is dosen
     */
    public function isDosen()
    {
        return $this->hasRole('dosen');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Admin panel access check (merged dashboard). Uses the `role` column,
     * with a fallback to legacy `user_type` values.
     */
    public function isDashboardAdmin(): bool
    {
        if (!empty($this->role) && $this->role === 'admin') {
            return true;
        }

        return in_array($this->user_type, ['admin', 'demo_admin'], true);
    }

    /**
     * Account active check for admin middleware. Treats empty status as active
     * for backward compatibility with older records.
     */
    public function isActiveAccount(): bool
    {
        return empty($this->status) || in_array($this->status, ['active', 'aktif'], true);
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'study_program_id');
    }

    /**
     * Relasi ke penilaian sebagai mahasiswa
     */
    public function penilaianAsMahasiswa()
    {
        return $this->hasMany(Penilaian::class, 'mahasiswa_id');
    }

    /**
     * Relasi ke penilaian sebagai dosen
     */
    public function penilaianAsDosen()
    {
        return $this->hasMany(Penilaian::class, 'dosen_id');
    }

    /**
     * Get penilaian for this user (as mahasiswa)
     */
    public function getPenilaian()
    {
        return $this->penilaianAsMahasiswa()->first();
    }

    public function ledGroups()
    {
        return $this->hasMany(Group::class, 'leader_id');
    }

    public function supervisedGroups()
    {
        return $this->hasMany(Group::class, 'dosen_id');
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Relasi ke profil pengguna (modul dashboard).
     */
    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }
}

