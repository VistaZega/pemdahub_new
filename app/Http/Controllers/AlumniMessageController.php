<?php

namespace App\Http\Controllers;

use App\Models\AlumniMessage;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;

class AlumniMessageController extends Controller
{
    private function resolveSchoolId(): ?int
    {
        $user = auth()->user();
        return $user->alumniDirectory?->school_id 
            ?? $user->school_id 
            ?? $user->student?->school_id 
            ?? $user->alumniProfile?->school_id;
    }

    public function index()
    {
        $user = auth()->user();
        $schoolId = $this->resolveSchoolId();

        // Jika superadmin / yayasan / owner
        if ($user->canAccessAllSchools()) {
            $alumnis = User::whereHas('alumniDirectory')
                ->where('id', '!=', $user->id)
                ->when($schoolId, fn($q) => $q->whereHas('alumniDirectory', fn($sq) => $sq->where('school_id', $schoolId)))
                ->get();

            // Jika masih kosong untuk unit spesifik, tampilkan seluruh alumni terdaftar
            if ($alumnis->isEmpty()) {
                $alumnis = User::whereHas('alumniDirectory')
                    ->where('id', '!=', $user->id)
                    ->get();
            }

            return view('alumni.chat.index', compact('alumnis'));
        }

        if (!$schoolId) {
            $schoolId = School::where('type', '!=', 'yayasan')->first()?->id;
        }

        // Ambil daftar alumni di sekolah yang sama, kecuali diri sendiri
        $alumnis = User::whereHas('alumniDirectory', function($q) use ($schoolId) {
            if ($schoolId) {
                $q->where('school_id', $schoolId);
            }
        })->where('id', '!=', $user->id)->get();

        return view('alumni.chat.index', compact('alumnis'));
    }

    public function show(User $contact)
    {
        $user = auth()->user();
        $schoolId = $this->resolveSchoolId();
        $contactSchoolId = $contact->alumniDirectory?->school_id ?? $contact->school_id;

        if (!$user->canAccessAllSchools() && $schoolId && $contactSchoolId && $schoolId !== $contactSchoolId) {
            abort(403, 'Anda hanya dapat mengirim pesan ke alumni dari unit sekolah yang sama.');
        }

        // Mark as read
        AlumniMessage::where('sender_id', $contact->id)
            ->where('receiver_id', $user->id)
            ->update(['is_read' => true]);

        $messages = AlumniMessage::where(function($q) use ($user, $contact) {
            $q->where('sender_id', $user->id)->where('receiver_id', $contact->id);
        })->orWhere(function($q) use ($user, $contact) {
            $q->where('sender_id', $contact->id)->where('receiver_id', $user->id);
        })->orderBy('created_at', 'asc')->get();

        // Pass to layout
        if ($user->canAccessAllSchools()) {
            $alumnis = User::whereHas('alumniDirectory')
                ->where('id', '!=', $user->id)
                ->when($schoolId, fn($q) => $q->whereHas('alumniDirectory', fn($sq) => $sq->where('school_id', $schoolId)))
                ->get();

            if ($alumnis->isEmpty()) {
                $alumnis = User::whereHas('alumniDirectory')->where('id', '!=', $user->id)->get();
            }
        } else {
            $alumnis = User::whereHas('alumniDirectory', function($q) use ($schoolId) {
                if ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            })->where('id', '!=', $user->id)->get();
        }

        return view('alumni.chat.index', compact('contact', 'messages', 'alumnis'));
    }

    public function store(Request $request, User $contact)
    {
        $request->validate(['message' => 'required|string']);

        $user = auth()->user();
        $schoolId = $this->resolveSchoolId();
        $contactSchoolId = $contact->alumniDirectory?->school_id ?? $contact->school_id;

        if (!$user->canAccessAllSchools() && $schoolId && $contactSchoolId && $schoolId !== $contactSchoolId) {
            abort(403, 'Akses ditolak.');
        }

        AlumniMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => $contact->id,
            'message' => $request->message,
        ]);

        return back();
    }
}

