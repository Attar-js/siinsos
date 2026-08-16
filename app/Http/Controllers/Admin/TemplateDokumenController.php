<?php

namespace App\Http\Controllers\Admin;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class TemplateDokumenController extends Controller
{
    public function index()
    {
        $templates = DocumentTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('admin.template-dokumen.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'download_url' => 'required|url|max:2000',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ], [
            'title.required' => 'Judul template harus diisi.',
            'download_url.required' => 'Link unduh harus diisi.',
            'download_url.url' => 'Link unduh harus berupa URL yang valid.',
        ]);

        DocumentTemplate::create([
            'key' => DocumentTemplate::makeUniqueKey($validated['title']),
            'title' => $validated['title'],
            'download_url' => $validated['download_url'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.template-dokumen.index')
            ->with('success', 'Template berhasil ditambahkan.');
    }

    public function update(Request $request, DocumentTemplate $template)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'download_url' => 'required|url|max:2000',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ], [
            'title.required' => 'Judul template harus diisi.',
            'download_url.required' => 'Link unduh harus diisi.',
            'download_url.url' => 'Link unduh harus berupa URL yang valid.',
        ]);

        $template->update([
            'title' => $validated['title'],
            'download_url' => $validated['download_url'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? $template->sort_order,
        ]);

        return redirect()
            ->route('admin.template-dokumen.index')
            ->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(DocumentTemplate $template)
    {
        $title = $template->title;
        $template->delete();

        return redirect()
            ->route('admin.template-dokumen.index')
            ->with('success', 'Template "' . $title . '" berhasil dihapus.');
    }
}
