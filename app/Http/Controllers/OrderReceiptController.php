<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;

class OrderReceiptController extends Controller
{
    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        // Load semua service IDs dari items order
        $serviceIds = collect($order->services ?? [])->pluck('service_id')->filter()->unique();
        $serviceMap = Service::whereIn('id', $serviceIds)->pluck('name', 'id');

        $items = collect($order->serviceLineItems())->map(function ($item) use ($serviceMap) {
            return [
                'name' => $serviceMap[$item['service_id'] ?? null] ?? 'Service',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price' => (float) ($item['price'] ?? 0),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'line_total' => (float) ($item['line_total'] ?? 0),
                'description' => $item['description'] ?? null,
            ];
        });

        $discount = (float) ($order->discount ?? 0);
        $total = (float) $order->total_price;
        $subtotal = $items->sum(fn ($item) => $item['line_total']);

        return view('orders.receipt', compact('order', 'items', 'subtotal', 'discount', 'total'));
    }
}
