<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'is_member',
    ];

    protected $casts = [
        'is_member' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::updated(function (Customer $customer): void {
            if (! $customer->user_id) {
                return;
            }

            $changes = collect(['name', 'phone', 'address'])
                ->filter(fn (string $field): bool => $customer->wasChanged($field))
                ->mapWithKeys(fn (string $field): array => [$field => $customer->{$field}])
                ->all();

            if ($changes !== []) {
                $customer->user?->updateQuietly($changes);
            }
        });
    }
}
