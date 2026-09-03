<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $user = auth('sanctum')->user();
        $isAdmin = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin']);
        $customerId = null;

        if ($user && ! $isAdmin) {
            $customer = Customer::where('user_id', $user->id)->first();
            $customerId = $customer?->id;
        }

        // Base order query
        $orderQuery = Order::query();
        if (! $isAdmin) {
            if ($customerId) {
                $orderQuery->where('customer_id', $customerId);
            } else {
                $orderQuery->whereRaw('1 = 0');
            }
        } elseif ($customerId) {
            $orderQuery->where('customer_id', $customerId);
        }

        // Orders stats
        $totalOrdersToday = (clone $orderQuery)->whereDate('created_at', $today)->count();
        $totalOrdersMonth = (clone $orderQuery)->where('created_at', '>=', $startOfMonth)->count();

        $activeOrders = (clone $orderQuery)->whereIn('status', ['pending', 'in_progress'])->count();
        $readyOrders = (clone $orderQuery)->where('status', 'ready_for_pickup')->count();

        // Revenue query
        if (! $isAdmin) {
            $revenueToday = 0;
            $revenueMonth = 0;
        } else {
            $revenueQuery = Order::where('status', 'completed');
            $revenueToday = (clone $revenueQuery)
                ->whereDate('finished_date', $today)
                ->sum('total_price');

            $revenueMonth = (clone $revenueQuery)
                ->where('finished_date', '>=', $startOfMonth)
                ->sum('total_price');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => [
                    'today' => $totalOrdersToday,
                    'this_month' => $totalOrdersMonth,
                    'active' => $activeOrders,
                    'ready_for_pickup' => $readyOrders,
                ],
                'revenue' => [
                    'today' => $revenueToday,
                    'this_month' => $revenueMonth,
                ],
            ],
        ]);
    }
}
