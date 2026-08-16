<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'SIINSOS API',
    description: 'Dokumentasi API SIINSOS. Contoh nilai di skema (example) bersifat ilustrasi. Data asli diambil dari database setelah request berhasil.

Cara uji endpoint Nilai Akhir:
1. Klik tombol Authorize di kanan atas.
2. Isi X-API-KEY dengan nilai MITRA_API_KEY pada file .env.
3. Klik Authorize, lalu Execute.

Tanpa langkah itu, server mengembalikan 401 dan data mahasiswa tidak diambil. Tag lain adalah dokumentasi internal dashboard.',
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Server SIINSOS'
)]
#[OA\SecurityScheme(
    securityScheme: 'ApiKeyAuth',
    type: 'apiKey',
    in: 'header',
    name: 'X-API-KEY',
    description: 'Masukkan API key mitra di sini untuk endpoint tarik data nilai.'
)]
#[OA\Tag(name: 'Nilai Akhir', description: 'Tarik data nilai akhir mahasiswa (untuk sistem mitra)')]
#[OA\Tag(name: 'KKN', description: 'Pendaftaran KKN dari mahasiswa')]
#[OA\Tag(name: 'Proposal', description: 'Pengajuan & verifikasi proposal')]
#[OA\Tag(name: 'Laporan Akhir', description: 'Pengumpulan & verifikasi laporan akhir')]
#[OA\Tag(name: 'Luaran', description: 'Pengumpulan & verifikasi luaran')]
#[OA\Tag(name: 'Peer Review', description: 'Pengumpulan & verifikasi peer review')]
#[OA\Tag(name: 'Form Kesediaan', description: 'Form kesediaan dosen pembimbing')]
#[OA\Tag(name: 'Nilai', description: 'Penerimaan & data nilai dari project-akhir')]
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
