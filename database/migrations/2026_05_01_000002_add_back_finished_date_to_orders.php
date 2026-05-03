<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kembali kolom finished_date sebagai nullable.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->date('finished_date')->nullable()->after('estimated_finished_date');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('finished_date');
        });
    }
};
