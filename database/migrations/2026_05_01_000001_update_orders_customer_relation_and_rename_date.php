<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - Ubah foreign key customer_id dari users ke customers
     * - Rename estimated_date menjadi estimated_finished_date
     * - Hapus kolom finished_date
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign key lama yang mengarah ke users
            $table->dropForeign(['customer_id']);

            // Tambahkan foreign key baru yang mengarah ke customers
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            // Rename estimated_date menjadi estimated_finished_date
            $table->renameColumn('estimated_date', 'estimated_finished_date');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Hapus kolom finished_date
            $table->dropColumn('finished_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->date('finished_date')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('estimated_finished_date', 'estimated_date');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }
};
