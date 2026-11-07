<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    public function organization()
    {
        $areas = DB::table('areas')->get();
        $clusters = DB::table('clusters')->join('areas', 'clusters.area_id', '=', 'areas.id')
            ->select('clusters.*', 'areas.name as area_name')->get();
        $taps = DB::table('taps')->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->select('taps.*', 'clusters.name as cluster_name')->get();
        $sales = DB::table('sales')->join('taps', 'sales.tap_id', '=', 'taps.id')
            ->select('sales.*', 'taps.name as tap_name')->get();

        return view('master.organization', compact('areas', 'clusters', 'taps', 'sales'));
    }

    public function products()
    {
        $products = DB::table('products')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select('products.*', 'product_categories.name as category_name')
            ->get();

        return view('master.products', compact('products'));
    }

    public function warehouses()
    {
        $warehouses = DB::table('warehouses')
            ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
            ->leftJoin('sales', 'warehouses.sales_id', '=', 'sales.id')
            ->select('warehouses.*', 'taps.name as tap_name', 'sales.name as sales_name')
            ->get();

        return view('master.warehouses', compact('warehouses'));
    }
}

