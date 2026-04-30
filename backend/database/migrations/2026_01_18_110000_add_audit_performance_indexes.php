<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('penugasan')) {
            Schema::table('penugasan', function (Blueprint $table) {
                // Use a try-catch block or explicit checks if Schema::hasIndex is strictly available
                // To be safe and compatible, we check for index existence by name.
                
                $indexesToCheck = [
                    'penugasan_kegiatan_id_audit_index' => ['kegiatan_id'],
                    'penugasan_mitra_id_audit_index' => ['mitra_id'],
                    'penugasan_bln_bayar_audit_index' => ['bln_bayar'],
                    'penugasan_kegiatan_bln_audit_index' => ['kegiatan_id', 'bln_bayar']
                ];

                foreach ($indexesToCheck as $name => $columns) {
                    if (!Schema::hasIndex('penugasan', $name)) {
                       $table->index($columns, $name);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('penugasan')) {
            Schema::table('penugasan', function (Blueprint $table) {
                $indexes = [
                    'penugasan_kegiatan_id_audit_index',
                    'penugasan_mitra_id_audit_index',
                    'penugasan_bln_bayar_audit_index',
                    'penugasan_kegiatan_bln_audit_index'
                ];
                
                foreach ($indexes as $index) {
                     if (Schema::hasIndex('penugasan', $index)) {
                        $table->dropIndex($index);
                     }
                }
            });
        }
    }
};
