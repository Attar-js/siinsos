<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\FormKesediaan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DashboardHelper;
use OpenApi\Attributes as OA;

class FormKesediaanController extends Controller
{
    /**
     * Menampilkan halaman form kesediaan
     */
    #[OA\Get(
        path: '/api/form-kesediaan/list',
        tags: ['Form Kesediaan'],
        summary: 'Daftar semua form kesediaan',
        description: 'Mengambil seluruh data form kesediaan dosen beserta status verifikasinya.',
        responses: [
            new OA\Response(response: 200, description: 'Daftar form kesediaan berhasil diambil'),
        ]
    )]
    public function index()
    {
        $formKesediaan = FormKesediaan::orderBy('created_at', 'desc')->get();
        
        // Transform data untuk API
        $transformedData = $formKesediaan->map(function ($item) {
            return [
                'id' => $item->id,
                'judul_kegiatan' => $item->judul_kegiatan,
                'user_nim' => $item->user_nim,
                'status' => $item->status,
                'catatan' => $item->catatan,
                'file_name' => $item->file_name,
                'file_path' => $item->file_path,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at
            ];
        });
        
        // Check if request wants JSON (API call)
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json($transformedData);
        }
        
        // Return view for web request
        return view('admin.special-pages.form-kesediaan', compact('formKesediaan'));
    }

    /**
     * Redirect to dashboard with success message
     */
    public function redirectToDashboard($message = '')
    {
        $dashboardUrl = DashboardHelper::getDashboardUrl('special-pages/form-kesediaan');
        
        if ($message) {
            return redirect($dashboardUrl)->with('success', $message);
        }
        
        return redirect($dashboardUrl);
    }

    /**
     * Get dashboard configuration
     */
    public function getDashboardConfig()
    {
        return response()->json([
            'dashboard_url' => DashboardHelper::getDashboardUrl(),
            'form_kesediaan_url' => DashboardHelper::getFormKesediaanUrl(),
            'file_manager_url' => DashboardHelper::getFileManagerUrl(),
            'app_name' => config('app.name'),
            'app_url' => config('app.url')
        ]);
    }

    /**
     * Menyimpan data form kesediaan baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'user_nim' => 'required|string|max:20',
            'file' => 'required|file|mimes:pdf|max:10240' // 10MB max
        ], [
            'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
            'user_nim.required' => 'NIM user input harus diisi',
            'file.required' => 'File form kesediaan harus diupload',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 10MB'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Simpan file ke storage form-kesediaan-files
            Storage::disk('public')->put('form-kesediaan-files/' . $fileName, file_get_contents($file));
            
            // Simpan data ke database
            FormKesediaan::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $fileName,
                'file_path' => 'form-kesediaan-files/' . $fileName,
                'user_nim' => $request->user_nim,
                'status' => 'pending',
                'catatan' => null,
            ]);

            return redirect()->route('admin.special-pages.form-kesediaan')->with('success', 'Form Kesediaan berhasil ditambahkan!');
        } catch (\Exception $e) {
            \Log::error('Error storing form kesediaan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan form kesediaan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan detail form kesediaan
     */
    #[OA\Get(
        path: '/api/form-kesediaan/{id}',
        tags: ['Form Kesediaan'],
        summary: 'Detail form kesediaan',
        description: 'Mengambil detail satu form kesediaan berdasarkan ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID form kesediaan', schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail form kesediaan berhasil diambil'),
            new OA\Response(response: 404, description: 'Data tidak ditemukan'),
        ]
    )]
    public function show($id)
    {
        try {
            $formKesediaan = FormKesediaan::findOrFail($id);
            return response()->json($formKesediaan);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update data form kesediaan
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'user_nim' => 'required|string|max:20',
            'file' => 'nullable|file|mimes:pdf|max:10240' // 10MB max
        ], [
            'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
            'user_nim.required' => 'NIM user input harus diisi',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 10MB'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $formKesediaan = FormKesediaan::findOrFail($id);
            
            $formKesediaan->judul_kegiatan = $request->judul_kegiatan;
            $formKesediaan->user_nim = $request->user_nim;
            
            // Update file jika ada
            if ($request->hasFile('file')) {
                // Hapus file lama
                if ($formKesediaan->file_path && Storage::disk('public')->exists($formKesediaan->file_path)) {
                    Storage::disk('public')->delete($formKesediaan->file_path);
                }
                
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Simpan file baru
                Storage::disk('public')->put('form-kesediaan-files/' . $fileName, file_get_contents($file));
                
                $formKesediaan->file_name = $fileName;
                $formKesediaan->file_path = 'form-kesediaan-files/' . $fileName;
            }
            
            $formKesediaan->save();

            return redirect()->route('admin.special-pages.form-kesediaan')->with('success', 'Form Kesediaan berhasil diupdate!');
        } catch (\Exception $e) {
            \Log::error('Error updating form kesediaan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengupdate form kesediaan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Verifikasi form kesediaan
     */
    #[OA\Put(
        path: '/api/form-kesediaan/{id}/status',
        tags: ['Form Kesediaan'],
        summary: 'Verifikasi / ubah status form kesediaan',
        description: 'Mengubah status verifikasi form kesediaan (pending, approved, rejected) beserta catatan opsional.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID form kesediaan', schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'approved'),
                    new OA\Property(property: 'catatan', type: 'string', maxLength: 1000, nullable: true, example: 'Disetujui'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Status form kesediaan berhasil diperbarui'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function verifikasi(Request $request, $id)
    {
        $isApi = $request->is('api/*') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected',
            'catatan' => 'nullable|string|max:1000'
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
            $formKesediaan = FormKesediaan::findOrFail($id);
            $formKesediaan->status = $request->status;
            $formKesediaan->catatan = $request->catatan;
            $formKesediaan->save();

            if ($isApi) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status form kesediaan berhasil diupdate!',
                    'data' => $formKesediaan
                ]);
            }
            return redirect()->route('admin.special-pages.form-kesediaan')->with('success', 'Status form kesediaan berhasil diupdate!');
        } catch (\Exception $e) {
            \Log::error('Error updating form kesediaan status: ' . $e->getMessage());
            if ($isApi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengupdate status: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate status: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data form kesediaan
     */
    public function destroy($id)
    {
        try {
            $formKesediaan = FormKesediaan::findOrFail($id);
            
            // Hapus file dari storage
            if ($formKesediaan->file_path && Storage::disk('public')->exists($formKesediaan->file_path)) {
                Storage::disk('public')->delete($formKesediaan->file_path);
            }
            
            $formKesediaan->delete();

            return redirect()->route('admin.special-pages.form-kesediaan')->with('success', 'Form Kesediaan berhasil dihapus!');
        } catch (\Exception $e) {
            \Log::error('Error deleting form kesediaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus form kesediaan: ' . $e->getMessage());
        }
    }

    /**
     * API untuk menerima data dari project-akhir
     */
    #[OA\Post(
        path: '/api/form-kesediaan/store-from-external',
        tags: ['Form Kesediaan'],
        summary: 'Menerima form kesediaan dari project-akhir',
        description: 'Menyimpan form kesediaan dosen beserta file (dikirim sebagai string base64). Endpoint ini juga tersedia sebagai alias di /api/form-kesediaan/store.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['judul_kegiatan', 'user_nim', 'file_content', 'file_name', 'file_mime_type', 'file_size'],
                properties: [
                    new OA\Property(property: 'judul_kegiatan', type: 'string', maxLength: 255, example: 'KKN Tematik Desa Sukamaju'),
                    new OA\Property(property: 'user_nim', type: 'string', maxLength: 20, example: '2010001'),
                    new OA\Property(property: 'file_content', type: 'string', format: 'byte', description: 'Isi file PDF dalam base64'),
                    new OA\Property(property: 'file_name', type: 'string', maxLength: 255, example: 'form-kesediaan.pdf'),
                    new OA\Property(property: 'file_mime_type', type: 'string', example: 'application/pdf'),
                    new OA\Property(property: 'file_size', type: 'integer', example: 204800),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Form kesediaan berhasil disimpan'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
            new OA\Response(response: 500, description: 'Terjadi kesalahan server'),
        ]
    )]
    public function storeFromExternal(Request $request)
    {
        try {
            $request->validate([
                'judul_kegiatan' => 'required|string|max:255',
                'user_nim' => 'required|string|max:20',
                'file_content' => 'required|string',
                'file_name' => 'required|string|max:255',
                'file_mime_type' => 'required|string',
                'file_size' => 'required|integer'
            ]);

            // Decode base64 file content
            $fileContent = base64_decode($request->file_content);
            
            // Simpan file ke storage
            Storage::disk('public')->put('form-kesediaan-files/' . $request->file_name, $fileContent);
            
            // Simpan data ke database
            FormKesediaan::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $request->file_name,
                'file_path' => 'form-kesediaan-files/' . $request->file_name,
                'user_nim' => $request->user_nim,
                'status' => 'pending',
                'catatan' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Form Kesediaan berhasil disimpan'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error storing form kesediaan from external: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
