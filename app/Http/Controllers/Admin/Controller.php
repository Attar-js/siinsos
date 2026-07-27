<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'KKN Integration Dashboard API',
    description: 'Dokumentasi API dashboard KKN. API ini dipakai untuk integrasi dengan aplikasi project-akhir (portal mahasiswa & dosen) yang mengirim data pendaftaran, dokumen, dan nilai ke dashboard.',
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Server lokal dashboard'
)]
#[OA\SecurityScheme(
    securityScheme: 'ApiKeyAuth',
    type: 'apiKey',
    in: 'header',
    name: 'X-API-KEY',
    description: 'Masukkan API key mitra di sini untuk endpoint tarik data nilai.'
)]
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
