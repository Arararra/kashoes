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
        static::updated(function (Order $order): void {
            $prevStatus   = $order->getOriginal('status');
            $newStatus    = $order->status;
            $totalHarga   = (float) $order->total_price;
            $statusChanged = $order->isDirty('status');
            $priceChanged  = $order->isDirty('total_price');

            // ── 1. Status baru menjadi 'completed' ──────────────────────
            //    → Buat CashFlow income (hanya jika sebelumnya bukan completed)
            if ($statusChanged && $newStatus === 'completed' && $prevStatus !== 'completed') {
                $order->recordCashFlow(
                    type: 'income',
                    amount: $totalHarga,
                    title: sprintf('Order #%d - Pembayaran Diterima', $order->id),
                    description: sprintf('Order selesai atas nama %s', $order->customer_name),
                );
            }

            // ── 2. Status KELUAR dari 'completed' ke status apapun ──────
            //    → Hapus semua income CashFlow order ini (tidak perlu reversal
            //      karena pesanannya belum tentu benar-benar dibayar)
            if ($statusChanged && $prevStatus === 'completed' && $newStatus !== 'completed') {
                CashFlow::where('order_id', $order->id)->delete();
            }

            // ── 3. Harga berubah saat status masih/tetap 'completed' ────
            //    → UPDATE langsung amount di CashFlow yang sudah ada,
            //      bukan buat entri koreksi baru (lebih bersih di DB)
            if ($priceChanged && !$statusChanged && $newStatus === 'completed') {
                $existing = CashFlow::where('order_id', $order->id)
                    ->where('type', 'income')
                    ->first();

                if ($existing) {
                    // Langsung update amount-nya
                    $existing->update(['amount' => $totalHarga]);
                } else {
                    // Belum ada entri (edge case), buat baru
                    $order->recordCashFlow(
                        type: 'income',
                        amount: $totalHarga,
                        title: sprintf('Order #%d - Pembayaran Diterima', $order->id),
                        description: sprintf('Order selesai atas nama %s', $order->customer_name),
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
