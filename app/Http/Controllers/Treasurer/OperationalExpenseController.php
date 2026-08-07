<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\OperationalExpense;
use App\Models\ExpenseCategory;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OperationalExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $query = OperationalExpense::with(['school', 'expenseCategory', 'academicYear', 'semester', 'creator'])
            ->where('school_id', $schoolId)
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

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

        $categories = ExpenseCategory::active()
            ->where(function ($q) use ($schoolId) {
                $q->whereNull('school_id')->orWhere('school_id', $schoolId);
            })
            ->orderBy('name')
            ->get();

        $totalAmount = OperationalExpense::where('school_id', $schoolId)->sum('amount');
        $thisMonthAmount = OperationalExpense::where('school_id', $schoolId)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        return view('treasurer.operational_expenses.index', compact(
            'expenses',
            'categories',
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
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'recipient_name' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,transfer',
            'notes' => 'nullable|string',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $validated['school_id'] = $user->school_id;
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

        if ($operationalExpense->school_id !== $user->school_id) {
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

        if ($operationalExpense->school_id !== $user->school_id) {
            abort(403, 'Akses ditolak.');
        }

        if ($operationalExpense->proof_file) {
            Storage::disk('public')->delete($operationalExpense->proof_file);
        }

        $operationalExpense->delete();

        return redirect()->back()->with('success', 'Transaksi pengeluaran operasional berhasil dihapus!');
    }
}
