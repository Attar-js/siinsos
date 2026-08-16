<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\VerifikasiPendaftarController;
use App\Http\Controllers\Admin\PendaftarController;
use App\Http\Controllers\Admin\ProposalController;
use App\Http\Controllers\Admin\LaporanAkhirController;
use App\Http\Controllers\Admin\LuaranController;
use App\Http\Controllers\Admin\PeerReviewController;
use App\Http\Controllers\Admin\FormKesediaanController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\NilaiCpmkController;
use App\Http\Controllers\Admin\NilaiAkhirController;
use App\Http\Controllers\Admin\AssignKelompokController;
use App\Http\Controllers\Admin\TambahDosenController;
use App\Http\Controllers\Admin\TambahAdminController;
use App\Http\Controllers\Admin\ImportUserController;
use App\Http\Controllers\Admin\TemplateDokumenController;
use App\Http\Controllers\Admin\PenilaianController;
use App\Http\Controllers\Admin\Security\RolePermission;
use App\Http\Controllers\Admin\Security\RoleController;
use App\Http\Controllers\Admin\Security\PermissionController;

/*
|--------------------------------------------------------------------------
| Admin (Dashboard) Routes
|--------------------------------------------------------------------------
| Merged from the former separate "dashboard" application. Mounted under the
| "/admin" URL prefix and "admin." route-name prefix to avoid collisions with
| the landing (mahasiswa/dosen/tim penciri) routes.
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Role & Permission
    Route::get('/role-permission', [RolePermission::class, 'index'])->name('role.permission.list');
    Route::resource('permission', PermissionController::class);
    Route::resource('role', RoleController::class);

    // Dashboard (Admin only)
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data', [DashboardController::class, 'getDashboardDataAjax'])->name('dashboard.data');
    });

    // Users
    Route::resource('users', UserController::class);

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Special Pages (list views)
    Route::prefix('special-pages')->group(function () {
        Route::get('pendaftar', [VerifikasiPendaftarController::class, 'index'])->name('special-pages.pendaftar');
        Route::get('pendaftar/{group}', [VerifikasiPendaftarController::class, 'show'])->name('special-pages.pendaftar.show');
        Route::post('pendaftar/{group}/verifikasi', [VerifikasiPendaftarController::class, 'verify'])->name('special-pages.pendaftar.verifikasi');
        Route::get('luaran', [LuaranController::class, 'index'])->name('special-pages.luaran');
        Route::get('proposal', [ProposalController::class, 'index'])->name('special-pages.proposal');
        Route::get('laporan-akhir', [LaporanAkhirController::class, 'index'])->name('special-pages.laporan-akhir');
        Route::get('peer-review', [PeerReviewController::class, 'index'])->name('special-pages.peer-review');
        Route::get('form-kesediaan', [FormKesediaanController::class, 'index'])->name('special-pages.form-kesediaan');
    });

    // Pendaftar CRUD
    Route::prefix('pendaftar')->group(function () {
        Route::post('store', [PendaftarController::class, 'store'])->name('pendaftar.store');
        Route::put('update/{id}', [PendaftarController::class, 'update'])->name('pendaftar.update');
        Route::put('verifikasi/{id}', [PendaftarController::class, 'verifikasi'])->name('pendaftar.verifikasi');
        Route::delete('destroy/{id}', [PendaftarController::class, 'destroy'])->name('pendaftar.destroy');
        Route::get('config', [PendaftarController::class, 'getDashboardConfig'])->name('pendaftar.config');
    });

    // Luaran CRUD
    Route::prefix('luaran')->group(function () {
        Route::post('store', [LuaranController::class, 'store'])->name('luaran.store');
        Route::put('update/{id}', [LuaranController::class, 'update'])->name('luaran.update');
        Route::put('verifikasi/{id}', [LuaranController::class, 'verifikasi'])->name('luaran.verifikasi');
        Route::delete('destroy/{id}', [LuaranController::class, 'destroy'])->name('luaran.destroy');
    });

    // Proposal CRUD
    Route::prefix('proposal')->group(function () {
        Route::post('store', [ProposalController::class, 'store'])->name('proposal.store');
        Route::put('update/{id}', [ProposalController::class, 'update'])->name('proposal.update');
        Route::put('verifikasi/{id}', [ProposalController::class, 'verifikasi'])->name('proposal.verifikasi');
        Route::delete('destroy/{id}', [ProposalController::class, 'destroy'])->name('proposal.destroy');
    });

    // Laporan Akhir CRUD
    Route::prefix('laporan-akhir')->group(function () {
        Route::post('store', [LaporanAkhirController::class, 'store'])->name('laporan-akhir.store');
        Route::put('update/{id}', [LaporanAkhirController::class, 'update'])->name('laporan-akhir.update');
        Route::put('verifikasi/{id}', [LaporanAkhirController::class, 'verifikasi'])->name('laporan-akhir.verifikasi');
        Route::delete('destroy/{id}', [LaporanAkhirController::class, 'destroy'])->name('laporan-akhir.destroy');
    });

    // Peer Review CRUD
    Route::prefix('peer-review')->group(function () {
        Route::post('store', [PeerReviewController::class, 'store'])->name('peer-review.store');
        Route::put('update/{id}', [PeerReviewController::class, 'update'])->name('peer-review.update');
        Route::put('verifikasi/{id}', [PeerReviewController::class, 'verifikasi'])->name('peer-review.verifikasi');
        Route::delete('destroy/{id}', [PeerReviewController::class, 'destroy'])->name('peer-review.destroy');
    });

    // Form Kesediaan CRUD
    Route::prefix('form-kesediaan')->group(function () {
        Route::post('store', [FormKesediaanController::class, 'store'])->name('form-kesediaan.store');
        Route::put('update/{id}', [FormKesediaanController::class, 'update'])->name('form-kesediaan.update');
        Route::put('verifikasi/{id}', [FormKesediaanController::class, 'verifikasi'])->name('form-kesediaan.verifikasi');
        Route::delete('destroy/{id}', [FormKesediaanController::class, 'destroy'])->name('form-kesediaan.destroy');
    });

    // Files (serve / download / sync)
    Route::prefix('files')->group(function () {
        Route::get('pdf/{filename}', [FileController::class, 'showPdf'])->name('files.pdf.show');
        Route::get('pdf/{filename}/download', [FileController::class, 'downloadPdf'])->name('files.pdf.download');
        Route::get('check/{filename}', [FileController::class, 'checkFile'])->name('files.check');
        Route::get('list', [FileController::class, 'listFiles'])->name('files.list');
        Route::get('status', [FileController::class, 'checkFileStatus'])->name('files.status');
        Route::post('fix-missing', [FileController::class, 'fixMissingFiles'])->name('files.fix-missing');
        Route::post('create-sample', [FileController::class, 'createSampleFile'])->name('files.create-sample');
        Route::post('sync-from-project', [FileController::class, 'syncFromProjectAkhir'])->name('files.sync');
        Route::get('pdf-db/{filename}', [FileController::class, 'showPdfFromDatabase'])->name('files.pdf.db');
        Route::get('pdf-db/{filename}/download', [FileController::class, 'downloadPdfFromDatabase'])->name('files.pdf.db.download');
        Route::get('check-db/{filename}', [FileController::class, 'checkFileInDatabase'])->name('files.check.db');
        Route::post('sync-to-db/{filename}', [FileController::class, 'syncFileToDatabase'])->name('files.sync.to.db');
        Route::get('pdf-dashboard/{filename}', [FileController::class, 'showPdfFromHopeUi'])->name('files.pdf.dashboard');
        Route::get('pdf-dashboard/{filename}/download', [FileController::class, 'downloadPdfFromHopeUi'])->name('files.pdf.dashboard.download');
        Route::get('pdf-luaran/{filename}', [FileController::class, 'showPdfFromLuaran'])->name('files.pdf.luaran');
        Route::get('pdf-luaran/{filename}/download', [FileController::class, 'downloadPdfFromLuaran'])->name('files.pdf.luaran.download');
        Route::get('pdf-proposal/{filename}', [FileController::class, 'showPdfFromProposal'])->name('files.pdf.proposal');
        Route::get('pdf-proposal/{filename}/download', [FileController::class, 'downloadPdfFromProposal'])->name('files.pdf.proposal.download');
        Route::get('pdf-laporan-akhir/{filename}', [FileController::class, 'showPdfFromLaporanAkhir'])->name('files.pdf.laporan-akhir');
        Route::get('pdf-laporan-akhir/{filename}/download', [FileController::class, 'downloadPdfFromLaporanAkhir'])->name('files.pdf.laporan-akhir.download');
        Route::get('pdf-peer-review/{filename}', [FileController::class, 'showPdfFromPeerReview'])->name('files.pdf.peer-review');
        Route::get('pdf-peer-review/{filename}/download', [FileController::class, 'downloadPdfFromPeerReview'])->name('files.pdf.peer-review.download');
        Route::get('pdf-form-kesediaan/{filename}', [FileController::class, 'showPdfFromFormKesediaan'])->name('files.pdf.form-kesediaan');
        Route::get('pdf-form-kesediaan/{filename}/download', [FileController::class, 'downloadPdfFromFormKesediaan'])->name('files.pdf.form-kesediaan.download');
    });

    // Nilai CPMK
    Route::prefix('nilai-cpmk')->group(function () {
        Route::get('/', [NilaiCpmkController::class, 'index'])->name('nilai-cpmk.index');
        Route::get('create', [NilaiCpmkController::class, 'create'])->name('nilai-cpmk.create');
        Route::post('store', [NilaiCpmkController::class, 'store'])->name('nilai-cpmk.store');
        Route::get('edit/{nilaiCpmk}', [NilaiCpmkController::class, 'edit'])->name('nilai-cpmk.edit');
        Route::put('update/{nilaiCpmk}', [NilaiCpmkController::class, 'update'])->name('nilai-cpmk.update');
        Route::delete('destroy/{nilaiCpmk}', [NilaiCpmkController::class, 'destroy'])->name('nilai-cpmk.destroy');
        Route::patch('unassign/{nilaiCpmk}', [NilaiCpmkController::class, 'unassign'])->name('nilai-cpmk.unassign');
        Route::post('unassign/{nilaiCpmk}', [NilaiCpmkController::class, 'unassign']);
        Route::get('download/{nilaiCpmk}', [NilaiCpmkController::class, 'download'])->name('nilai-cpmk.download');
        Route::get('view/{nilaiCpmk}', [NilaiCpmkController::class, 'view'])->name('nilai-cpmk.view');
        Route::get('api/search-students', [NilaiCpmkController::class, 'searchStudents'])->name('nilai-cpmk.api.search-students');
        Route::get('api/all-students', [NilaiCpmkController::class, 'getAllStudents'])->name('nilai-cpmk.api.all-students');
        Route::get('api/student-details/{id}', [NilaiCpmkController::class, 'getStudentDetails'])->name('nilai-cpmk.api.student-details');
        Route::get('api/get-student-by-id', [NilaiCpmkController::class, 'getStudentById'])->name('nilai-cpmk.api.get-student-by-id');
        Route::post('api/assign-mahasiswa', [NilaiCpmkController::class, 'assignMahasiswa'])->name('nilai-cpmk.api.assign-mahasiswa');
    });

    // Assign Kelompok
    Route::prefix('assign-kelompok')->group(function () {
        Route::get('/', [AssignKelompokController::class, 'index'])->name('assign-kelompok.index');
        Route::post('/assign/{group_id}', [AssignKelompokController::class, 'assignDosen'])->name('assign-kelompok.assign-dosen');
        Route::delete('/hapus/{group_id}', [AssignKelompokController::class, 'hapusAssignment'])->name('assign-kelompok.hapus');
        Route::get('/assigned-groups', [AssignKelompokController::class, 'assignedGroups'])->name('assign-kelompok.assigned-groups');
    });

    // Nilai Akhir
    Route::prefix('nilai-akhir')->group(function () {
        Route::get('/', [NilaiAkhirController::class, 'index'])->name('nilai-akhir.index');
        Route::get('/refresh', [NilaiAkhirController::class, 'refresh'])->name('nilai-akhir.refresh');
        Route::get('/export/csv', [NilaiAkhirController::class, 'exportCsv'])->name('nilai-akhir.export.csv');
        Route::get('/export', [NilaiAkhirController::class, 'exportExcel'])->name('nilai-akhir.export');
        Route::get('/detail/{nim}', [NilaiAkhirController::class, 'detail'])->name('nilai-akhir.detail');
        Route::delete('/{id}', [NilaiAkhirController::class, 'destroy'])->name('nilai-akhir.destroy');
        Route::get('/api/real-time-data', [NilaiAkhirController::class, 'getRealTimeData'])->name('nilai-akhir.api.real-time');
        Route::get('/api/real-time-with-notification', [NilaiAkhirController::class, 'getRealTimeDataWithNotification'])->name('nilai-akhir.api.real-time-notification');
    });

    // Tambah Dosen
    Route::prefix('tambah-dosen')->group(function () {
        Route::get('/', [TambahDosenController::class, 'index'])->name('tambah-dosen.index');
        Route::post('/', [TambahDosenController::class, 'store'])->name('tambah-dosen.store');
        Route::get('/{id}/edit', [TambahDosenController::class, 'edit'])->name('tambah-dosen.edit');
        Route::put('/{id}', [TambahDosenController::class, 'update'])->name('tambah-dosen.update');
        Route::delete('/{id}', [TambahDosenController::class, 'destroy'])->name('tambah-dosen.destroy');
    });

    // Impor Pengguna Massal (Admin only)
    Route::middleware('admin')->prefix('import')->name('import.')->group(function () {
        Route::get('{role}/template', [ImportUserController::class, 'template'])
            ->whereIn('role', ['mahasiswa', 'dosen'])
            ->name('template');
        Route::get('{role}', [ImportUserController::class, 'show'])
            ->whereIn('role', ['mahasiswa', 'dosen'])
            ->name('show');
        Route::post('{role}', [ImportUserController::class, 'import'])
            ->whereIn('role', ['mahasiswa', 'dosen'])
            ->name('store');
    });

    // Tambah Admin (Admin only)
    Route::middleware('admin')->prefix('tambah-admin')->group(function () {
        Route::get('/', [TambahAdminController::class, 'index'])->name('tambah-admin.index');
        Route::post('/', [TambahAdminController::class, 'store'])->name('tambah-admin.store');
        Route::get('/{id}/edit', [TambahAdminController::class, 'edit'])->name('tambah-admin.edit');
        Route::put('/{id}', [TambahAdminController::class, 'update'])->name('tambah-admin.update');
        Route::delete('/{id}', [TambahAdminController::class, 'destroy'])->name('tambah-admin.destroy');
    });

    // Template dokumen (link unduh untuk halaman landing)
    Route::middleware('admin')->prefix('template-dokumen')->name('template-dokumen.')->group(function () {
        Route::get('/', [TemplateDokumenController::class, 'index'])->name('index');
        Route::post('/', [TemplateDokumenController::class, 'store'])->name('store');
        Route::put('/{template}', [TemplateDokumenController::class, 'update'])->name('update');
        Route::delete('/{template}', [TemplateDokumenController::class, 'destroy'])->name('destroy');
    });

    // Penilaian
    Route::prefix('penilaian')->group(function () {
        Route::get('/', [PenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('/create', [PenilaianController::class, 'create'])->name('penilaian.create');
        Route::post('/', [PenilaianController::class, 'store'])->name('penilaian.store');
        Route::get('/{id}', [PenilaianController::class, 'show'])->name('penilaian.show');
        Route::get('/{id}/edit', [PenilaianController::class, 'edit'])->name('penilaian.edit');
        Route::put('/{id}', [PenilaianController::class, 'update'])->name('penilaian.update');
        Route::delete('/{id}', [PenilaianController::class, 'destroy'])->name('penilaian.destroy');
        Route::get('/download/{filename}', [PenilaianController::class, 'downloadFile'])->name('penilaian.download');
    });
});
