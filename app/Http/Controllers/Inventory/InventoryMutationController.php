<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryAsset;
use App\Models\InventoryMutation;
use Illuminate\Http\Request;

class InventoryMutationController extends Controller
{
    /**
     * Catat Mutasi Lokasi, Servis, atau Pengecekan Fisik Aset
     */
    public function store(Request $request, InventoryAsset $asset)
    {
        $validated = $request->validate([
            'mutation_date' => 'required|date',
            'type' => 'required|string|in:mutasi_lokasi,perbaikan,pengecekan_rutin,perubahan_kondisi,penghapusan',
            'to_location_id' => 'nullable|exists:inventory_locations,id',
            'to_condition' => 'nullable|string|in:baik,rusak_ringan,rusak_berat,hilang,dihapuskan',
            'cost' => 'nullable|numeric|min:0',
            'description' => 'required|string',
        ]);

        $mutation = InventoryMutation::create([
            'asset_id' => $asset->id,
            'mutation_date' => $validated['mutation_date'],
            'type' => $validated['type'],
            'from_location_id' => $asset->location_id,
            'to_location_id' => $validated['to_location_id'] ?: $asset->location_id,
            'from_condition' => $asset->condition,
            'to_condition' => $validated['to_condition'] ?: $asset->condition,
            'cost' => $validated['cost'] ?: 0,
            'description' => $validated['description'],
            'performed_by' => auth()->id(),
        ]);

        // Perbarui lokasi/kondisi aset jika ada perubahan
        if (!empty($validated['to_location_id'])) {
            $asset->location_id = $validated['to_location_id'];
        }
        if (!empty($validated['to_condition'])) {
            $asset->condition = $validated['to_condition'];
            if ($validated['to_condition'] === 'dihapuskan') {
                $asset->status = 'dihapuskan';
            }
        }
        $asset->updated_by = auth()->id();
        $asset->saveQuietly();

        return redirect()->route('inventory.show', $asset->id)
            ->with('success', 'Riwayat mutasi/pemeliharaan aset berhasil dicatat.');
    }
}
