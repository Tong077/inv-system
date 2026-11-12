<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate a daily sequential document number while the caller's transaction is active.
     */
    public function next(string $prefix, string $table, string $column): string
    {
        $now = now();
        $date = $now->toDateString();
        $dateLabel = $now->format('Ymd');

        DB::table('document_number_sequences')->insertOrIgnore([
            'prefix' => $prefix,
            'sequence_date' => $date,
            'last_number' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sequence = DB::table('document_number_sequences')
            ->where('prefix', $prefix)
            ->where('sequence_date', $date)
            ->lockForUpdate()
            ->first();

        $number = (int) $sequence->last_number;
        do {
            $number++;
            $candidate = sprintf('%s-%s-%05d', $prefix, $dateLabel, $number);
        } while (DB::table($table)->where($column, $candidate)->exists());

        DB::table('document_number_sequences')
            ->where('id', $sequence->id)
            ->update(['last_number' => $number, 'updated_at' => now()]);

        return $candidate;
    }
}
