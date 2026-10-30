<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sinkronkan paid_amount dengan jumlah riil pembayaran terverifikasi jika ada perbedaan
        DB::statement("
            UPDATE student_bills b
            INNER JOIN (
                SELECT bill_id, SUM(amount_paid) AS verified_sum
                FROM payments
                WHERE is_verified = 1 AND bill_id IS NOT NULL
                GROUP BY bill_id
            ) p ON p.bill_id = b.id
            SET b.paid_amount = p.verified_sum
            WHERE b.paid_amount != p.verified_sum
        ");

        // 2. Set status = 'lunas' untuk semua tagihan yang sudah terbayar penuh (paid_amount >= amount) namun masih 'cicilan' atau 'belum_bayar'
        DB::statement("
            UPDATE student_bills
            SET status = 'lunas', updated_at = NOW()
            WHERE status != 'lunas'
              AND amount > 0
              AND paid_amount >= amount
        ");

        // 3. Set status = 'lunas' untuk tagihan dengan diskon penuh atau sisa 0
        DB::statement("
            UPDATE student_bills
            SET status = 'lunas', updated_at = NOW()
            WHERE status != 'lunas'
              AND amount > 0
              AND (paid_amount + discount_amount) >= amount
              AND paid_amount > 0
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu dibalikkan karena ini adalah perbaikan integritas data (data repair)
    }
};
