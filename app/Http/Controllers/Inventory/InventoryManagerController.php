<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryManager;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;

class InventoryManagerController extends Controller
{
    /**
     * Pastikan hanya Yayasan & Super Admin yang berhak mengakses menu penunjukan akun
     */
    protected function authorizeAccess()
    {
        $user = auth()->user();
        if (!$user->canDesignateInventoryManagers()) {
            abort(403, 'Akses Ditolak: Hanya Pengurus Yayasan dan Super Admin yang berhak menunjuk pengelola aset.');
        }
    }

    /**
     * Daftar Akun yang Ditunjuk oleh Yayasan
     */
    public function index()
    {
        $this->authorizeAccess();

        $managers = InventoryManager::with(['user.school', 'school', 'appointedBy'])
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $schools = School::where('is_active', true)->orderBy('type')->get();

        // Cari calon pengguna yang belum ditunjuk
        $assignedUserIds = $managers->pluck('user_id')->toArray();
        $availableUsers = User::whereNotIn('id', $assignedUserIds)
            ->whereIn('role', ['guru', 'pegawai', 'admin_sekolah', 'bendahara'])
            ->where('is_active', true)
            ->with('school')
            ->orderBy('name')
            ->get();

        $layout = auth()->user()->layout;

        return view('inventory.managers.index', compact('managers', 'schools', 'availableUsers', 'layout'));
    }

    /**
     * Simpan Penunjukan Akun Baru
     */
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'school_id' => 'nullable',
            'role_title' => 'required|string|max:100',
            'can_create' => 'nullable|boolean',
            'can_edit' => 'nullable|boolean',
            'can_delete' => 'nullable|boolean',
            'can_export' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validated['school_id'] === 'all' || empty($validated['school_id'])) {
            $validated['school_id'] = null;
        }

        $manager = InventoryManager::updateOrCreate(
            ['user_id' => $validated['user_id']],
            [
                'school_id' => $validated['school_id'],
                'role_title' => $validated['role_title'],
                'can_create' => $request->boolean('can_create', true),
                'can_edit' => $request->boolean('can_edit', true),
                'can_delete' => $request->boolean('can_delete', true),
                'can_export' => $request->boolean('can_export', true),
                'is_active' => true,
                'appointed_by' => auth()->id(),
                'appointed_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        $targetUser = User::find($validated['user_id']);

        return redirect()->route('inventory.managers.index')
            ->with('success', "Akun {$targetUser->name} ({$targetUser->username}) berhasil ditunjuk sebagai {$manager->role_title} PembdaINVENTORY.");
    }

    /**
     * Toggle Status Aktif / Nonaktif Akun
     */
    public function toggleActive($id)
    {
        $this->authorizeAccess();

        $manager = InventoryManager::with('user')->findOrFail($id);
        $manager->is_active = !$manager->is_active;
        $manager->save();

        $statusText = $manager->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';
        return redirect()->route('inventory.managers.index')
            ->with('success', "Akses kelola inventaris untuk {$manager->user->name} berhasil {$statusText}.");
    }

    /**
     * Cabut Penunjukan Akun
     */
    public function destroy($id)
    {
        $this->authorizeAccess();

        $manager = InventoryManager::with('user')->findOrFail($id);
        $userName = $manager->user->name;
        $manager->delete();

        return redirect()->route('inventory.managers.index')
            ->with('success', "Penunjukan akun {$userName} sebagai pengelola aset berhasil dicabut.");
    }
}
