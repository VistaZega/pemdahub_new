<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $currentYear = \App\Models\AcademicYear::where('is_active', 1)->first();

        // Base query
        $query = Position::with(['school', 'activeEmployees' => function ($q) use ($currentYear) {
            if ($currentYear) {
                $q->where('employee_positions.academic_year_id', $currentYear->id);
            }
        }]);
        if (!$user->isSuperAdmin()) {
            $query->where(function($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->orWhereNull('school_id');
            });
        }
        
        // Filter by school from request (for superadmin)
        if ($request->filled('school_id')) {
            if ($request->school_id === 'global') {
                $query->whereNull('school_id');
            } else {
                $query->where('school_id', $request->school_id);
            }
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('position_category', $request->category);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('position_name', 'like', "%{$search}%")
                  ->orWhere('position_code', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }
        
        // Clone query for stats calculation before ordering and pagination
        $statsQuery = clone $query;
        $totalPositions = $statsQuery->count();
        $activePositions = (clone $statsQuery)->where('is_active', 1)->count();
        $avgAllowance = (clone $statsQuery)->where('is_active', 1)->avg('allowance_amount') ?? 0;
        $totalBudget = (clone $statsQuery)->where('is_active', 1)->sum('allowance_amount') ?? 0;

        $positions = $query->orderBy('position_category')
            ->orderBy('position_name')
            ->paginate(20)->withQueryString();
        
        // Get schools for filter
        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', 1)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();
        
        // Get categories
        $categories = Position::select('position_category')
            ->distinct()
            ->whereNotNull('position_category')
            ->pluck('position_category');
        
        return view('admin.positions.index', compact(
            'positions', 
            'schools', 
            'categories',
            'totalPositions',
            'activePositions',
            'avgAllowance',
            'totalBudget'
        ));
    }
    
    public function create()
    {
        $user = auth()->user();
        
        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', 1)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();
        
        $categories = ['structural', 'functional', 'staff', 'support'];
        
        return view('admin.positions.create', compact('schools', 'categories'));
    }
    
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'position_name' => 'required|string|max:100',
            'position_code' => 'required|string|max:100|unique:positions,position_code',
            'position_category' => 'required|string|in:structural,functional,staff,support',
            'allowance_amount' => 'required|numeric|min:0',
            'school_id' => 'nullable|exists:schools,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        // Authorization check
        if (!$user->isSuperAdmin()) {
            if ($validated['school_id'] && $validated['school_id'] != $user->school_id) {
                abort(403, 'Unauthorized');
            }
        }
        
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        
        Position::create($validated);
        
        return redirect()
            ->route('admin.master.positions.index', $this->getFiltersFromRequest($request))
            ->with('success', 'Jabatan berhasil ditambahkan.');
    }
    
    public function edit(Position $position)
    {
        $user = auth()->user();
        
        // Authorization check
        if (!$user->isSuperAdmin()) {
            if ($position->school_id && $position->school_id != $user->school_id) {
                abort(403, 'Unauthorized');
            }
        }
        
        $schools = $user->isSuperAdmin() 
            ? School::where('is_active', 1)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();
        
        $categories = ['structural', 'functional', 'staff', 'support'];
        
        return view('admin.positions.edit', compact('position', 'schools', 'categories'));
    }
    
    public function update(Request $request, Position $position)
    {
        $user = auth()->user();
        
        // Authorization check
        if (!$user->isSuperAdmin()) {
            if ($position->school_id && $position->school_id != $user->school_id) {
                abort(403, 'Unauthorized');
            }
        }
        
        $validated = $request->validate([
            'position_name' => 'required|string|max:100',
            'position_code' => 'required|string|max:100|unique:positions,position_code,' . $position->id,
            'position_category' => 'required|string|in:structural,functional,staff,support',
            'allowance_amount' => 'required|numeric|min:0',
            'school_id' => 'nullable|exists:schools,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        // Authorization check for new school_id
        if (!$user->isSuperAdmin()) {
            if ($validated['school_id'] && $validated['school_id'] != $user->school_id) {
                abort(403, 'Unauthorized');
            }
        }
        
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        
        $position->update($validated);
        
        return redirect()
            ->route('admin.master.positions.index', $this->getFiltersFromRequest($request))
            ->with('success', 'Jabatan berhasil diperbarui.');
    }
    
    public function destroy(Request $request, Position $position)
    {
        $user = auth()->user();
        
        // Authorization check
        if (!$user->isSuperAdmin()) {
            if ($position->school_id && $position->school_id != $user->school_id) {
                abort(403, 'Unauthorized');
            }
        }
        
        // Check if position is being used in active academic year
        $currentYear = \App\Models\AcademicYear::where('is_active', 1)->first();
        
        $usageQuery = DB::table('employee_positions')
            ->where('position_id', $position->id)
            ->whereNull('end_date');
            
        if ($currentYear) {
            $usageQuery->where('academic_year_id', $currentYear->id);
        }
        
        $usageCount = $usageQuery->count();
        
        if ($usageCount > 0) {
            $yearName = $currentYear ? $currentYear->year : 'Aktif';
            return back()->with('error', "Tidak dapat menghapus jabatan. Masih ada {$usageCount} guru yang aktif memegang jabatan ini pada Tahun Pelajaran {$yearName}.");
        }
        
        $position->delete();
        
        return redirect()
            ->route('admin.master.positions.index', $this->getFiltersFromRequest($request))
            ->with('success', 'Jabatan berhasil dihapus.');
    }

    public function bulkUpdateAllowances(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|exists:positions,id',
            'positions.*.allowance_amount' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        try {
            foreach ($validated['positions'] as $posData) {
                $position = Position::find($posData['id']);
                
                // Authorization check
                if (!$user->isSuperAdmin()) {
                    if ($position->school_id && $position->school_id != $user->school_id) {
                        continue; // Skip unauthorized positions
                    }
                }
                
                $position->update(['allowance_amount' => $posData['allowance_amount']]);
            }
            
            DB::commit();
            
            return back()->with('success', 'Tarif tunjangan berhasil diperbarui.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui tarif: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui tarif. Silakan coba lagi.');
        }
    }

    private function getFiltersFromRequest(Request $request): array
    {
        $filters = [];
        $keys = ['school_id', 'category', 'status', 'search', 'page'];
        
        foreach ($keys as $key) {
            if ($request->has("f_{$key}")) {
                $filters[$key] = $request->get("f_{$key}");
            } elseif ($request->has($key)) {
                $filters[$key] = $request->get($key);
            }
        }
        
        return $filters;
    }
}
