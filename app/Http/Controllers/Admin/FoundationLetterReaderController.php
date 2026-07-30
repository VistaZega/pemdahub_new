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

        return view('admin.letters.index', compact('letters', 'user', 'schoolId'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $schoolId = session('school_id', $user->school_id);

        $letter = FoundationLetter::where('status', 'published')->findOrFail($id);

        // Record read receipt
        FoundationLetterRead::firstOrCreate([
            'foundation_letter_id' => $letter->id,
            'school_id'            => $schoolId,
            'user_id'              => $user->id,
        ], [
            'read_at' => now(),
        ]);

        return view('admin.letters.show', compact('letter', 'user'));
    }

    public function print($id)
    {
        $letter = FoundationLetter::where('status', 'published')->findOrFail($id);
        return view('yayasan.letters.print', compact('letter'));
    }
}
