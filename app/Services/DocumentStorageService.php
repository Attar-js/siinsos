<?php

namespace App\Services;

use App\Models\FormKesediaan;
use App\Models\LaporanAkhir;
use App\Models\Luaran;
use App\Models\PeerReview;
use App\Models\Proposal;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Centralised, in-app document storage.
 *
 * Replaces the previous HTTP API distribution to the separate dashboard app.
 * Files are written to the local "public" disk (storage/app/public/<type>-files)
 * and metadata rows are created directly via Eloquent on the shared database.
 * This mirrors exactly what the old dashboard "store-from-external" endpoints did.
 */
class DocumentStorageService
{
    public function storeProposal(string $judulKegiatan, string $userNim, UploadedFile $file, ?string $fileName = null): Proposal
    {
        $fileName = $fileName ?: (time() . '_proposal_' . $file->getClientOriginalName());
        $filePath = 'proposal-files/' . $fileName;
        Storage::disk('public')->put($filePath, file_get_contents($file));

        return Proposal::create([
            'judul_kegiatan' => $judulKegiatan,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'user_nim' => $userNim,
            'status' => 'pending',
        ]);
    }

    public function storeLaporanAkhir(string $judulKegiatan, string $userNim, UploadedFile $file, ?string $fileName = null): LaporanAkhir
    {
        $fileName = $fileName ?: (time() . '_' . $file->getClientOriginalName());
        $filePath = 'laporan-akhir-files/' . $fileName;
        $content = file_get_contents($file);
        Storage::disk('public')->put($filePath, $content);

        return LaporanAkhir::create([
            'judul_kegiatan' => $judulKegiatan,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_content' => base64_encode($content),
            'file_mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'user_nim' => $userNim,
            'status' => 'pending',
        ]);
    }

    public function storeLuaran(string $judulKegiatan, string $userNim, string $videoAftermovie, string $artikelLink, ?UploadedFile $artikelFile = null): Luaran
    {
        $data = [
            'judul_kegiatan' => trim($judulKegiatan),
            'video_aftermovie' => trim($videoAftermovie),
            'artikel_link' => trim($artikelLink),
            'status' => 'pending',
            'user_nim' => trim($userNim),
        ];

        if ($artikelFile) {
            $fileName = time() . '_luaran_' . $artikelFile->getClientOriginalName();
            $data['artikel_file_path'] = $artikelFile->storeAs('luaran-files', $fileName, 'public');
            $data['artikel_file_name'] = $fileName;
        }

        return Luaran::create($data);
    }

    public function storeFormKesediaan(string $judulKegiatan, string $userNim, UploadedFile $file, ?string $fileName = null): FormKesediaan
    {
        $fileName = $fileName ?: (time() . '_kesediaan_' . $file->getClientOriginalName());
        $filePath = 'form-kesediaan-files/' . $fileName;
        Storage::disk('public')->put($filePath, file_get_contents($file));

        return FormKesediaan::create([
            'judul_kegiatan' => $judulKegiatan,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'user_nim' => $userNim,
            'status' => 'pending',
            'catatan' => null,
        ]);
    }

    public function storePeerReview(string $userNim, UploadedFile $file, ?string $judulKegiatan = null, ?string $fileName = null): PeerReview
    {
        $fileName = $fileName ?: (time() . '_peer_' . $file->getClientOriginalName());
        $filePath = 'peer-review-files/' . $fileName;
        Storage::disk('public')->put($filePath, file_get_contents($file));

        return PeerReview::create([
            'judul_kegiatan' => $judulKegiatan,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'user_nim' => $userNim,
            'status' => 'pending',
        ]);
    }

    /**
     * Generic dispatcher used by the "upload ulang" (re-upload) flow, keyed by
     * the same document-type segments the old API used.
     */
    public function storeByType(string $type, string $judulKegiatan, string $userNim, UploadedFile $file)
    {
        return match ($type) {
            'proposal' => $this->storeProposal($judulKegiatan, $userNim, $file),
            'laporan-akhir' => $this->storeLaporanAkhir($judulKegiatan, $userNim, $file),
            'form-kesediaan' => $this->storeFormKesediaan($judulKegiatan, $userNim, $file),
            'peer-review' => $this->storePeerReview($userNim, $file, $judulKegiatan),
            'luaran' => $this->storeLuaran($judulKegiatan, $userNim, '', '', $file),
            default => throw new \InvalidArgumentException('Tipe dokumen tidak dikenal: ' . $type),
        };
    }
}
