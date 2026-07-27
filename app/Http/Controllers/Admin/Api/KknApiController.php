<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Controller;
use App\Models\KknPendaftar;
use App\Models\KknAnggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class KknApiController extends Controller
{
    /**
     * Menyimpan data pendaftaran dari project-akhir
     */
    #[OA\Post(
        path: '/api/kkn/store-from-external',
        tags: ['KKN'],
        summary: 'Menerima pendaftaran KKN dari project-akhir',
        description: 'Menyimpan data pendaftaran KKN beserta anggota kelompok dan file proposal (PDF). Dikirim oleh aplikasi project-akhir saat mahasiswa mendaftar.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['judul_kegiatan', 'mitra', 'lokasi_mitra', 'nama[]', 'nim[]', 'prodi[]', 'peran[]', 'file', 'user_nim'],
                    properties: [
                        new OA\Property(property: 'judul_kegiatan', type: 'string', maxLength: 255, example: 'KKN Tematik Desa Sukamaju'),
                        new OA\Property(property: 'mitra', type: 'string', maxLength: 100, example: 'Pemerintah Desa Sukamaju'),
                        new OA\Property(property: 'lokasi_mitra', type: 'string', maxLength: 255, example: 'Desa Sukamaju, Kab. Bandung'),
                        new OA\Property(property: 'nama[]', type: 'array', items: new OA\Items(type: 'string'), example: ['Budi Santoso', 'Siti Aminah']),
                        new OA\Property(property: 'nim[]', type: 'array', items: new OA\Items(type: 'string'), example: ['2010001', '2010002']),
                        new OA\Property(property: 'prodi[]', type: 'array', items: new OA\Items(type: 'string'), example: ['Teknik Informatika', 'Sistem Informasi']),
                        new OA\Property(property: 'peran[]', type: 'array', items: new OA\Items(type: 'string', enum: ['Ketua', 'Anggota']), example: ['Ketua', 'Anggota']),
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'File proposal PDF (maks 10MB)'),
                        new OA\Property(property: 'user_nim', type: 'string', maxLength: 20, example: '2010001'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Pendaftaran berhasil disimpan'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
            new OA\Response(response: 500, description: 'Terjadi kesalahan server'),
        ]
    )]
    public function storeFromExternal(Request $request)
    {
        // Debug logging
        \Log::info('KknApiController@storeFromExternal called', [
            'request_all' => $request->all(),
            'has_file' => $request->hasFile('file'),
            'files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type')
        ]);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'mitra' => 'required|string|max:100',
            'lokasi_mitra' => 'required|string|max:255',
            'nama' => 'required|array|min:1',
            'nama.*' => 'required|string|max:100',
            'nim' => 'required|array|min:1',
            'nim.*' => 'required|string|max:20',
            'prodi' => 'required|array|min:1',
            'prodi.*' => 'required|string|max:100',
            'peran' => 'required|array|min:1',
            'peran.*' => 'required|in:Ketua,Anggota',
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'user_nim' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if file exists
            if (!$request->hasFile('file')) {
                throw new \Exception('No file uploaded in request');
            }

            // Upload file
            $file = $request->file('file');
            
            \Log::info('File details before upload', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'error' => $file->getError(),
                'real_path' => $file->getRealPath(),
                'is_valid' => $file->isValid()
            ]);

            // Create directory if not exists
            $storagePath = storage_path('app/public/kkn-files');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
                \Log::info('Created storage directory', ['path' => $storagePath]);
            }

            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('kkn-files', $fileName, 'public');

            \Log::info('File storage attempt', [
                'filename' => $fileName,
                'filepath' => $filePath,
                'storage_exists' => Storage::disk('public')->exists($filePath),
                'storage_size' => Storage::disk('public')->exists($filePath) ? Storage::disk('public')->size($filePath) : null
            ]);

            // Verify file was uploaded successfully
            if (!Storage::disk('public')->exists($filePath)) {
                throw new \Exception('File upload failed - file not found in storage after upload');
            }

            // Log successful upload
            \Log::info('File uploaded successfully', [
                'filename' => $fileName,
                'filepath' => $filePath,
                'size' => Storage::disk('public')->size($filePath),
                'url' => Storage::disk('public')->url($filePath)
            ]);

            // Simpan data pendaftar
            $pendaftar = KknPendaftar::create([
                'judul_kegiatan' => trim($request->judul_kegiatan),
                'mitra' => trim($request->mitra),
                'lokasi_mitra' => trim($request->lokasi_mitra),
                'file_path' => $filePath,
                'file_name' => $fileName,
                'status' => 'pending',
                'status_verifikasi' => 'pending',
                'user_nim' => trim($request->user_nim)
            ]);

            // Simpan data anggota
            $namaArray = $request->nama;
            $nimArray = $request->nim;
            $prodiArray = $request->prodi;
            $peranArray = $request->peran;

            for ($i = 0; $i < count($namaArray); $i++) {
                if (!empty($namaArray[$i]) && !empty($nimArray[$i]) && !empty($prodiArray[$i]) && !empty($peranArray[$i])) {
                    KknAnggota::create([
                        'kkn_pendaftar_id' => $pendaftar->id,
                        'nama' => trim($namaArray[$i]),
                        'nim' => trim($nimArray[$i]),
                        'program_studi' => trim($prodiArray[$i]),
                        'peran' => trim($peranArray[$i])
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran KKN berhasil disimpan!',
                'data' => $pendaftar->load('anggota')
            ], 201);

        } catch (\Exception $e) {
            // Hapus file jika ada error
            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil semua data pendaftar
     */
    #[OA\Get(
        path: '/api/kkn/pendaftar',
        tags: ['KKN'],
        summary: 'Daftar semua pendaftar KKN',
        description: 'Mengambil seluruh data pendaftar KKN beserta ringkasan anggota dan status verifikasi.',
        responses: [
            new OA\Response(response: 200, description: 'Daftar pendaftar berhasil diambil'),
            new OA\Response(response: 500, description: 'Terjadi kesalahan server'),
        ]
    )]
    public function index()
    {
        try {
            $pendaftar = KknPendaftar::with('anggota')
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform data untuk include informasi mahasiswa
            $transformedData = $pendaftar->map(function ($item) {
                $ketua = $item->anggota->where('peran', 'Ketua')->first();
                $anggotaList = $item->anggota->map(function ($anggota) {
                    return $anggota->nama . ' (' . $anggota->program_studi . ')';
                })->implode(', ');
                
                return [
                    'id' => $item->id,
                    'judul_kegiatan' => $item->judul_kegiatan,
                    'mitra' => $item->mitra,
                    'lokasi_mitra' => $item->lokasi_mitra,
                    'nama' => $ketua ? $ketua->nama : 'Data tidak tersedia',
                    'nim' => $ketua ? $ketua->nim : 'Data tidak tersedia',
                    'program_studi' => $ketua ? $ketua->program_studi : 'Data tidak tersedia',
                    'anggota' => $anggotaList,
                    'status' => $item->status,
                    'status_verifikasi' => $item->status_verifikasi,
                    'catatan' => $item->catatan_verifikasi,
                    'file_name' => $item->file_name,
                    'file_path' => $item->file_path,
                    'user_nim' => $item->user_nim,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ];
            });

            return response()->json($transformedData);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil data pendaftar berdasarkan NIM user
     */
    #[OA\Get(
        path: '/api/kkn/pendaftar/user/{userNim}',
        tags: ['KKN'],
        summary: 'Daftar pendaftar berdasarkan NIM user',
        description: 'Mengambil data pendaftaran KKN milik user tertentu berdasarkan NIM.',
        parameters: [
            new OA\Parameter(name: 'userNim', in: 'path', required: true, description: 'NIM user pendaftar', schema: new OA\Schema(type: 'string'), example: '2010001'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data pendaftar berhasil diambil'),
            new OA\Response(response: 500, description: 'Terjadi kesalahan server'),
        ]
    )]
    public function getByUserNim($userNim)
    {
        try {
            $pendaftar = KknPendaftar::with('anggota')
                ->where('user_nim', $userNim)
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform data untuk include informasi mahasiswa
            $transformedData = $pendaftar->map(function ($item) {
                $ketua = $item->anggota->where('peran', 'Ketua')->first();
                $anggotaList = $item->anggota->map(function ($anggota) {
                    return $anggota->nama . ' (' . $anggota->program_studi . ')';
                })->implode(', ');
                
                return [
                    'id' => $item->id,
                    'judul_kegiatan' => $item->judul_kegiatan,
                    'mitra' => $item->mitra,
                    'lokasi_mitra' => $item->lokasi_mitra,
                    'nama' => $ketua ? $ketua->nama : 'Data tidak tersedia',
                    'nim' => $ketua ? $ketua->nim : 'Data tidak tersedia',
                    'program_studi' => $ketua ? $ketua->program_studi : 'Data tidak tersedia',
                    'anggota' => $anggotaList,
                    'status' => $item->status,
                    'status_verifikasi' => $item->status_verifikasi,
                    'catatan' => $item->catatan_verifikasi,
                    'file_name' => $item->file_name,
                    'file_path' => $item->file_path,
                    'user_nim' => $item->user_nim,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ];
            });

            return response()->json($transformedData);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil detail pendaftar
     */
    #[OA\Get(
        path: '/api/kkn/pendaftar/{id}',
        tags: ['KKN'],
        summary: 'Detail pendaftar KKN',
        description: 'Mengambil detail satu pendaftar KKN beserta anggotanya berdasarkan ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID pendaftar', schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail pendaftar berhasil diambil'),
            new OA\Response(response: 404, description: 'Data tidak ditemukan'),
        ]
    )]
    public function show($id)
    {
        try {
            $pendaftar = KknPendaftar::with('anggota')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $pendaftar
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update status verifikasi
     */
    public function updateVerifikasi(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status_verifikasi' => 'required|in:diterima,ditolak,pending',
            'catatan_verifikasi' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pendaftar = KknPendaftar::findOrFail($id);
            
            $pendaftar->update([
                'status_verifikasi' => $request->status_verifikasi,
                'catatan_verifikasi' => $request->catatan_verifikasi,
                'tanggal_verifikasi' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status verifikasi berhasil diupdate!',
                'data' => $pendaftar
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


} 