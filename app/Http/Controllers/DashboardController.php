<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. KPI Aggregates
        $totalSales = DB::table('sales_orders')->sum('grand_total');
        $outstandingAR = DB::table('accounts_receivable')->where('status', '!=', 'PAID')->sum('balance_amount');
        $totalStockQty = DB::table('stock_balances')->sum('quantity');
        $totalIccidAvailable = DB::table('iccid_units')->where('status', 'AVAILABLE')->count();
        $totalIccidSold = DB::table('iccid_units')->where('status', 'SOLD')->count();
        $pendingSO = DB::table('sales_orders')->where('status', 'PENDING_APPROVAL')->count();
        $cashInToday = DB::table('payments')->sum('amount');

        // 2. Stock distribution across warehouse types
        $stockByWhType = DB::table('stock_balances')
            ->join('warehouses', 'stock_balances.warehouse_id', '=', 'warehouses.id')
            ->select('warehouses.type', DB::raw('SUM(stock_balances.quantity) as total_qty'))
            ->groupBy('warehouses.type')
            ->pluck('total_qty', 'type')
            ->toArray();

        $mainStock = $stockByWhType['MAIN'] ?? 0;
        $invStock = $stockByWhType['INVENTORY'] ?? 0;
        $salesStock = $stockByWhType['SALES'] ?? 0;

        // 3. Recent Transactions (SO, GD, AR, FP) with full DocType audit
        $recentOrders = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->join('sales', 'sales_orders.sales_id', '=', 'sales.id')
            ->join('taps', 'sales_orders.tap_id', '=', 'taps.id')
            ->select(
                'sales_orders.*',
                'customers.name as customer_name',
                'sales.name as sales_name',
                'taps.name as tap_name'
            )
            ->orderBy('sales_orders.created_at', 'desc')
            ->take(6)
            ->get();

        // 4. Sales Leaderboard
        $salesLeaderboard = DB::table('sales')
            ->where('sales.status', 'ACTIVE')
            ->join('taps', 'sales.tap_id', '=', 'taps.id')
            ->leftJoin('sales_orders', 'sales.id', '=', 'sales_orders.sales_id')
            ->select(
                'sales.id',
                'sales.name',
                'sales.code',
                'taps.name as tap_name',
                DB::raw('COALESCE(SUM(sales_orders.grand_total), 0) as total_omset'),
                DB::raw('COUNT(sales_orders.id) as total_trx')
            )
            ->groupBy('sales.id', 'sales.name', 'sales.code', 'taps.name')
            ->orderBy('total_omset', 'desc')
            ->get();

        // 5. Filter Dropdown Hierarki
        $areas = DB::table('areas')->where('status', 'ACTIVE')->get();
        $clusters = DB::table('clusters')->where('status', 'ACTIVE')->get();
        $taps = DB::table('taps')->where('status', 'ACTIVE')->get();

        return view('dashboard.index', compact(
            'totalSales',
            'outstandingAR',
            'totalStockQty',
            'totalIccidAvailable',
            'totalIccidSold',
            'pendingSO',
            'cashInToday',
            'mainStock',
            'invStock',
            'salesStock',
            'recentOrders',
            'salesLeaderboard',
            'areas',
            'clusters',
            'taps'
        ));
    }
}

