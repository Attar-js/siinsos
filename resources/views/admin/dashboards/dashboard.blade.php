<x-app-layout :assets="$assets ?? []">
<link rel="stylesheet" href="{{ asset('css/dashboard-home.css') }}">

<div class="db-home">
    <div class="db-stat-grid">
        <div class="db-stat-card">
            <div class="db-stat-icon db-stat-icon--blue">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <p class="db-stat-number" id="stat-total-kelompok">{{ number_format($dashboardData['stats']['total_kelompok']) }}</p>
                <p class="db-stat-label">Total Pendaftar</p>
            </div>
        </div>

        <div class="db-stat-card">
            <div class="db-stat-icon db-stat-icon--yellow">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="db-stat-number" id="stat-menunggu">{{ number_format($dashboardData['stats']['menunggu_verifikasi']) }}</p>
                <p class="db-stat-label">Menunggu Verifikasi</p>
            </div>
        </div>

        <div class="db-stat-card">
            <div class="db-stat-icon db-stat-icon--green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="db-stat-number" id="stat-terverifikasi">{{ number_format($dashboardData['stats']['terverifikasi']) }}</p>
                <p class="db-stat-label">Terverifikasi</p>
            </div>
        </div>

        <div class="db-stat-card">
            <div class="db-stat-icon db-stat-icon--red">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <p class="db-stat-number" id="stat-mahasiswa">{{ number_format($dashboardData['stats']['total_mahasiswa']) }}</p>
                <p class="db-stat-label">Total Mahasiswa</p>
            </div>
        </div>
    </div>

    <div class="db-main-grid">
        <div class="db-panel">
            <div class="db-panel-header">
                <div>
                    <h2 class="db-panel-title">Antrian Verifikasi</h2>
                    <p class="db-panel-subtitle">Sudah disetujui dosen pembimbing, menunggu skor CPMK Anda</p>
                </div>
                <a href="{{ route('admin.special-pages.pendaftar', ['filter' => 'perlu_diverifikasi']) }}" class="db-panel-link">
                    Lihat Semua ↗
                </a>
            </div>
            <div class="db-panel-body" id="verification-queue">
                @forelse($dashboardData['verification_queue'] as $index => $item)
                    <a href="{{ $item['url'] }}" class="db-queue-item">
                        <span class="db-queue-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="db-queue-content">
                            <p class="db-queue-title">{{ $item['judul'] }}</p>
                            <p class="db-queue-subtitle">Kelompok {{ $item['kelompok'] }}</p>
                        </div>
                        <span class="db-queue-badge">Menunggu verifikasi</span>
                    </a>
                @empty
                    <div class="db-empty-state">
                        Tidak ada kelompok yang menunggu verifikasi saat ini.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="db-panel">
            <div class="db-panel-header">
                <div>
                    <h2 class="db-panel-title">Aktivitas Terbaru</h2>
                    <p class="db-panel-subtitle">Aksi mahasiswa &amp; dosen</p>
                </div>
            </div>
            <ul class="db-activity-list" id="recent-activities">
                @forelse($dashboardData['recent_activities'] as $activity)
                    <li class="db-activity-item">
                        <span class="db-activity-dot" aria-hidden="true"></span>
                        <div>
                            <p class="db-activity-title">{{ $activity['title'] }}</p>
                            <p class="db-activity-message">{{ $activity['message'] }}</p>
                            <span class="db-activity-time">{{ $activity['time'] }}</span>
                        </div>
                    </li>
                @empty
                    <li class="db-empty-state">Belum ada aktivitas terbaru.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<script>
setInterval(function () {
    fetch('{{ route('admin.dashboard.data') }}')
        .then(function (response) { return response.json(); })
        .then(function (data) {
            document.getElementById('stat-total-kelompok').textContent = data.stats.total_kelompok.toLocaleString();
            document.getElementById('stat-menunggu').textContent = data.stats.menunggu_verifikasi.toLocaleString();
            document.getElementById('stat-terverifikasi').textContent = data.stats.terverifikasi.toLocaleString();
            document.getElementById('stat-mahasiswa').textContent = data.stats.total_mahasiswa.toLocaleString();
        })
        .catch(function (error) {
            console.error('Error updating dashboard:', error);
        });
}, 30000);
</script>
</x-app-layout>
