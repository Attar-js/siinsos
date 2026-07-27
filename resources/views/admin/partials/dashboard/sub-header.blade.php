

<div class="iq-navbar-header" style="height: 215px;">
    <div class="container-fluid iq-container">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        @if(request()->routeIs('special-pages.pendaftar.show'))
                            <h1>Verifikasi Pendaftar</h1>
                            <p class="vp-page-subtitle mb-0">Detail kelompok — isi skor CPMK setelah dosen menyetujui dan mahasiswa mengisi deskripsi kegiatan.</p>
                        @elseif(request()->routeIs('special-pages.pendaftar*'))
                            <h1>Verifikasi Pendaftar</h1>
                            <p class="vp-page-subtitle mb-0">Verifikasi dilakukan dengan mengisi skor CPMK setelah dosen pembimbing menyetujui kelompok bimbingannya.</p>
                        @elseif(request()->routeIs('nilai-akhir*'))
                            <h1>Penilaian Akhir Mahasiswa</h1>
                            <p class="vp-page-subtitle mb-0">Rekap nilai akhir tiap mahasiswa berdasarkan penilaian dosen pembimbing.</p>
                        @elseif(request()->routeIs('profile.*'))
                            <h1>Profil Saya</h1>
                            <p class="vp-page-subtitle mb-2">Inovasi Sosial</p>
                            <nav class="page-header-breadcrumb" aria-label="Breadcrumb">
                                <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('admin.special-pages.pendaftar') }}">Beranda</a>
                                <span>/</span>
                                <span class="page-header-breadcrumb-current">Profil Saya</span>
                            </nav>
                        @elseif(request()->routeIs('dashboard'))
                            <p class="db-welcome-date mb-2">{{ \App\Http\Controllers\DashboardController::welcomeDateLabel() }}</p>
                            <h1 class="db-welcome-title mb-2">
                                Selamat datang kembali,
                                <span class="db-welcome-name">{{ \App\Http\Controllers\DashboardController::displayUserName() }}</span>.
                            </h1>
                            <p class="vp-page-subtitle mb-0">Inovasi Sosial</p>
                        @else
                            <h1>Tim Penciri</h1>
                            <p>Inovasi Sosial</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="iq-header-img">
        <img src="{{asset('images/dashboard/top-header.png')}}" alt="header" class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX">
        <img src="{{asset('images/dashboard/top-header1.png')}}" alt="header" class="theme-color-purple-img img-fluid w-100 h-100 animated-scaleX">
        <img src="{{asset('images/dashboard/top-header2.png')}}" alt="header" class="theme-color-blue-img img-fluid w-100 h-100 animated-scaleX">
        <img src="{{asset('images/dashboard/top-header3.png')}}" alt="header" class="theme-color-green-img img-fluid w-100 h-100 animated-scaleX">
        <img src="{{asset('images/dashboard/top-header4.png')}}" alt="header" class="theme-color-yellow-img img-fluid w-100 h-100 animated-scaleX">
        <img src="{{asset('images/dashboard/top-header5.png')}}" alt="header" class="theme-color-pink-img img-fluid w-100 h-100 animated-scaleX">
    </div>
</div>
