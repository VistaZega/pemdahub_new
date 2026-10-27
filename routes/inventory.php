<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Inventory\InventoryManagerController;
use App\Http\Controllers\Inventory\InventoryMutationController;
use App\Http\Controllers\Inventory\InventoryLocationCategoryController;

/*
|--------------------------------------------------------------------------
| PembdaINVENTORY Routes
|--------------------------------------------------------------------------
| Sistem Manajemen Aset Terpadu Yayasan & Seluruh Unit Sekolah
| Akses Khusus: Hanya Pengurus Yayasan dan Akun yang Ditunjuk
| Middleware: auth, inventory.access
*/

Route::prefix('inventory')->name('inventory.')->middleware(['auth', 'inventory.access'])->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::get('/create', [InventoryController::class, 'create'])->name('create');
    Route::post('/store', [InventoryController::class, 'store'])->name('store');
    Route::post('/recalculate-all', [InventoryController::class, 'recalculateAll'])->name('recalculate');
    Route::get('/export-pdf', [InventoryController::class, 'exportPdf'])->name('export_pdf');
    Route::get('/export-csv', [InventoryController::class, 'exportCsv'])->name('export_csv');

    // 1. Akun yang Ditunjuk oleh Yayasan (Delegated Inventory Managers)
    Route::prefix('managers')->name('managers.')->group(function () {
        Route::get('/', [InventoryManagerController::class, 'index'])->name('index');
        Route::post('/store', [InventoryManagerController::class, 'store'])->name('store');
        Route::post('/{id}/toggle', [InventoryManagerController::class, 'toggle'])->name('toggle');
        Route::delete('/{id}', [InventoryManagerController::class, 'destroy'])->name('destroy');
    });

    // 2. Pengaturan Master Kategori & Lokasi Penempatan Ruangan
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [InventoryLocationCategoryController::class, 'index'])->name('index');
        Route::post('/categories', [InventoryLocationCategoryController::class, 'storeCategory'])->name('categories.store');
        Route::delete('/categories/{category}', [InventoryLocationCategoryController::class, 'destroyCategory'])->name('categories.destroy');
        Route::post('/locations', [InventoryLocationCategoryController::class, 'storeLocation'])->name('locations.store');
        Route::delete('/locations/{location}', [InventoryLocationCategoryController::class, 'destroyLocation'])->name('locations.destroy');
    });

    // 3. Detail, Edit, Update, Label QR, dan Hapus Aset
    Route::get('/{asset}', [InventoryController::class, 'show'])->name('show');
    Route::get('/{asset}/edit', [InventoryController::class, 'edit'])->name('edit');
    Route::put('/{asset}', [InventoryController::class, 'update'])->name('update');
    Route::delete('/{asset}', [InventoryController::class, 'destroy'])->name('destroy');
    Route::get('/{asset}/qr-label', [InventoryController::class, 'qrLabel'])->name('qr_label');

    // 4. Catat Mutasi & Servis Aset
    Route::post('/{asset}/mutations', [InventoryMutationController::class, 'store'])->name('mutations.store');
});
