<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;

class OrderReceiptController extends Controller
{
    public function show(Order $order)
    {
        // Load semua service IDs dari items order
        $serviceIds = collect($order->services ?? [])->pluck('service_id')->filter()->unique();
        $serviceMap = Service::whereIn('id', $serviceIds)->pluck('name', 'id');

        $items = collect($order->services ?? [])->map(function ($item) use ($serviceMap) {
            return [
                'name'        => $serviceMap[$item['service_id'] ?? null] ?? 'Service',
                'quantity'    => (int) ($item['quantity'] ?? 1),
                'price'       => (float) ($item['price'] ?? 0),
                'description' => $item['description'] ?? null,
            ];
        });

        $subtotal   = $items->sum(fn ($i) => $i['price']);
        $discount   = (float) ($order->discount ?? 0);
        $total      = (float) $order->total_price;

        return view('orders.receipt', compact('order', 'items', 'subtotal', 'discount', 'total'));
    }
}
