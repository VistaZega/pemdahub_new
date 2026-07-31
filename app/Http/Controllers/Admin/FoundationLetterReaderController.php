<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundationLetter;
use App\Models\FoundationLetterRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FoundationLetterReaderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $schoolId = session('school_id', $user->school_id);

        $letters = FoundationLetter::where('status', 'published')
            ->forUser($user)
            ->orderBy('effective_date', 'desc')
            ->paginate(10);

        $layout = $user->layout;

        return view('admin.letters.index', compact('letters', 'user', 'schoolId', 'layout'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $schoolId = session('school_id', $user->school_id);

        $letter = FoundationLetter::where('status', 'published')->findOrFail($id);

        // Verifikasi apakah pengguna berhak membaca surat ini berdasarkan sasaran/tujuan surat
        if (!$user->isSuperAdmin() && $user->role !== 'ketua_yayasan') {
            $canAccess = FoundationLetter::where('id', $letter->id)->forUser($user)->exists();
            if (!$canAccess) {
                abort(403, 'Akses Ditolak: Surat edaran ini tidak ditujukan untuk peran/jabatan Anda.');
            }
        }

        // Record read receipt
        FoundationLetterRead::firstOrCreate([
            'foundation_letter_id' => $letter->id,
            'school_id'            => $schoolId,
            'user_id'              => $user->id,
        ], [
            'read_at' => now(),
        ]);

        $layout = $user->layout;

        return view('admin.letters.show', compact('letter', 'user', 'layout'));
    }

    public function print($id)
    {
        $letter = FoundationLetter::where('status', 'published')->findOrFail($id);
        return view('yayasan.letters.print', compact('letter'));
    }
}
