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
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Get the user who created this order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::created(function (Order $order): void {
            $order->recordCashFlow(
                type: 'income',
                amount: $order->total_price,
                title: sprintf('Order #%d payment received', $order->id),
                description: sprintf('Order created for %s', $order->customer_name),
            );
        });

        static::updated(function (Order $order): void {
            $originalTotal = (float) $order->getOriginal('total_price');
            $currentTotal = (float) $order->total_price;

            if ($order->isDirty('total_price') && $currentTotal !== $originalTotal) {
                $difference = $currentTotal - $originalTotal;

                if ($difference > 0) {
                    $order->recordCashFlow(
                        type: 'income',
                        amount: $difference,
                        title: sprintf('Order #%d amount updated', $order->id),
                        description: sprintf('Order total increased from %s to %s', number_format($originalTotal, 2), number_format($currentTotal, 2)),
                    );
                } elseif ($difference < 0) {
                    $order->recordCashFlow(
                        type: 'expense',
                        amount: abs($difference),
                        title: sprintf('Order #%d amount reduced', $order->id),
                        description: sprintf('Order total decreased from %s to %s', number_format($originalTotal, 2), number_format($currentTotal, 2)),
                    );
                }
            }

            if ($order->isDirty('status') && $order->status === 'cancelled' && $order->getOriginal('status') !== 'cancelled') {
                $order->recordCashFlow(
                    type: 'expense',
                    amount: $currentTotal,
                    title: sprintf('Order #%d cancelled', $order->id),
                    description: 'Order cancelled and amount refunded/reversed.',
                );
            }
        });
    }

    private function recordCashFlow(string $type, float $amount, string $title, ?string $description = null): void
    {
        if ($amount <= 0) {
            return;
        }

        CashFlow::create([
            'date' => now()->toDateString(),
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'amount' => $amount,
            'created_by' => auth()->id(),
        ]);
    }
}
