<?php

namespace App\Models;

use App\Models\CashFlow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'latitude',
        'longitude',
        'services',
        'total_price',
        'discount',
        'status',
        'estimated_finished_date',
        'finished_date',
        'created_by',
    ];

    protected $casts = [
        'services'               => 'array',
        'estimated_finished_date' => 'date',
        'finished_date'          => 'date',
        'total_price'            => 'decimal:2',
        'discount'               => 'decimal:2',
        'latitude'               => 'decimal:8',
        'longitude'              => 'decimal:8',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        // ── UPDATE: pantau perubahan status ────────────────────────────
        static::updated(function (Order $order): void {
            $prevStatus  = $order->getOriginal('status');
            $newStatus   = $order->status;
            $totalHarga  = (float) $order->total_price;

            // 1. Baru menjadi 'completed' (Selesai/Sudah Diambil) → catat income
            if ($order->isDirty('status') && $newStatus === 'completed' && $prevStatus !== 'completed') {
                $order->recordCashFlow(
                    type: 'income',
                    amount: $totalHarga,
                    title: sprintf('Order #%d - Pembayaran Diterima', $order->id),
                    description: sprintf('Order selesai atas nama %s', $order->customer_name),
                );
            }

            // 2. Dari 'completed' → 'cancelled': hapus income lalu catat reversal
            if ($order->isDirty('status') && $newStatus === 'cancelled' && $prevStatus === 'completed') {
                CashFlow::where('order_id', $order->id)->where('type', 'income')->delete();

                $order->recordCashFlow(
                    type: 'expense',
                    amount: $totalHarga,
                    title: sprintf('Order #%d - Dibatalkan (Reversal)', $order->id),
                    description: sprintf('Order milik %s dibatalkan setelah selesai.', $order->customer_name),
                );
            }

            // 3. Harga berubah saat order sudah 'completed' → koreksi CashFlow
            if ($order->isDirty('total_price') && $newStatus === 'completed') {
                $originalTotal = (float) $order->getOriginal('total_price');
                $difference    = $totalHarga - $originalTotal;

                if ($difference > 0) {
                    $order->recordCashFlow(
                        type: 'income',
                        amount: $difference,
                        title: sprintf('Order #%d - Koreksi Harga (+)', $order->id),
                        description: sprintf('Total naik dari Rp%s ke Rp%s', number_format($originalTotal, 0, ',', '.'), number_format($totalHarga, 0, ',', '.')),
                    );
                } elseif ($difference < 0) {
                    $order->recordCashFlow(
                        type: 'expense',
                        amount: abs($difference),
                        title: sprintf('Order #%d - Koreksi Harga (-)', $order->id),
                        description: sprintf('Total turun dari Rp%s ke Rp%s', number_format($originalTotal, 0, ',', '.'), number_format($totalHarga, 0, ',', '.')),
                    );
                }
            }
        });

        // ── SOFT DELETE → hapus semua CashFlow terkait order ──────────
        static::deleted(function (Order $order): void {
            CashFlow::where('order_id', $order->id)->delete();
        });

        // ── FORCE DELETE → hapus permanen CashFlow terkait ─────────────
        static::forceDeleted(function (Order $order): void {
            CashFlow::where('order_id', $order->id)->forceDelete();
        });
    }

    private function recordCashFlow(string $type, float $amount, string $title, ?string $description = null): void
    {
        if ($amount <= 0) {
            return;
        }

        CashFlow::create([
            'date'        => now()->toDateString(),
            'type'        => $type,
            'title'       => $title,
            'description' => $description,
            'amount'      => $amount,
            'created_by'  => auth()->id(),
            'order_id'    => $this->id,
        ]);
    }
}
