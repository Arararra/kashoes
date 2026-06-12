<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CashFlow;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $user = auth('sanctum')->user();
        $isCustomer = $user && $user->hasRole('customer');
        $customerId = null;

        if ($isCustomer) {
            $customer = \App\Models\Customer::where('user_id', $user->id)->first();
            if ($customer) {
                $customerId = $customer->id;
            }
        }

        // Base order query
        $orderQuery = Order::query();
        if ($customerId) {
            $orderQuery->where('customer_id', $customerId);
        }

        // Orders stats
        $totalOrdersToday = (clone $orderQuery)->whereDate('created_at', $today)->count();
        $totalOrdersMonth = (clone $orderQuery)->where('created_at', '>=', $startOfMonth)->count();
        
        $activeOrders = (clone $orderQuery)->whereIn('status', ['pending', 'in_progress'])->count();
        $readyOrders = (clone $orderQuery)->where('status', 'ready_for_pickup')->count();

        // Base revenue query
        $revenueQuery = CashFlow::where('type', 'income');
        if ($customerId) {
            // Usually customers don't see revenue, or they only see their spending.
            // If it's a customer, revenue might be irrelevant or it's their total spending.
            // But let's filter it by their orders if needed, or just return 0.
            $revenueQuery->where('reference_id', 'LIKE', 'ORD-%'); // Need to join or just return 0
            // For simplicity, let's just return 0 for customer revenue.
            $revenueToday = 0;
            $revenueMonth = 0;
        } else {
            $revenueToday = (clone $revenueQuery)
                ->whereDate('date', $today)
                ->sum('amount');
                
            $revenueMonth = (clone $revenueQuery)
                ->where('date', '>=', $startOfMonth)
                ->sum('amount');
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
                ]
            ]
        ]);
    }
}
