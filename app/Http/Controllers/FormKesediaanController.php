<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormKesediaan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DashboardHelper;

class FormKesediaanController extends Controller
{
    /**
     * Menampilkan form kesediaan
     */
    public function showForm()
    {
        return view('form-kesediaan', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Menyimpan data form kesediaan
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $identityNumber = $user->isDosen() ? ($user->nip ?? '') : ($user->nim ?? '');

        $request->validate([
            'judul_kegiatan' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf|max:10240' // 10MB max
        ], [
            'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
            'file.required' => 'File form kesediaan harus diupload',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 10MB'
        ]);

        try {
            // Merged app: store locally instead of POSTing to the dashboard API.
            $file = $request->file('file');

            app(\App\Services\DocumentStorageService::class)->storeFormKesediaan(
                (string) $request->judul_kegiatan,
                (string) $identityNumber,
                $file
            );

            return redirect()->back()->with('success', 'Form Kesediaan berhasil dikirim!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Cek status form kesediaan
     */
    public function status()
    {
        // Implementasi untuk cek status form kesediaan
        return view('form-kesediaan-status');
    }
}


