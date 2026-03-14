<?php
// app/Http/Controllers/Admin/AdminPaymentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user:id,name,phone'])
            ->where('payment_status', '!=', 'pending');

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $paiements = $query->latest()->paginate(25);

        $totaux = Order::where('payment_status', 'paid')
            ->select(
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count'),
                'payment_method'
            )
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'paiements' => $paiements,
            'totaux'    => $totaux,
        ]);
    }

    public function export()
    {
        // return Excel::download(new PaymentsExport, 'paiements.xlsx');
        return response()->json(['message' => 'Export en cours...']);
    }
}
