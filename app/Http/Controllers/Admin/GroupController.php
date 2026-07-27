<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Group;

class GroupController extends Controller
{
    // Menampilkan detail kelompok (untuk halaman billing.blade.php)
    public function show($id)
    {
        $group = Group::findOrFail($id);
        $members = $group->members;
        return view('admin.special-pages.billing', compact('group', 'members'));

    }


    // Memverifikasi kelompok
    public function verify($id)
    {
        $group = Group::findOrFail($id);
        $group->is_verified = true;
        $group->save();

        return redirect()->back()->with('success', 'Kelompok berhasil diverifikasi.');
    }
}
