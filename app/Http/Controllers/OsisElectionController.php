<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\OsisElection;
use App\Models\OsisCandidate;
use App\Models\OsisVoter;
use App\Models\OsisBallot;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;

class OsisElectionController extends Controller
{
    /**
     * Halaman Publik Kampanye Web Desktop (/osis)
     */
    public function publicIndex(Request $request, $slug = null)
    {
        $query = OsisElection::with(['school', 'candidates', 'academicYear'])
            ->where('is_active', true);

        if ($slug) {
            $election = $query->where('slug', $slug)->firstOrFail();
        } else {
            // Default ambil election aktif terbaru (prioritas status voting_open / upcoming)
            $election = $query->orderByRaw("CASE 
                    WHEN status = 'voting_open' THEN 1 
                    WHEN status = 'upcoming' THEN 2 
                    ELSE 3 END")
                ->latest()
                ->first();
        }

        $allElections = OsisElection::with('school')
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('osis.public_showcase', compact('election', 'allElections'));
    }

    /**
     * Halaman Publik Kampanye Mobile Web PWA (/m/osis)
     */
    public function publicMobileIndex(Request $request, $slug = null)
    {
        $query = OsisElection::with(['school', 'candidates', 'academicYear'])
            ->where('is_active', true);

        if ($slug) {
            $election = $query->where('slug', $slug)->firstOrFail();
        } else {
            $election = $query->orderByRaw("CASE 
                    WHEN status = 'voting_open' THEN 1 
                    WHEN status = 'upcoming' THEN 2 
                    ELSE 3 END")
                ->latest()
                ->first();
        }

        $allElections = OsisElection::with('school')
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('osis.mobile_showcase', compact('election', 'allElections'));
    }

    /**
     * Bilik Suara Interaktif Civitas (Siswa, Guru, Pegawai yang Login)
     * Rute: /pemilihan-osis
     */
    public function booth(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses bilik suara OSIS.');
        }

        // Cari event pemilihan yang sesuai dengan sekolah user
        $isYayasanOrAdmin = in_array($user->role, ['superadmin', 'yayasan', 'ketua_yayasan', 'admin_yayasan'])
            || (method_exists($user, 'isKetuaYayasan') && $user->isKetuaYayasan())
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin());

        // Prioritas unit aktif dari switcher session
        $userSchoolId = session('active_school_id') 
            ?? (method_exists($user, 'getActiveSchoolId') ? $user->getActiveSchoolId() : $user->school_id);

        // Jika user adalah siswa, pastikan school_id dari relasi student jika ada
        if ($user->student) {
            $userSchoolId = $user->student->school_id ?? $userSchoolId;
        }

        // Cek jika schoolId adalah tipe yayasan
        if ($userSchoolId) {
            $sch = School::find($userSchoolId);
            if ($sch && $sch->type === 'yayasan') {
                $userSchoolId = null;
            }
        }

        // Cari election aktif untuk unit sekolah user
        $election = OsisElection::with(['school', 'candidates', 'academicYear'])
            ->where('is_active', true)
            ->when($userSchoolId, function($q) use ($userSchoolId) {
                $q->where('school_id', $userSchoolId);
            })
            ->latest()
            ->first();

        // Fallback jika tidak ada event spesifik unit, ambil event aktif pertama (contoh SMK)
        if (!$election) {
            $election = OsisElection::with(['school', 'candidates', 'academicYear'])
                ->where('is_active', true)
                ->latest()
                ->first();
        }

        if (!$election) {
            return view('osis.booth_empty', [
                'message' => 'Belum ada agenda Pemilihan OSIS aktif untuk unit sekolah Anda saat ini.'
            ]);
        }

        // Cek status hak pilih user pada election ini
        $voter = OsisVoter::where('election_id', $election->id)
            ->where('user_id', $user->id)
            ->first();

        // Hitung total partisipasi (anonim)
        $totalVoters = $election->voters()->count();
        $totalVotes = $election->ballots()->count();
        $turnout = $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100, 1) : 0;

        return view('osis.booth', compact('election', 'voter', 'user', 'totalVoters', 'totalVotes', 'turnout'));
    }

    /**
     * Proses Pencoblosan (Voting Engine) - Transaksional, Atomik & Anonim (LUBER)
     */
    public function submitVote(Request $request)
    {
        $request->validate([
            'election_id' => 'required|exists:osis_elections,id',
            'candidate_id' => 'required|exists:osis_candidates,id',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login telah berakhir. Silakan login kembali.'
            ], 401);
        }

        $election = OsisElection::findOrFail($request->election_id);

        // 1. Validasi Status & Waktu Pemilihan
        if (!$election->isVotingOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Pemungutan suara saat ini sedang ditutup atau belum dibuka.'
            ], 422);
        }

        // 2. Validasi Kelayakan Pemilih (Voter Eligibility)
        $userRole = strtolower($user->role);
        $voterType = 'student';

        if (in_array($userRole, ['guru', 'teacher'])) {
            $voterType = 'teacher';
            if (!$election->allow_teachers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru tidak diikutsertakan dalam pemungutan suara ini.'
                ], 403);
            }
        } elseif (in_array($userRole, ['pegawai', 'staff'])) {
            $voterType = 'staff';
            if (!$election->allow_staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pegawai tidak diikutsertakan dalam pemungutan suara ini.'
                ], 403);
            }
        } elseif (in_array($userRole, ['admin', 'superadmin', 'yayasan'])) {
            $voterType = 'admin';
        } else {
            $voterType = 'student';
        }

        // 3. Verifikasi Kandidat termasuk dalam Pemilihan ini
        $candidate = OsisCandidate::where('id', $request->candidate_id)
            ->where('election_id', $election->id)
            ->first();

        if (!$candidate) {
            return response()->json([
                'success' => false,
                'message' => 'Pasangan calon yang dipilih tidak valid.'
            ], 422);
        }

        // 4. Eksekusi Atomik dengan Database Transaction & Pessimistic Locking
        try {
            $voteToken = null;

            DB::transaction(function () use ($election, $candidate, $user, $voterType, $request, &$voteToken) {
                // Lock record voter untuk mencegah double-vote bersamaan
                $voter = OsisVoter::where('election_id', $election->id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if ($voter && $voter->has_voted) {
                    throw new \Exception('Anda sudah memberikan suara sebelumnya! Setiap akun hanya dapat memilih satu kali.');
                }

                $voteToken = 'PEMBDA-VOTE-' . strtoupper(Str::random(8));

                if ($voter) {
                    $voter->update([
                        'has_voted' => true,
                        'voted_at' => Carbon::now(),
                        'vote_token' => $voteToken,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                } else {
                    OsisVoter::create([
                        'election_id' => $election->id,
                        'user_id' => $user->id,
                        'voter_type' => $voterType,
                        'has_voted' => true,
                        'voted_at' => Carbon::now(),
                        'vote_token' => $voteToken,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                }

                // Masukkan suara ke Kotak Suara Anonim (TANPA USER ID)
                OsisBallot::create([
                    'election_id' => $election->id,
                    'candidate_id' => $candidate->id,
                    'created_at' => Carbon::now(),
                ]);

                // Increment agregat suara kandidat
                $candidate->increment('vote_count');
            });

            return response()->json([
                'success' => true,
                'message' => 'Suara Anda berhasil dicatat secara sah dan rahasia!',
                'vote_token' => $voteToken,
                'candidate_number' => $candidate->candidate_number,
                'candidate_name' => $candidate->leader_name,
                'voted_at' => Carbon::now()->translatedFormat('d F Y - H:i:s') . ' WIB',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Halaman Real Count & Rekapitulasi Suara
     */
    public function realCount(Request $request, $slug = null)
    {
        $query = OsisElection::with(['school', 'candidates', 'academicYear'])
            ->where('is_active', true);

        if ($slug) {
            $election = $query->where('slug', $slug)->firstOrFail();
        } else {
            $election = $query->latest()->firstOrFail();
        }

        $user = Auth::user();
        $isAdmin = $user && in_array($user->role, ['superadmin', 'admin', 'yayasan']);

        // Jika mode blind vote aktif dan belum ditutup, serta bukan admin: sembunyikan perolehan paslon
        $hideResults = !$election->show_realtime_count && !$election->isClosed() && !$isAdmin;

        $totalVoters = $election->voters()->count();
        $totalVotes = $election->ballots()->count();
        $turnout = $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100, 1) : 0;

        // Pemenang jika selesai
        $winner = null;
        if ($election->isClosed() || $election->show_realtime_count || $isAdmin) {
            $winner = $election->candidates()->orderBy('vote_count', 'desc')->first();
        }

        return view('osis.real_count', compact('election', 'hideResults', 'totalVoters', 'totalVotes', 'turnout', 'winner'));
    }

    /**
     * Cetak Berita Acara Resmi Hasil Pemilihan (Print View / PDF Ready)
     */
    public function printReport(Request $request, $id)
    {
        $election = OsisElection::with(['school', 'candidates', 'academicYear'])->findOrFail($id);

        $totalVoters = $election->voters()->count();
        $totalVotes = $election->ballots()->count();
        $turnout = $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100, 1) : 0;
        $winner = $election->candidates()->orderBy('vote_count', 'desc')->first();

        return view('osis.report_print', compact('election', 'totalVoters', 'totalVotes', 'turnout', 'winner'));
    }
}
