<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Luaran;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class LuaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/luaran/list',
        tags: ['Luaran'],
        summary: 'Daftar semua luaran',
        description: 'Mengambil seluruh data luaran (artikel, video aftermovie, link) beserta statusnya.',
        responses: [
            new OA\Response(response: 200, description: 'Daftar luaran berhasil diambil'),
        ]
    )]
    public function index()
    {
        $luaran = Luaran::orderBy('created_at', 'desc')->get();
        
        // Transform data untuk API
        $transformedData = $luaran->map(function ($item) {
            return [
                'id' => $item->id,
                'judul_kegiatan' => $item->judul_kegiatan,
                'user_nim' => $item->user_nim,
                'status' => $item->status,
                'catatan' => $item->catatan,
                'artikel_file_name' => $item->artikel_file_name, // Perbaiki field name
                'artikel_file_path' => $item->artikel_file_path,
                'video_aftermovie' => $item->video_aftermovie,
                'artikel_link' => $item->artikel_link,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at
            ];
        });
        
        // Check if request wants JSON (API call)
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json($transformedData);
        }
        
        // Return view for web request
        return view('admin.special-pages.luaran', compact('luaran'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.special-pages.luaran');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul_kegiatan' => 'required|string|max:255',
            'video_aftermovie' => 'required|url|max:255',
            'artikel_link' => 'required|url|max:255',
            'artikel_file' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'user_nim' => 'required|string|max:20',
        ], [
            'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
            'video_aftermovie.required' => 'Link video aftermovie harus diisi',
            'video_aftermovie.url' => 'Link video aftermovie harus berupa URL yang valid',
            'artikel_link.required' => 'Link artikel harus diisi',
            'artikel_link.url' => 'Link artikel harus berupa URL yang valid',
            'artikel_file.required' => 'File artikel harus diupload',
            'artikel_file.mimes' => 'File artikel harus berformat PDF, DOC, atau DOCX',
            'artikel_file.max' => 'Ukuran file artikel maksimal 10MB',
            'user_nim.required' => 'NIM user input harus diisi',
        ]);

        try {
            $file = $request->file('artikel_file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Simpan file ke storage
            $filePath = $file->storeAs('luaran-files', $fileName, 'public');

            // Simpan data luaran
            $luaran = Luaran::create([
                'judul_kegiatan' => trim($request->judul_kegiatan),
                'video_aftermovie' => trim($request->video_aftermovie),
                'artikel_link' => trim($request->artikel_link),
                'artikel_file_path' => $filePath,
                'artikel_file_name' => $fileName,
                'status' => 'pending',
                'user_nim' => $request->user_nim ?? auth()->user()->nim ?? null,
            ]);

            return redirect()->back()->with('success', 'Luaran berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan luaran: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/luaran/{id}',
        tags: ['Luaran'],
        summary: 'Detail luaran',
        description: 'Mengambil detail satu luaran berdasarkan ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID luaran', schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail luaran berhasil diambil'),
            new OA\Response(response: 404, description: 'Data tidak ditemukan'),
        ]
    )]
    public function show(string $id)
    {
        try {
            $luaran = Luaran::findOrFail($id);

            if (request()->is('api/*') || request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $luaran
                ]);
            }

            return view('admin.special-pages.luaran', compact('luaran'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $luaran = Luaran::findOrFail($id);
        return view('admin.special-pages.luaran', compact('luaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $luaran = Luaran::findOrFail($id);

        $request->validate([
            'judul_kegiatan' => 'required|string|max:255',
            'video_aftermovie' => 'required|url|max:255',
            'artikel_link' => 'required|url|max:255',
            'artikel_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'user_nim' => 'required|string|max:20',
        ], [
            'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
            'video_aftermovie.required' => 'Link video aftermovie harus diisi',
            'video_aftermovie.url' => 'Link video aftermovie harus berupa URL yang valid',
            'artikel_link.required' => 'Link artikel harus diisi',
            'artikel_link.url' => 'Link artikel harus berupa URL yang valid',
            'artikel_file.mimes' => 'File artikel harus berformat PDF, DOC, atau DOCX',
            'artikel_file.max' => 'Ukuran file artikel maksimal 10MB',
            'user_nim.required' => 'NIM user input harus diisi',
        ]);

        try {
            $data = [
                'judul_kegiatan' => trim($request->judul_kegiatan),
                'video_aftermovie' => trim($request->video_aftermovie),
                'artikel_link' => trim($request->artikel_link),
                'user_nim' => $request->user_nim ?? auth()->user()->nim ?? null,
            ];

            // Jika ada file baru
            if ($request->hasFile('artikel_file')) {
                $file = $request->file('artikel_file');
                $fileName = time() . '_' . $file->getClientOriginalName();

                // Hapus file lama jika ada
                if ($luaran->artikel_file_path && Storage::disk('public')->exists($luaran->artikel_file_path)) {
                    Storage::disk('public')->delete($luaran->artikel_file_path);
                }

                // Simpan file baru
                $filePath = $file->storeAs('luaran-files', $fileName, 'public');
                $data['artikel_file_path'] = $filePath;
                $data['artikel_file_name'] = $fileName;
            }

            $luaran->update($data);

            return redirect()->back()->with('success', 'Luaran berhasil diupdate!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate luaran: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $luaran = Luaran::findOrFail($id);

            // Hapus file jika ada
            if ($luaran->artikel_file_path && Storage::disk('public')->exists($luaran->artikel_file_path)) {
                Storage::disk('public')->delete($luaran->artikel_file_path);
            }

            $luaran->delete();

            return redirect()->back()->with('success', 'Luaran berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus luaran: ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi luaran
     */
    #[OA\Put(
        path: '/api/luaran/{id}/status',
        tags: ['Luaran'],
        summary: 'Verifikasi / ubah status luaran',
        description: 'Mengubah status verifikasi luaran (pending, approved, rejected) beserta catatan opsional.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID luaran', schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'approved'),
                    new OA\Property(property: 'catatan', type: 'string', maxLength: 1000, nullable: true, example: 'Luaran sudah sesuai'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Status luaran berhasil diperbarui'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function verifikasi(Request $request, string $id)
    {
        $isApi = $request->is('api/*') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected',
            'catatan' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($isApi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $luaran = Luaran::findOrFail($id);
            $luaran->update([
                'status' => $request->status,
                'catatan' => $request->catatan,
            ]);

            $statusText = $request->status == 'approved' ? 'disetujui' : ($request->status == 'rejected' ? 'ditolak' : 'pending');

            if ($isApi) {
                return response()->json([
                    'success' => true,
                    'message' => "Luaran berhasil diverifikasi dengan status: {$statusText}",
                    'data' => $luaran
                ]);
            }
            return redirect()->back()->with('success', "Luaran berhasil diverifikasi dengan status: {$statusText}");
        } catch (\Exception $e) {
            if ($isApi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat verifikasi luaran: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan saat verifikasi luaran: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan data luaran dari external (project-akhir)
     */
    #[OA\Post(
        path: '/api/luaran/store-from-external',
        tags: ['Luaran'],
        summary: 'Menerima luaran dari project-akhir',
        description: 'Menyimpan luaran berupa file artikel (PDF/DOC/DOCX), link video aftermovie, dan link artikel. Endpoint ini juga tersedia sebagai alias di /api/luaran/store.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['judul_kegiatan', 'video_aftermovie', 'artikel_link', 'artikel_file', 'user_nim'],
                    properties: [
                        new OA\Property(property: 'judul_kegiatan', type: 'string', maxLength: 255, example: 'KKN Tematik Desa Sukamaju'),
                        new OA\Property(property: 'video_aftermovie', type: 'string', format: 'uri', example: 'https://youtube.com/watch?v=xxxx'),
                        new OA\Property(property: 'artikel_link', type: 'string', format: 'uri', example: 'https://media.com/artikel-kkn'),
                        new OA\Property(property: 'artikel_file', type: 'string', format: 'binary', description: 'File artikel PDF/DOC/DOCX (maks 10MB)'),
                        new OA\Property(property: 'user_nim', type: 'string', maxLength: 20, example: '2010001'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Luaran berhasil disimpan'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
            new OA\Response(response: 500, description: 'Terjadi kesalahan server'),
        ]
    )]
    public function storeFromExternal(Request $request)
    {
        $request->validate([
            'judul_kegiatan' => 'required|string|max:255',
            'video_aftermovie' => 'required|url|max:255',
            'artikel_link' => 'required|url|max:255',
            'artikel_file' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'user_nim' => 'required|string|max:20',
        ], [
            'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
            'video_aftermovie.required' => 'Link video aftermovie harus diisi',
            'video_aftermovie.url' => 'Link video aftermovie harus berupa URL yang valid',
            'artikel_link.required' => 'Link artikel harus diisi',
            'artikel_link.url' => 'Link artikel harus berupa URL yang valid',
            'artikel_file.required' => 'File artikel harus diupload',
            'artikel_file.mimes' => 'File artikel harus berformat PDF, DOC, atau DOCX',
            'artikel_file.max' => 'Ukuran file artikel maksimal 10MB',
            'user_nim.required' => 'NIM user input harus diisi',
        ]);

        try {
            $file = $request->file('artikel_file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Simpan file ke storage
            $filePath = $file->storeAs('luaran-files', $fileName, 'public');

            // Simpan data luaran
            $luaran = Luaran::create([
                'judul_kegiatan' => trim($request->judul_kegiatan),
                'video_aftermovie' => trim($request->video_aftermovie),
                'artikel_link' => trim($request->artikel_link),
                'artikel_file_path' => $filePath,
                'artikel_file_name' => $fileName,
                'status' => 'pending',
                'user_nim' => trim($request->user_nim),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Luaran berhasil disimpan!',
                'data' => $luaran
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
} 