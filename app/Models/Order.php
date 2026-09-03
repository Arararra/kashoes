<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'services' => 'array',
        'estimated_finished_date' => 'date',
        'finished_date' => 'date',
        'total_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cashFlow(): HasOne
    {
        return $this->hasOne(CashFlow::class)->where('type', 'income');
    }

    public function serviceLineItems(): array
    {
        $items = collect($this->services ?? [])->map(fn (array $item): array => [
            ...$item,
            'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
            'price' => (float) ($item['price'] ?? 0),
        ]);
        $expectedSubtotal = (float) $this->total_price + (float) $this->discount;
        $unitPriceSubtotal = $items->sum(fn (array $item): float => $item['price'] * $item['quantity']);
        $legacyLineSubtotal = $items->sum('price');
        $usesLegacyLinePrice = abs($legacyLineSubtotal - $expectedSubtotal)
            < abs($unitPriceSubtotal - $expectedSubtotal);

        return $items->map(function (array $item) use ($usesLegacyLinePrice): array {
            $lineTotal = $usesLegacyLinePrice
                ? $item['price']
                : $item['price'] * $item['quantity'];

            return [
                ...$item,
                'unit_price' => $usesLegacyLinePrice
                    ? $item['price'] / $item['quantity']
                    : $item['price'],
                'line_total' => $lineTotal,
            ];
        })->all();
    }

    protected static function booted(): void
    {
        static::created(function (Order $order): void {
            if ($order->status === 'completed') {
                $order->finished_date = now()->toDateString();
                $order->saveQuietly();
                $order->recordCashFlow(
                    type: 'income',
                    amount: (float) $order->total_price,
                    title: sprintf('Order #%d - Pembayaran Diterima', $order->id),
                    description: sprintf('Order selesai atas nama %s', $order->customer_name),
                );
            }
        });

        static::updated(function (Order $order): void {
            $prevStatus = $order->getOriginal('status');
            $newStatus = $order->status;
            $totalHarga = (float) $order->total_price;
            $statusChanged = $order->isDirty('status');
            $priceChanged = $order->isDirty('total_price');

            // ── 1. Status baru menjadi 'completed' ──────────────────────
            //    → Buat CashFlow income (hanya jika sebelumnya bukan completed)
            if ($statusChanged && $newStatus === 'completed' && $prevStatus !== 'completed') {
                $order->finished_date = now()->toDateString();
                $order->saveQuietly();
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
                if (! empty($order->finished_date)) {
                    $order->finished_date = null;
                    $order->saveQuietly();
                }
            }

            // ── 3. Harga berubah saat status masih/tetap 'completed' ────
            //    → UPDATE langsung amount di CashFlow yang sudah ada,
            //      bukan buat entri koreksi baru (lebih bersih di DB)
            if ($priceChanged && ! $statusChanged && $newStatus === 'completed') {
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

            if ($order->isDirty('finished_date') && ! $statusChanged && $newStatus === 'completed') {
                CashFlow::where('order_id', $order->id)
                    ->where('type', 'income')
                    ->update(['date' => $order->finished_date ?? now()->toDateString()]);
            }
        });

        // ── SOFT DELETE → hapus semua CashFlow terkait order ──────────
        static::deleted(function (Order $order): void {
            CashFlow::where('order_id', $order->id)->delete();
        });

        // ── FORCE DELETE → hapus permanen CashFlow terkait ─────────────
        static::forceDeleted(function (Order $order): void {
            CashFlow::where('order_id', $order->id)->delete();
        });

        static::restored(function (Order $order): void {
            if ($order->status !== 'completed') {
                return;
            }

            if (empty($order->finished_date)) {
                $order->finished_date = now()->toDateString();
                $order->saveQuietly();
            }

            $order->recordCashFlow(
                type: 'income',
                amount: (float) $order->total_price,
                title: sprintf('Order #%d - Pembayaran Diterima', $order->id),
                description: sprintf('Order selesai atas nama %s', $order->customer_name),
            );
        });
    }

    private function recordCashFlow(string $type, float $amount, string $title, ?string $description = null): void
    {
        if ($amount <= 0) {
            return;
        }

        CashFlow::updateOrCreate([
            'order_id' => $this->id,
            'type' => $type,
        ], [
            'date' => $this->finished_date?->toDateString() ?? now()->toDateString(),
            'title' => $title,
            'description' => $description,
            'amount' => $amount,
            'created_by' => request()->user()?->id ?? auth()->id() ?? $this->created_by,
        ]);
    }
}
