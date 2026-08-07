<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationalExpense;
use App\Models\ExpenseCategory;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OperationalExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isYayasan()) {
            abort(403, 'Akses khusus Yayasan.');
        }
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $schools = $user->isSuperAdmin()
            ? School::where('is_active', true)->orderBy('name')->get()
            : School::where('id', $user->school_id)->get();

        $query = OperationalExpense::with(['school', 'expenseCategory', 'academicYear', 'semester', 'creator'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        } elseif ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->expense_category_id);
        }

        if ($request->filled('month')) {
            $query->whereMonth('expense_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('expense_date', $request->year);
        }

        $expenses = $query->paginate(20)->withQueryString();

        // Master categories for dropdown
        $categoriesQuery = ExpenseCategory::active();
        if (!$user->isSuperAdmin()) {
            $categoriesQuery->where(function ($q) use ($user) {
                $q->whereNull('school_id')->orWhere('school_id', $user->school_id);
            });
        }
        $categories = $categoriesQuery->orderBy('name')->get();

        // Statistics Summary
        $statsQuery = OperationalExpense::query();
        if (!$user->isSuperAdmin()) {
            $statsQuery->where('school_id', $user->school_id);
        } elseif ($request->filled('school_id')) {
            $statsQuery->where('school_id', $request->school_id);
        }

        $totalAmount = (clone $statsQuery)->sum('amount');
        $thisMonthAmount = (clone $statsQuery)->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount');

        return view('admin.operational_expenses.index', compact(
            'expenses',
            'categories',
            'schools',
            'activeYear',
            'activeSemester',
            'totalAmount',
            'thisMonthAmount'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'recipient_name' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,transfer',
            'notes' => 'nullable|string',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        if (!$user->isSuperAdmin()) {
            $validated['school_id'] = $user->school_id;
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $validated['academic_year_id'] = $activeYear?->id;
        $validated['semester_id'] = $activeSemester?->id;
        $validated['created_by'] = $user->id;

        if ($request->hasFile('proof_file')) {
            $validated['proof_file'] = $request->file('proof_file')->store('operational_expenses', 'public');
        }

        OperationalExpense::create($validated);

        return redirect()->back()->with('success', 'Transaksi pengeluaran operasional berhasil dicatat!');
    }

    public function update(Request $request, OperationalExpense $operationalExpense)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $operationalExpense->school_id !== $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'recipient_name' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,transfer',
            'notes' => 'nullable|string',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        if ($request->hasFile('proof_file')) {
            if ($operationalExpense->proof_file) {
                Storage::disk('public')->delete($operationalExpense->proof_file);
            }
            $validated['proof_file'] = $request->file('proof_file')->store('operational_expenses', 'public');
        }

        $operationalExpense->update($validated);

        return redirect()->back()->with('success', 'Data pengeluaran operasional berhasil diperbarui!');
    }

    public function destroy(OperationalExpense $operationalExpense)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $operationalExpense->school_id !== $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        if ($operationalExpense->proof_file) {
            Storage::disk('public')->delete($operationalExpense->proof_file);
        }

        $operationalExpense->delete();

        return redirect()->back()->with('success', 'Transaksi pengeluaran operasional berhasil dihapus!');
    }

    // Category Management
    public function storeCategory(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        if (!$user->isSuperAdmin()) {
            $validated['school_id'] = $user->school_id;
        }

        ExpenseCategory::create($validated);

        return redirect()->back()->with('success', 'Kategori rekening pengeluaran berhasil ditambahkan!');
    }

    public function updateCategory(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Kategori rekening berhasil diperbarui!');
    }

    public function destroyCategory(ExpenseCategory $category)
    {
        if ($category->operationalExpenses()->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus kategori yang sudah memiliki transaksi!');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Kategori rekening berhasil dihapus!');
    }
}
