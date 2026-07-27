<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Proposal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DashboardHelper;
use OpenApi\Attributes as OA;

class ProposalController extends Controller
{
    /**
     * Menampilkan halaman proposal
     */
    #[OA\Get(
        path: '/api/proposal/list',
        tags: ['Proposal'],
        summary: 'Daftar semua proposal',
        description: 'Mengambil seluruh data proposal beserta status verifikasinya.',
        responses: [
            new OA\Response(response: 200, description: 'Daftar proposal berhasil diambil'),
        ]
    )]
    public function index()
    {
        $proposals = Proposal::orderBy('created_at', 'desc')->get();
        
        // Transform data untuk API
        $transformedData = $proposals->map(function ($item) {
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
        return view('admin.special-pages.proposal', compact('proposals'));
    }

    /**
     * Redirect to dashboard with success message
     */
    public function redirectToDashboard($message = '')
    {
        $dashboardUrl = DashboardHelper::getDashboardUrl('special-pages/proposal');
        
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
            'proposal_url' => DashboardHelper::getProposalUrl(),
            'file_manager_url' => DashboardHelper::getFileManagerUrl(),
            'app_name' => config('app.name'),
            'app_url' => config('app.url')
        ]);
    }

    /**
     * Menyimpan data proposal baru
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
            'file.required' => 'File proposal harus diupload',
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
            
            // Simpan file ke storage proposal-files
            Storage::disk('public')->put('proposal-files/' . $fileName, file_get_contents($file));
            
            // Simpan data ke database
            Proposal::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $fileName,
                'file_path' => 'proposal-files/' . $fileName,
                'user_nim' => $request->user_nim,
                'status' => 'pending'
            ]);

            return redirect()->back()->with('success', 'Proposal berhasil ditambahkan!');
        } catch (\Exception $e) {
            \Log::error('Error storing proposal: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan proposal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan detail proposal
     */
    #[OA\Get(
        path: '/api/proposal/{id}',
        tags: ['Proposal'],
        summary: 'Detail proposal',
        description: 'Mengambil detail satu proposal berdasarkan ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID proposal', schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail proposal berhasil diambil'),
            new OA\Response(response: 404, description: 'Data tidak ditemukan'),
        ]
    )]
    public function show($id)
    {
        try {
            $proposal = Proposal::findOrFail($id);

            if (request()->is('api/*') || request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $proposal
                ]);
            }

            return view('admin.special-pages.proposal', compact('proposal'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update data proposal
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'user_nim' => 'required|string|max:20',
            'file' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $proposal = Proposal::findOrFail($id);
            $proposal->judul_kegiatan = $request->judul_kegiatan;
            $proposal->user_nim = $request->user_nim;

            if ($request->hasFile('file')) {
                // Hapus file lama jika ada
                if ($proposal->file_name) {
                    Storage::disk('public')->delete('proposal-files/' . $proposal->file_name);
                }
                
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                Storage::disk('public')->put('proposal-files/' . $fileName, file_get_contents($file));
                $proposal->file_name = $fileName;
            }

            $proposal->save();
            return redirect()->back()->with('success', 'Proposal berhasil diupdate!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate proposal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Verifikasi proposal
     */
    public function verifikasi(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected',
            'catatan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $proposal = Proposal::findOrFail($id);
            $proposal->status = $request->status;
            $proposal->catatan = $request->catatan;
            $proposal->save();

            $statusText = $request->status == 'approved' ? 'disetujui' : 
                         ($request->status == 'rejected' ? 'ditolak' : 'pending');

            return redirect()->back()->with('success', "Proposal berhasil $statusText!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate status proposal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hapus proposal
     */
    public function destroy($id)
    {
        try {
            $proposal = Proposal::findOrFail($id);
            
            // Hapus file dari storage
            if ($proposal->file_name) {
                Storage::disk('public')->delete('proposal-files/' . $proposal->file_name);
            }
            
            $proposal->delete();
            
            // Redirect ke dashboard dengan pesan sukses
            return redirect()->route('admin.special-pages.proposal')->with('success', 'Proposal berhasil dihapus!');
            
        } catch (\Exception $e) {
            return redirect()->route('admin.special-pages.proposal')->with('error', 'Gagal menghapus proposal: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan data proposal dari external (project-akhir)
     */
    #[OA\Post(
        path: '/api/proposal/store-from-external',
        tags: ['Proposal'],
        summary: 'Menerima proposal dari project-akhir',
        description: 'Menyimpan proposal beserta file (dikirim sebagai string base64). Endpoint ini juga tersedia sebagai alias di /api/proposal/store.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['judul_kegiatan', 'user_nim', 'file_content', 'file_name', 'file_mime_type', 'file_size'],
                properties: [
                    new OA\Property(property: 'judul_kegiatan', type: 'string', maxLength: 255, example: 'KKN Tematik Desa Sukamaju'),
                    new OA\Property(property: 'user_nim', type: 'string', maxLength: 20, example: '2010001'),
                    new OA\Property(property: 'file_content', type: 'string', format: 'byte', description: 'Isi file PDF dalam base64'),
                    new OA\Property(property: 'file_name', type: 'string', example: 'proposal.pdf'),
                    new OA\Property(property: 'file_mime_type', type: 'string', example: 'application/pdf'),
                    new OA\Property(property: 'file_size', type: 'integer', example: 204800),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Proposal berhasil disimpan'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
            new OA\Response(response: 500, description: 'Terjadi kesalahan server'),
        ]
    )]
    public function storeFromExternal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'user_nim' => 'required|string|max:20',
            'file_content' => 'required|string',
            'file_name' => 'required|string',
            'file_mime_type' => 'required|string',
            'file_size' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Decode base64 content
            $fileContent = base64_decode($request->file_content);
            
            // Create directory if not exists
            $storagePath = storage_path('app/public/proposal-files');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            // Save file
            $filePath = 'proposal-files/' . $request->file_name;
            Storage::disk('public')->put($filePath, $fileContent);

            // Save to database
            $proposal = Proposal::create([
                'judul_kegiatan' => $request->judul_kegiatan,
                'file_name' => $request->file_name,
                'file_path' => $filePath,
                'user_nim' => $request->user_nim,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proposal berhasil disimpan!',
                'data' => $proposal
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
