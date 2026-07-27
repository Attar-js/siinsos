<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PeerReview;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DashboardHelper;
use OpenApi\Attributes as OA;

class PeerReviewController extends Controller
{
    /**
     * Menampilkan halaman peer review
     */
    #[OA\Get(
        path: '/api/peer-review/list',
        tags: ['Peer Review'],
        summary: 'Daftar semua peer review',
        description: 'Mengambil seluruh data peer review beserta status verifikasinya.',
        responses: [
            new OA\Response(response: 200, description: 'Daftar peer review berhasil diambil'),
        ]
    )]
    public function index()
    {
        $peerReviews = PeerReview::orderBy('created_at', 'desc')->get();
        
        // Transform data untuk API
        $transformedData = $peerReviews->map(function ($item) {
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
        return view('admin.special-pages.peer-review', compact('peerReviews'));
    }

    /**
     * Redirect to dashboard with success message
     */
    public function redirectToDashboard($message = '')
    {
        $dashboardUrl = DashboardHelper::getDashboardUrl('special-pages/peer-review');
        
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
            'peer_review_url' => DashboardHelper::getPeerReviewUrl(),
            'file_manager_url' => DashboardHelper::getFileManagerUrl(),
            'app_name' => config('app.name'),
            'app_url' => config('app.url')
        ]);
    }

    /**
     * Menyimpan data peer review baru
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
            'file.required' => 'File peer review harus diupload',
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
            
            // Simpan file ke storage peer-review-files
            Storage::disk('public')->put('peer-review-files/' . $fileName, file_get_contents($file));
            
            // Simpan data ke database
            PeerReview::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $fileName,
                'file_path' => 'peer-review-files/' . $fileName,
                'user_nim' => $request->user_nim,
                'status' => 'pending',
                'catatan' => null,
            ]);

            return redirect()->route('admin.special-pages.peer-review')->with('success', 'Peer Review berhasil ditambahkan!');
        } catch (\Exception $e) {
            \Log::error('Error storing peer review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan peer review: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan detail peer review
     */
    #[OA\Get(
        path: '/api/peer-review/{id}',
        tags: ['Peer Review'],
        summary: 'Detail peer review',
        description: 'Mengambil detail satu peer review berdasarkan ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID peer review', schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail peer review berhasil diambil'),
            new OA\Response(response: 404, description: 'Data tidak ditemukan'),
        ]
    )]
    public function show($id)
    {
        try {
            $peerReview = PeerReview::findOrFail($id);
            return response()->json($peerReview);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update data peer review
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
            $peerReview = PeerReview::findOrFail($id);
            
            $peerReview->judul_kegiatan = $request->judul_kegiatan;
            $peerReview->user_nim = $request->user_nim;
            
            // Update file jika ada
            if ($request->hasFile('file')) {
                // Hapus file lama
                if ($peerReview->file_path && Storage::disk('public')->exists($peerReview->file_path)) {
                    Storage::disk('public')->delete($peerReview->file_path);
                }
                
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Simpan file baru
                Storage::disk('public')->put('peer-review-files/' . $fileName, file_get_contents($file));
                
                $peerReview->file_name = $fileName;
                $peerReview->file_path = 'peer-review-files/' . $fileName;
            }
            
            $peerReview->save();

            return redirect()->route('admin.special-pages.peer-review')->with('success', 'Peer Review berhasil diupdate!');
        } catch (\Exception $e) {
            \Log::error('Error updating peer review: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengupdate peer review: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Verifikasi peer review
     */
    #[OA\Put(
        path: '/api/peer-review/{id}/status',
        tags: ['Peer Review'],
        summary: 'Verifikasi / ubah status peer review',
        description: 'Mengubah status verifikasi peer review (pending, approved, rejected) beserta catatan opsional.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID peer review', schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'approved'),
                    new OA\Property(property: 'catatan', type: 'string', maxLength: 1000, nullable: true, example: 'Sudah sesuai'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Status peer review berhasil diperbarui'),
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
            $peerReview = PeerReview::findOrFail($id);
            $peerReview->status = $request->status;
            $peerReview->catatan = $request->catatan;
            $peerReview->save();

            if ($isApi) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status peer review berhasil diupdate!',
                    'data' => $peerReview
                ]);
            }
            return redirect()->route('admin.special-pages.peer-review')->with('success', 'Status peer review berhasil diupdate!');
        } catch (\Exception $e) {
            \Log::error('Error updating peer review status: ' . $e->getMessage());
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
     * Hapus data peer review
     */
    public function destroy($id)
    {
        try {
            $peerReview = PeerReview::findOrFail($id);
            
            // Hapus file dari storage
            if ($peerReview->file_path && Storage::disk('public')->exists($peerReview->file_path)) {
                Storage::disk('public')->delete($peerReview->file_path);
            }
            
            $peerReview->delete();

            return redirect()->route('admin.special-pages.peer-review')->with('success', 'Peer Review berhasil dihapus!');
        } catch (\Exception $e) {
            \Log::error('Error deleting peer review: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus peer review: ' . $e->getMessage());
        }
    }

    /**
     * API untuk menerima data dari project-akhir
     */
    #[OA\Post(
        path: '/api/peer-review/store-from-external',
        tags: ['Peer Review'],
        summary: 'Menerima peer review dari project-akhir',
        description: 'Menyimpan peer review beserta file (dikirim sebagai string base64). Endpoint ini juga tersedia sebagai alias di /api/peer-review/store.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['judul_kegiatan', 'user_nim', 'file_content', 'file_name', 'file_mime_type', 'file_size'],
                properties: [
                    new OA\Property(property: 'judul_kegiatan', type: 'string', maxLength: 255, example: 'KKN Tematik Desa Sukamaju'),
                    new OA\Property(property: 'user_nim', type: 'string', maxLength: 20, example: '2010001'),
                    new OA\Property(property: 'file_content', type: 'string', format: 'byte', description: 'Isi file PDF dalam base64'),
                    new OA\Property(property: 'file_name', type: 'string', maxLength: 255, example: 'peer-review.pdf'),
                    new OA\Property(property: 'file_mime_type', type: 'string', example: 'application/pdf'),
                    new OA\Property(property: 'file_size', type: 'integer', example: 204800),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Peer review berhasil disimpan'),
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
            Storage::disk('public')->put('peer-review-files/' . $request->file_name, $fileContent);
            
            // Simpan data ke database
            PeerReview::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $request->file_name,
                'file_path' => 'peer-review-files/' . $request->file_name,
                'user_nim' => $request->user_nim,
                'status' => 'pending',
                'catatan' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Peer Review berhasil disimpan'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error storing peer review from external: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
