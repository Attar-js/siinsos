<?php

namespace App\Http\Controllers\Admin;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Penilaian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboardData = $this->getDashboardData();

        return view('admin.dashboards.dashboard', compact('dashboardData'));
    }

    private function getDashboardData(): array
    {
        return [
            'stats' => $this->getStats(),
            'verification_queue' => $this->getVerificationQueue(),
            'recent_activities' => $this->getRecentActivities(),
        ];
    }

    private function getStats(): array
    {
        $menungguQuery = Group::whereNotNull('supervisor_approved_at')
            ->where(function ($query) {
                $query->whereNull('progress_verifikasi')
                    ->orWhere('progress_verifikasi', '<', 100);
            });

        return [
            'total_kelompok' => Group::count(),
            'menunggu_verifikasi' => (clone $menungguQuery)->count(),
            'terverifikasi' => Group::where('progress_verifikasi', '>=', 100)->count(),
            'total_mahasiswa' => GroupMember::where('status', 'active')->distinct('mahasiswa_id')->count('mahasiswa_id'),
        ];
    }

    private function getVerificationQueue(): array
    {
        return Group::whereNotNull('supervisor_approved_at')
            ->where(function ($query) {
                $query->whereNull('progress_verifikasi')
                    ->orWhere('progress_verifikasi', '<', 100);
            })
            ->orderByDesc('supervisor_approved_at')
            ->limit(5)
            ->get()
            ->map(function (Group $group) {
                return [
                    'id' => $group->id,
                    'judul' => $group->judul_kegiatan,
                    'kelompok' => $group->nama_kelompok ?: 'Kelompok KKN',
                    'url' => route('admin.special-pages.pendaftar.show', $group),
                ];
            })
            ->values()
            ->all();
    }

    private function getRecentActivities(): array
    {
        $items = [];

        foreach (Group::latest()->take(5)->get() as $group) {
            $items[] = [
                'title' => 'Pendaftaran baru',
                'message' => "Kelompok {$group->nama_kelompok} ({$group->judul_kegiatan}) telah mendaftar.",
                'timestamp' => $group->created_at->timestamp,
                'time' => $group->created_at->diffForHumans(),
            ];
        }

        foreach (Group::whereNotNull('supervisor_approved_at')->with('dosen')->latest('supervisor_approved_at')->take(5)->get() as $group) {
            $dosen = $group->dosen?->name ?? 'Dosen pembimbing';
            $items[] = [
                'title' => 'Dosen menyetujui proposal',
                'message' => "{$dosen} menyetujui proposal Kelompok {$group->nama_kelompok}.",
                'timestamp' => $group->supervisor_approved_at->timestamp,
                'time' => $group->supervisor_approved_at->diffForHumans(),
            ];
        }

        foreach (Penilaian::with('dosen')->latest('updated_at')->take(5)->get() as $penilaian) {
            $dosen = $penilaian->dosen?->name ?? 'Dosen';
            $items[] = [
                'title' => 'Dosen mengumpulkan penilaian',
                'message' => "{$dosen} mengumpulkan penilaian mahasiswa NIM {$penilaian->mahasiswa_nim}.",
                'timestamp' => $penilaian->updated_at->timestamp,
                'time' => $penilaian->updated_at->diffForHumans(),
            ];
        }

        usort($items, fn (array $a, array $b) => $b['timestamp'] <=> $a['timestamp']);

        return array_map(
            fn (array $item) => [
                'title' => $item['title'],
                'message' => $item['message'],
                'time' => $item['time'],
            ],
            array_slice($items, 0, 6)
        );
    }

    public static function welcomeDateLabel(): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $now = Carbon::now();

        return $days[$now->dayOfWeek] . ', ' . $now->format('j') . ' ' . $months[(int) $now->format('n')] . ' ' . $now->format('Y');
    }

    public static function displayUserName(): string
    {
        $user = Auth::user();
        if (! $user) {
            return 'Admin';
        }

        if (! empty($user->name)) {
            return $user->name;
        }

        $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $fullName !== '' ? $fullName : 'Admin';
    }

    public function getDashboardDataAjax()
    {
        return response()->json($this->getDashboardData());
    }
}
