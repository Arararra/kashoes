<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->unsignedBigInteger('service');
            $table->date('estimated_date');
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'processing', 'ready', 'completed'])->default('pending');
            $table->timestamps();

            $table->foreign('customer')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
