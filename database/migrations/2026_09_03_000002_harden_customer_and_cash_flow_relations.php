<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateCustomer = DB::table('customers')
            ->whereNotNull('user_id')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateCustomer) {
            throw new \RuntimeException('Rapikan customer dengan user_id ganda sebelum menjalankan migration ini.');
        }

        $duplicateCashFlow = DB::table('cash_flows')
            ->whereNotNull('order_id')
            ->select('order_id', 'type')
            ->groupBy('order_id', 'type')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateCashFlow) {
            throw new \RuntimeException('Rapikan CashFlow order yang ganda sebelum menjalankan migration ini.');
        }

        $orphanCashFlow = DB::table('cash_flows')
            ->whereNotNull('order_id')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('orders')
                    ->whereColumn('orders.id', 'cash_flows.order_id');
            })
            ->exists();

        if ($orphanCashFlow) {
            throw new \RuntimeException('Rapikan CashFlow tanpa order sebelum menjalankan migration ini.');
        }

        $orders = DB::table('orders')
            ->where('status', 'completed')
            ->whereNull('finished_date')
            ->select(['id', 'updated_at'])
            ->get();

        foreach ($orders as $order) {
            $cashFlowDate = DB::table('cash_flows')
                ->where('order_id', $order->id)
                ->where('type', 'income')
                ->value('date');

            DB::table('orders')->where('id', $order->id)->update([
                'finished_date' => Carbon::parse($cashFlowDate ?? $order->updated_at)->toDateString(),
            ]);
        }

        Schema::table('customers', function (Blueprint $table): void {
            $table->unique('user_id', 'customers_user_id_unique');
        });

        Schema::table('cash_flows', function (Blueprint $table): void {
            $table->unique(['order_id', 'type'], 'cash_flows_order_type_unique');
            $table->foreign('order_id', 'cash_flows_order_id_foreign')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_flows', function (Blueprint $table): void {
            $table->dropForeign('cash_flows_order_id_foreign');
            $table->dropUnique('cash_flows_order_type_unique');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropUnique('customers_user_id_unique');
        });
    }
};
