<?php
// app/Http/Controllers/Admin/AdminDashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, User, MealKit, Review};
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function stats()
    {
        $today     = today();
        $thisMonth = now()->startOfMonth();

        return response()->json([
            'today' => [
                'revenue'       => Order::whereDate('created_at', $today)
                                    ->where('payment_status', 'paid')
                                    ->sum('total_amount'),
                'orders'        => Order::whereDate('created_at', $today)->count(),
                'new_clients'   => User::where('role', 'client')
                                    ->whereDate('created_at', $today)->count(),
                'active_livreurs' => User::where('role', 'livreur')
                                    ->where('is_active', true)->count(),
            ],

            'month' => [
                'revenue' => Order::where('created_at', '>=', $thisMonth)
                                ->where('payment_status', 'paid')
                                ->sum('total_amount'),
                'orders'  => Order::where('created_at', '>=', $thisMonth)->count(),
                'clients' => User::where('role', 'client')
                                ->where('created_at', '>=', $thisMonth)->count(),
            ],

            'orders_chart' => Order::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),

            'payment_methods' => Order::where('payment_status', 'paid')
                ->select('payment_method', DB::raw('COUNT(*) as count'))
                ->groupBy('payment_method')
                ->get(),

            'top_kits' => MealKit::orderBy('order_count', 'desc')
                ->take(5)
                ->get(['id', 'name', 'order_count', 'rating_avg', 'images']),

            'pending_orders' => Order::where('status', 'paid')
                ->whereNull('deliverer_id')->count(),

            'recent_orders' => Order::with(['user:id,name,phone'])
                ->latest()->take(8)
                ->get(['id', 'reference', 'user_id', 'total_amount',
                       'payment_method', 'status', 'created_at']),

            'order_statuses' => Order::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')->get(),
        ]);
    }
}
