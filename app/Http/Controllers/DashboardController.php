<?php

namespace App\Http\Controllers;

use App\Services\TerritoryScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. KPI Aggregates (Scope wilayah)
        $soScope = TerritoryScope::applyTapScope(DB::table('sales_orders'), 'sales_orders.tap_id');
        $totalSales = (clone $soScope)->sum('grand_total');
        $pendingSO = (clone $soScope)->where('status', 'PENDING_APPROVAL')->count();

        $whScopeStock = TerritoryScope::applyWarehouseScope(DB::table('stock_balances'), 'stock_balances.warehouse_id');
        $totalStockQty = (clone $whScopeStock)->sum('quantity');

        $whScopeIccid = TerritoryScope::applyWarehouseScope(DB::table('iccid_units'), 'iccid_units.warehouse_id');
        $totalIccidAvailable = (clone $whScopeIccid)->where('status', 'AVAILABLE')->count();
        $totalIccidSold = (clone $whScopeIccid)->where('status', 'SOLD')->count();

        $outstandingAR = TerritoryScope::applyTapScope(
            DB::table('accounts_receivable')
                ->join('sales_orders', 'accounts_receivable.sales_order_id', '=', 'sales_orders.id'),
            'sales_orders.tap_id'
        )->where('accounts_receivable.status', '!=', 'PAID')->sum('accounts_receivable.balance_amount');

        $cashInToday = TerritoryScope::applyTapScope(
            DB::table('payments')
                ->join('accounts_receivable', 'payments.accounts_receivable_id', '=', 'accounts_receivable.id')
                ->join('sales_orders', 'accounts_receivable.sales_order_id', '=', 'sales_orders.id'),
            'sales_orders.tap_id'
        )->sum('payments.amount');

        // 2. Stock distribution across warehouse types
        $stockByWhType = TerritoryScope::applyWarehouseScope(DB::table('stock_balances'), 'stock_balances.warehouse_id')
            ->join('warehouses', 'stock_balances.warehouse_id', '=', 'warehouses.id')
            ->select('warehouses.type', DB::raw('SUM(stock_balances.quantity) as total_qty'))
            ->groupBy('warehouses.type')
            ->pluck('total_qty', 'type')
            ->toArray();

        $mainStock = $stockByWhType['MAIN'] ?? 0;
        $invStock = $stockByWhType['INVENTORY'] ?? 0;
        $salesStock = $stockByWhType['SALES'] ?? 0;

        // 3. Recent Transactions (SO, GD, AR, FP) with full DocType audit
        $recentOrders = TerritoryScope::applyTapScope(DB::table('sales_orders'), 'sales_orders.tap_id')
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
        $salesLeaderboard = TerritoryScope::applyTapScope(DB::table('sales'), 'sales.tap_id')
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
            ->orderByDesc('total_omset')
            ->take(5)
            ->get();

        // 5. Filter Dropdown Hierarki (Terfilter TerritoryScope)
        $areas = TerritoryScope::applyAreaScope(DB::table('areas')->where('status', 'ACTIVE')->orderBy('name'), 'areas.id')->get();
        $clusters = TerritoryScope::applyClusterScope(DB::table('clusters')->where('status', 'ACTIVE')->orderBy('name'), 'clusters.id')->get();
        $taps = TerritoryScope::applyTapScope(DB::table('taps')->where('status', 'ACTIVE')->orderBy('name'), 'taps.id')->get();

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

