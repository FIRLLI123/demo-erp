<?php

namespace App\Http\Controllers;

use App\Services\TerritoryScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    public function organization()
    {
        // Ambil data sesuai cakupan wilayah & gudang user (user_has_areas_clusters_taps_warehouses)
        $areas = TerritoryScope::applyAreaScope(
            DB::table('areas')->orderBy('id'),
            'areas.id'
        )->get();

        $clusters = TerritoryScope::applyClusterScope(
            DB::table('clusters')
                ->join('areas', 'clusters.area_id', '=', 'areas.id')
                ->select('clusters.*', 'areas.name as area_name', 'areas.code as area_code')
                ->orderBy('clusters.id'),
            'clusters.id'
        )->get();

        $taps = TerritoryScope::applyTapScope(
            DB::table('taps')
                ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                ->join('areas', 'clusters.area_id', '=', 'areas.id')
                ->select(
                    'taps.*',
                    'clusters.name as cluster_name',
                    'clusters.code as cluster_code',
                    'clusters.area_id',
                    'areas.name as area_name',
                    'areas.code as area_code'
                )
                ->orderBy('taps.id'),
            'taps.id'
        )->get();

        $warehouses = TerritoryScope::applyWarehouseScope(
            DB::table('warehouses')
                ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
                ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                ->join('areas', 'clusters.area_id', '=', 'areas.id')
                ->leftJoin('sales', 'warehouses.sales_id', '=', 'sales.id')
                ->select(
                    'warehouses.*',
                    'taps.name as tap_name',
                    'taps.code as tap_code',
                    'taps.cluster_id',
                    'clusters.name as cluster_name',
                    'clusters.code as cluster_code',
                    'clusters.area_id',
                    'areas.name as area_name',
                    'areas.code as area_code',
                    'sales.name as sales_name',
                    'sales.code as sales_code'
                )
                ->orderBy('warehouses.id'),
            'warehouses.id'
        )->get();

        $salesList = TerritoryScope::applyTapScope(
            DB::table('sales')->where('status', 'ACTIVE')->orderBy('name'),
            'sales.tap_id'
        )->get();

        return view('master.organization', compact('areas', 'clusters', 'taps', 'warehouses', 'salesList'));
    }

    public function storeWarehouse(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code',
            'name' => 'required|string|max:150',
            'type' => 'required|in:MAIN,INVENTORY,SALES',
            'tap_id' => 'required|exists:taps,id',
            'sales_id' => 'nullable|required_if:type,SALES|exists:sales,id',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        if (!TerritoryScope::canAccessTap((int) $request->tap_id)) {
            return redirect()->route('master.organization', ['tab' => 'warehouses'])
                ->with('error', 'Akses ditolak: Anda tidak memiliki otorisasi wilayah untuk TAP tersebut.');
        }

        DB::table('warehouses')->insert([
            'code' => strtoupper(trim($request->code)),
            'name' => trim($request->name),
            'type' => $request->type,
            'tap_id' => $request->tap_id,
            'sales_id' => $request->type === 'SALES' ? $request->sales_id : null,
            'address' => $request->address ? trim($request->address) : null,
            'status' => $request->status ?? 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.organization', ['tab' => 'warehouses'])
            ->with('success', 'Gudang baru ' . $request->name . ' (' . strtoupper(trim($request->code)) . ') berhasil ditambahkan!');
    }

    public function updateWarehouse(Request $request, $id)
    {
        $warehouse = DB::table('warehouses')->where('id', $id)->first();
        if (!$warehouse) {
            return redirect()->route('master.organization', ['tab' => 'warehouses'])
                ->with('error', 'Gudang tidak ditemukan!');
        }

        if (!TerritoryScope::canAccessWarehouse((int) $id)) {
            return redirect()->route('master.organization', ['tab' => 'warehouses'])
                ->with('error', 'Akses ditolak: Gudang ini di luar cakupan wilayah Anda.');
        }

        $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code,' . $id,
            'name' => 'required|string|max:150',
            'type' => 'required|in:MAIN,INVENTORY,SALES',
            'tap_id' => 'required|exists:taps,id',
            'sales_id' => 'nullable|required_if:type,SALES|exists:sales,id',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        if (!TerritoryScope::canAccessTap((int) $request->tap_id)) {
            return redirect()->route('master.organization', ['tab' => 'warehouses'])
                ->with('error', 'Akses ditolak: Anda tidak memiliki otorisasi untuk TAP tujuan.');
        }

        DB::table('warehouses')->where('id', $id)->update([
            'code' => strtoupper(trim($request->code)),
            'name' => trim($request->name),
            'type' => $request->type,
            'tap_id' => $request->tap_id,
            'sales_id' => $request->type === 'SALES' ? $request->sales_id : null,
            'address' => $request->address ? trim($request->address) : null,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.organization', ['tab' => 'warehouses'])
            ->with('success', 'Data gudang ' . $request->name . ' berhasil diperbarui!');
    }

    public function products()
    {
        $iccidEnabled = (bool) config('erp.enable_iccid', false);

        $products = DB::table('products')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select(
                'products.*',
                'product_categories.name as category_name',
                'product_categories.code as category_code'
            )
            ->orderBy('products.id', 'desc')
            ->get();

        $productTypes = DB::table('product_categories')
            ->leftJoin('products', 'product_categories.id', '=', 'products.category_id')
            ->select(
                'product_categories.*',
                DB::raw('count(products.id) as products_count')
            )
            ->groupBy(
                'product_categories.id',
                'product_categories.name',
                'product_categories.code',
                'product_categories.description',
                'product_categories.status',
                'product_categories.created_at',
                'product_categories.updated_at'
            )
            ->orderBy('product_categories.name')
            ->get();

        $scope = TerritoryScope::for();

        $priceQuery = DB::table('product_prices')
            ->join('products', 'product_prices.product_id', '=', 'products.id')
            ->leftJoin('areas', 'product_prices.area_id', '=', 'areas.id')
            ->leftJoin('clusters', 'product_prices.cluster_id', '=', 'clusters.id')
            ->leftJoin('taps', 'product_prices.tap_id', '=', 'taps.id')
            ->select(
                'product_prices.*',
                'products.sku as product_sku',
                'products.name as product_name',
                'products.cost_price as default_cost_price',
                'products.selling_price as default_selling_price',
                'areas.name as area_name',
                'clusters.name as cluster_name',
                'clusters.area_id as cluster_area_id',
                'taps.name as tap_name',
                'taps.cluster_id as tap_cluster_id'
            );

        if (!$scope['is_full_access']) {
            $priceQuery->where(function ($q) use ($scope) {
                $hasAny = false;
                if (!empty($scope['tap_ids'])) {
                    $q->orWhereIn('product_prices.tap_id', $scope['tap_ids']);
                    $hasAny = true;
                }
                if (!empty($scope['cluster_ids'])) {
                    $q->orWhere(function ($sub) use ($scope) {
                        $sub->whereNull('product_prices.tap_id')
                            ->whereIn('product_prices.cluster_id', $scope['cluster_ids']);
                    });
                    $hasAny = true;
                }
                if (!empty($scope['area_ids'])) {
                    $q->orWhere(function ($sub) use ($scope) {
                        $sub->whereNull('product_prices.tap_id')
                            ->whereNull('product_prices.cluster_id')
                            ->whereIn('product_prices.area_id', $scope['area_ids']);
                    });
                    $hasAny = true;
                }
                if (!$hasAny) {
                    $q->whereRaw('1 = 0');
                }
            });
        }

        $productPrices = $priceQuery->orderBy('product_prices.id', 'desc')->get();

        $areas = TerritoryScope::applyAreaScope(
            DB::table('areas')->where('status', 'ACTIVE')->orderBy('name'),
            'areas.id'
        )->get();
        $clusters = TerritoryScope::applyClusterScope(
            DB::table('clusters')->where('status', 'ACTIVE')->orderBy('name'),
            'clusters.id'
        )->get();
        $taps = TerritoryScope::applyTapScope(
            DB::table('taps')
                ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                ->where('taps.status', 'ACTIVE')
                ->select('taps.*', 'clusters.area_id')
                ->orderBy('taps.name'),
            'taps.id'
        )->get();

        return view('master.products', compact(
            'products',
            'productTypes',
            'productPrices',
            'areas',
            'clusters',
            'taps',
            'iccidEnabled'
        ));
    }

    public function storeProduct(Request $request)
    {
        $iccidEnabled = (bool) config('erp.enable_iccid', false);

        $request->validate([
            'sku' => 'required|string|max:60|unique:products,sku',
            'item_code_telkomsel' => 'nullable|string|max:100',
            'name' => 'required|string|max:180',
            'category_id' => 'nullable|exists:product_categories,id',
            'type' => 'nullable|string|max:50',
            'denom' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:1000',
            'tracking_method' => 'nullable|in:ICCID,QUANTITY',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'uom' => 'required|string|max:20',
            'min_stock' => 'nullable|integer|min:0',
            'tax_alias' => 'nullable|string|max:150',
            'activation_date' => 'nullable|date',
            'expired_date' => 'nullable|date',
        ]);

        // Ambil kode tipe dari product_categories jika category_id dipilih
        $typeCode = $request->type ?: 'SA';
        if ($request->filled('category_id')) {
            $cat = DB::table('product_categories')->where('id', $request->category_id)->first();
            if ($cat) {
                $typeCode = $cat->code;
            }
        }

        // Kunci ICCID sesuai feature flag .env (ERP_ENABLE_ICCID)
        $trackingMethod = $iccidEnabled
            ? ($request->input('tracking_method', 'QUANTITY') ?: 'QUANTITY')
            : 'QUANTITY';

        DB::table('products')->insert([
            'category_id' => $request->category_id,
            'sku' => strtoupper(trim($request->sku)),
            'item_code_telkomsel' => $request->item_code_telkomsel,
            'name' => trim($request->name),
            'type' => $typeCode,
            'denom' => $request->denom,
            'description' => $request->description,
            'tracking_method' => $trackingMethod,
            'cost_price' => $request->cost_price,
            'selling_price' => $request->selling_price,
            'special_price' => $request->special_price ?? 0,
            'uom' => strtoupper(trim($request->uom)),
            'is_consignment' => $request->boolean('is_consignment'),
            'is_virtual' => $request->boolean('is_virtual'),
            'is_ppn' => $request->has('is_ppn') ? $request->boolean('is_ppn') : null,
            'tax_alias' => $request->tax_alias,
            'min_stock' => $request->min_stock ?? 0,
            'activation_date' => $request->activation_date ?: null,
            'expired_date' => $request->expired_date ?: null,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.products')->with('success', "Produk [{$request->sku}] {$request->name} berhasil ditambahkan.");
    }

    public function updateProduct(Request $request, $id)
    {
        $iccidEnabled = (bool) config('erp.enable_iccid', false);

        $request->validate([
            'sku' => "required|string|max:60|unique:products,sku,{$id}",
            'item_code_telkomsel' => 'nullable|string|max:100',
            'name' => 'required|string|max:180',
            'category_id' => 'nullable|exists:product_categories,id',
            'type' => 'nullable|string|max:50',
            'denom' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:1000',
            'tracking_method' => 'nullable|in:ICCID,QUANTITY',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'uom' => 'required|string|max:20',
            'min_stock' => 'nullable|integer|min:0',
            'tax_alias' => 'nullable|string|max:150',
            'activation_date' => 'nullable|date',
            'expired_date' => 'nullable|date',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $typeCode = $request->type ?: 'SA';
        if ($request->filled('category_id')) {
            $cat = DB::table('product_categories')->where('id', $request->category_id)->first();
            if ($cat) {
                $typeCode = $cat->code;
            }
        }

        $updateData = [
            'category_id' => $request->category_id,
            'sku' => strtoupper(trim($request->sku)),
            'item_code_telkomsel' => $request->item_code_telkomsel,
            'name' => trim($request->name),
            'type' => $typeCode,
            'denom' => $request->denom,
            'description' => $request->description,
            'cost_price' => $request->cost_price,
            'selling_price' => $request->selling_price,
            'special_price' => $request->special_price ?? 0,
            'uom' => strtoupper(trim($request->uom)),
            'is_consignment' => $request->boolean('is_consignment'),
            'is_virtual' => $request->boolean('is_virtual'),
            'is_ppn' => $request->has('is_ppn') ? $request->boolean('is_ppn') : null,
            'tax_alias' => $request->tax_alias,
            'min_stock' => $request->min_stock ?? 0,
            'activation_date' => $request->activation_date ?: null,
            'expired_date' => $request->expired_date ?: null,
            'status' => $request->status,
            'updated_at' => now(),
        ];

        // Kunci ICCID jika feature flag OFF
        $updateData['tracking_method'] = $iccidEnabled
            ? ($request->input('tracking_method', 'QUANTITY') ?: 'QUANTITY')
            : 'QUANTITY';

        DB::table('products')->where('id', $id)->update($updateData);

        return redirect()->route('master.products')->with('success', "Produk [{$request->sku}] {$request->name} berhasil diperbarui.");
    }

    public function storeProductType(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:30|unique:product_categories,code',
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
        ]);

        DB::table('product_categories')->insert([
            'code' => strtoupper(trim($request->code)),
            'name' => trim($request->name),
            'description' => $request->description,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.products', ['tab' => 'types'])->with('success', "Tipe Produk [{$request->code}] {$request->name} berhasil ditambahkan.");
    }

    public function updateProductType(Request $request, $id)
    {
        $request->validate([
            'code' => "required|string|max:30|unique:product_categories,code,{$id}",
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::table('product_categories')->where('id', $id)->update([
            'code' => strtoupper(trim($request->code)),
            'name' => trim($request->name),
            'description' => $request->description,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.products', ['tab' => 'types'])->with('success', "Tipe Produk [{$request->code}] berhasil diperbarui.");
    }

    public function storeProductPrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'area_id' => 'nullable|exists:areas,id',
            'cluster_id' => 'nullable|exists:clusters,id',
            'tap_id' => 'nullable|exists:taps,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
        ]);

        if (!$request->filled('area_id') && !$request->filled('cluster_id') && !$request->filled('tap_id')) {
            return redirect()->route('master.products', ['tab' => 'prices'])->with('error', 'Pilih minimal satu wilayah (Area, Cluster, atau TAP).');
        }

        $areaId = $request->area_id ?: null;
        $clusterId = $request->cluster_id ?: null;
        $tapId = $request->tap_id ?: null;

        // Auto-lengkapi parent wilayah agar konsisten
        if ($tapId && !$clusterId) {
            $tap = DB::table('taps')->where('id', $tapId)->first();
            if ($tap) $clusterId = $tap->cluster_id;
        }
        if ($clusterId && !$areaId) {
            $cls = DB::table('clusters')->where('id', $clusterId)->first();
            if ($cls) $areaId = $cls->area_id;
        }

        if (!TerritoryScope::canAccessTerritory($areaId ? (int) $areaId : null, $clusterId ? (int) $clusterId : null, $tapId ? (int) $tapId : null)) {
            return redirect()->route('master.products', ['tab' => 'prices'])->with('error', 'Akses ditolak: Wilayah yang dipilih di luar otorisasi Anda.');
        }

        DB::table('product_prices')->insert([
            'product_id' => $request->product_id,
            'area_id' => $areaId,
            'cluster_id' => $clusterId,
            'tap_id' => $tapId,
            'cost_price' => $request->cost_price,
            'selling_price' => $request->selling_price,
            'special_price' => $request->special_price ?? 0,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.products', ['tab' => 'prices'])->with('success', 'Setting harga wilayah berhasil ditambahkan.');
    }

    public function updateProductPrice(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'area_id' => 'nullable|exists:areas,id',
            'cluster_id' => 'nullable|exists:clusters,id',
            'tap_id' => 'nullable|exists:taps,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $areaId = $request->area_id ?: null;
        $clusterId = $request->cluster_id ?: null;
        $tapId = $request->tap_id ?: null;

        if ($tapId && !$clusterId) {
            $tap = DB::table('taps')->where('id', $tapId)->first();
            if ($tap) $clusterId = $tap->cluster_id;
        }
        if ($clusterId && !$areaId) {
            $cls = DB::table('clusters')->where('id', $clusterId)->first();
            if ($cls) $areaId = $cls->area_id;
        }

        if (!TerritoryScope::canAccessTerritory($areaId ? (int) $areaId : null, $clusterId ? (int) $clusterId : null, $tapId ? (int) $tapId : null)) {
            return redirect()->route('master.products', ['tab' => 'prices'])->with('error', 'Akses ditolak: Wilayah yang dipilih di luar otorisasi Anda.');
        }

        DB::table('product_prices')->where('id', $id)->update([
            'product_id' => $request->product_id,
            'area_id' => $areaId,
            'cluster_id' => $clusterId,
            'tap_id' => $tapId,
            'cost_price' => $request->cost_price,
            'selling_price' => $request->selling_price,
            'special_price' => $request->special_price ?? 0,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.products', ['tab' => 'prices'])->with('success', 'Setting harga wilayah berhasil diperbarui.');
    }

    public function deleteProductPrice($id)
    {
        $price = DB::table('product_prices')->where('id', $id)->first();
        if ($price && !TerritoryScope::canAccessTerritory(
            $price->area_id ? (int) $price->area_id : null,
            $price->cluster_id ? (int) $price->cluster_id : null,
            $price->tap_id ? (int) $price->tap_id : null
        )) {
            return redirect()->route('master.products', ['tab' => 'prices'])->with('error', 'Akses ditolak: Setting harga ini berada di luar wilayah Anda.');
        }

        DB::table('product_prices')->where('id', $id)->delete();
        return redirect()->route('master.products', ['tab' => 'prices'])->with('success', 'Setting harga wilayah berhasil dihapus.');
    }

    /**
     * Master Program Harga (Menu Terpisah - 2 Tab: Daftar Program & Setting Harga Item Program)
     */
    public function programs()
    {
        $scope = TerritoryScope::for();

        $programQuery = DB::table('programs')
            ->leftJoin('areas', 'programs.area_id', '=', 'areas.id')
            ->leftJoin('clusters', 'programs.cluster_id', '=', 'clusters.id')
            ->leftJoin('taps', 'programs.tap_id', '=', 'taps.id')
            ->leftJoin('program_items', 'programs.id', '=', 'program_items.program_id')
            ->select(
                'programs.*',
                'areas.name as area_name',
                'clusters.name as cluster_name',
                'taps.name as tap_name',
                DB::raw('count(program_items.id) as items_count')
            );

        // User non-full-access: lihat program Nasional (tanpa wilayah) + program di wilayahnya sendiri
        if (!$scope['is_full_access']) {
            $programQuery->where(function ($q) use ($scope) {
                $q->where(function ($nat) {
                    $nat->whereNull('programs.area_id')
                        ->whereNull('programs.cluster_id')
                        ->whereNull('programs.tap_id');
                });
                if (!empty($scope['tap_ids'])) {
                    $q->orWhereIn('programs.tap_id', $scope['tap_ids']);
                }
                if (!empty($scope['cluster_ids'])) {
                    $q->orWhere(function ($sub) use ($scope) {
                        $sub->whereNull('programs.tap_id')
                            ->whereIn('programs.cluster_id', $scope['cluster_ids']);
                    });
                }
                if (!empty($scope['area_ids'])) {
                    $q->orWhere(function ($sub) use ($scope) {
                        $sub->whereNull('programs.tap_id')
                            ->whereNull('programs.cluster_id')
                            ->whereIn('programs.area_id', $scope['area_ids']);
                    });
                }
            });
        }

        $programs = $programQuery
            ->groupBy(
                'programs.id',
                'programs.code',
                'programs.name',
                'programs.area_id',
                'programs.cluster_id',
                'programs.tap_id',
                'programs.start_date',
                'programs.end_date',
                'programs.description',
                'programs.status',
                'programs.created_at',
                'programs.updated_at',
                'areas.name',
                'clusters.name',
                'taps.name'
            )
            ->orderBy('programs.id', 'desc')
            ->get();

        $visibleProgramIds = $programs->pluck('id')->all();

        $programItems = !empty($visibleProgramIds)
            ? DB::table('program_items')
                ->join('programs', 'program_items.program_id', '=', 'programs.id')
                ->join('products', 'program_items.product_id', '=', 'products.id')
                ->whereIn('program_items.program_id', $visibleProgramIds)
                ->select(
                    'program_items.*',
                    'programs.code as program_code',
                    'programs.name as program_name',
                    'programs.cluster_id as program_cluster_id',
                    'programs.tap_id as program_tap_id',
                    'programs.area_id as program_area_id',
                    'products.sku as product_sku',
                    'products.name as product_name',
                    'products.selling_price as default_selling_price',
                    'products.cost_price as default_cost_price'
                )
                ->orderBy('program_items.id', 'desc')
                ->get()
            : collect([]);

        // Hitung harga normal efektif sesuai wilayah program untuk perbandingan langsung di UI
        $allClusterPrices = DB::table('product_prices')->where('status', 'ACTIVE')->get();
        foreach ($programItems as $item) {
            $normalPrice = (float) $item->default_selling_price;
            $override = null;
            if ($item->program_tap_id) {
                $override = $allClusterPrices->first(fn($p) => $p->product_id == $item->product_id && $p->tap_id == $item->program_tap_id);
            }
            if (!$override && $item->program_cluster_id) {
                $override = $allClusterPrices->first(fn($p) => $p->product_id == $item->product_id && $p->cluster_id == $item->program_cluster_id && !$p->tap_id);
            }
            if (!$override && $item->program_area_id) {
                $override = $allClusterPrices->first(fn($p) => $p->product_id == $item->product_id && $p->area_id == $item->program_area_id && !$p->cluster_id && !$p->tap_id);
            }
            if ($override) {
                $normalPrice = (float) $override->selling_price;
            }
            $item->normal_selling_price = $normalPrice;

            if ($item->price_mode === 'FIXED') {
                $item->final_price = (float) $item->price_value;
            } elseif ($item->price_mode === 'DISCOUNT_NOMINAL') {
                $item->final_price = max(0, $normalPrice - (float) $item->price_value);
            } else {
                $item->final_price = $normalPrice + (float) $item->price_value;
            }
            $item->price_diff = $item->final_price - $normalPrice;
        }

        $products = DB::table('products')->where('status', 'ACTIVE')->orderBy('name')->get();
        $areas = TerritoryScope::applyAreaScope(
            DB::table('areas')->where('status', 'ACTIVE')->orderBy('name'),
            'areas.id'
        )->get();
        $clusters = TerritoryScope::applyClusterScope(
            DB::table('clusters')->where('status', 'ACTIVE')->orderBy('name'),
            'clusters.id'
        )->get();
        $taps = TerritoryScope::applyTapScope(
            DB::table('taps')
                ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                ->where('taps.status', 'ACTIVE')
                ->select('taps.*', 'clusters.area_id')
                ->orderBy('taps.name'),
            'taps.id'
        )->get();

        return view('master.programs', compact('programs', 'programItems', 'products', 'areas', 'clusters', 'taps'));
    }

    public function storeProgram(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:30|unique:programs,code',
            'name' => 'required|string|max:150',
            'area_id' => 'nullable|exists:areas,id',
            'cluster_id' => 'nullable|exists:clusters,id',
            'tap_id' => 'nullable|exists:taps,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:1000',
        ]);

        $areaId = $request->area_id ?: null;
        $clusterId = $request->cluster_id ?: null;
        $tapId = $request->tap_id ?: null;

        if ($tapId && !$clusterId) {
            $tap = DB::table('taps')->where('id', $tapId)->first();
            if ($tap) $clusterId = $tap->cluster_id;
        }
        if ($clusterId && !$areaId) {
            $cls = DB::table('clusters')->where('id', $clusterId)->first();
            if ($cls) $areaId = $cls->area_id;
        }

        $scope = TerritoryScope::for();
        if (!$scope['is_full_access']) {
            if (!$areaId && !$clusterId && !$tapId) {
                return redirect()->route('master.programs')->with('error', 'Hanya Super Admin / Full Access yang dapat membuat program Nasional. Silakan pilih wilayah Anda.');
            }
            if (!TerritoryScope::canAccessTerritory($areaId ? (int) $areaId : null, $clusterId ? (int) $clusterId : null, $tapId ? (int) $tapId : null)) {
                return redirect()->route('master.programs')->with('error', 'Akses ditolak: Wilayah program di luar cakupan otorisasi Anda.');
            }
        }

        DB::table('programs')->insert([
            'code' => strtoupper(trim($request->code)),
            'name' => trim($request->name),
            'area_id' => $areaId,
            'cluster_id' => $clusterId,
            'tap_id' => $tapId,
            'start_date' => $request->start_date ?: null,
            'end_date' => $request->end_date ?: null,
            'description' => $request->description,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.programs')->with('success', "Program [{$request->code}] {$request->name} berhasil ditambahkan.");
    }

    public function updateProgram(Request $request, $id)
    {
        $request->validate([
            'code' => "required|string|max:30|unique:programs,code,{$id}",
            'name' => 'required|string|max:150',
            'area_id' => 'nullable|exists:areas,id',
            'cluster_id' => 'nullable|exists:clusters,id',
            'tap_id' => 'nullable|exists:taps,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $areaId = $request->area_id ?: null;
        $clusterId = $request->cluster_id ?: null;
        $tapId = $request->tap_id ?: null;

        if ($tapId && !$clusterId) {
            $tap = DB::table('taps')->where('id', $tapId)->first();
            if ($tap) $clusterId = $tap->cluster_id;
        }
        if ($clusterId && !$areaId) {
            $cls = DB::table('clusters')->where('id', $clusterId)->first();
            if ($cls) $areaId = $cls->area_id;
        }

        $scope = TerritoryScope::for();
        if (!$scope['is_full_access']) {
            if (!$areaId && !$clusterId && !$tapId) {
                return redirect()->route('master.programs')->with('error', 'Hanya Super Admin / Full Access yang dapat mengubah program Nasional.');
            }
            if (!TerritoryScope::canAccessTerritory($areaId ? (int) $areaId : null, $clusterId ? (int) $clusterId : null, $tapId ? (int) $tapId : null)) {
                return redirect()->route('master.programs')->with('error', 'Akses ditolak: Wilayah program di luar cakupan otorisasi Anda.');
            }
        }

        DB::table('programs')->where('id', $id)->update([
            'code' => strtoupper(trim($request->code)),
            'name' => trim($request->name),
            'area_id' => $areaId,
            'cluster_id' => $clusterId,
            'tap_id' => $tapId,
            'start_date' => $request->start_date ?: null,
            'end_date' => $request->end_date ?: null,
            'description' => $request->description,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.programs')->with('success', "Program [{$request->code}] berhasil diperbarui.");
    }

    public function storeProgramItem(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'product_id' => 'required|exists:products,id',
            'price_mode' => 'required|in:FIXED,DISCOUNT_NOMINAL,MARKUP_NOMINAL',
            'price_value' => 'required|numeric|min:0',
            'min_qty' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $exists = DB::table('program_items')
            ->where('program_id', $request->program_id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return redirect()->route('master.programs', ['tab' => 'items', 'program_id' => $request->program_id])
                ->with('error', 'Produk tersebut sudah terdaftar di program ini. Silakan edit baris yang sudah ada.');
        }

        DB::table('program_items')->insert([
            'program_id' => $request->program_id,
            'product_id' => $request->product_id,
            'price_mode' => $request->price_mode,
            'price_value' => $request->price_value,
            'min_qty' => $request->min_qty ?? 1,
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.programs', ['tab' => 'items', 'program_id' => $request->program_id])
            ->with('success', 'Item harga program berhasil ditambahkan.');
    }

    public function updateProgramItem(Request $request, $id)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'product_id' => 'required|exists:products,id',
            'price_mode' => 'required|in:FIXED,DISCOUNT_NOMINAL,MARKUP_NOMINAL',
            'price_value' => 'required|numeric|min:0',
            'min_qty' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::table('program_items')->where('id', $id)->update([
            'product_id' => $request->product_id,
            'price_mode' => $request->price_mode,
            'price_value' => $request->price_value,
            'min_qty' => $request->min_qty ?? 1,
            'notes' => $request->notes,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.programs', ['tab' => 'items', 'program_id' => $request->program_id])
            ->with('success', 'Item harga program berhasil diperbarui.');
    }

    public function deleteProgramItem(Request $request, $id)
    {
        $item = DB::table('program_items')->where('id', $id)->first();
        $programId = $item ? $item->program_id : $request->input('program_id');
        DB::table('program_items')->where('id', $id)->delete();

        return redirect()->route('master.programs', ['tab' => 'items', 'program_id' => $programId])
            ->with('success', 'Item harga program berhasil dihapus.');
    }

    public function warehouses()
    {
        $warehouses = TerritoryScope::applyWarehouseScope(
            DB::table('warehouses')
                ->join('taps', 'warehouses.tap_id', '=', 'taps.id')
                ->leftJoin('sales', 'warehouses.sales_id', '=', 'sales.id')
                ->select('warehouses.*', 'taps.name as tap_name', 'sales.name as sales_name'),
            'warehouses.id'
        )->get();

        return view('master.warehouses', compact('warehouses'));
    }

    /**
     * Master Tim Sales Kanvaser (PT Wahana Putra Yudha)
     */
    public function sales()
    {
        $salesList = TerritoryScope::applyTapScope(
            DB::table('sales')
                ->join('taps', 'sales.tap_id', '=', 'taps.id')
                ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                ->join('areas', 'clusters.area_id', '=', 'areas.id')
                ->leftJoin('warehouses', function ($join) {
                    $join->on('sales.id', '=', 'warehouses.sales_id')
                         ->where('warehouses.type', '=', 'SALES');
                })
                ->select(
                    'sales.*',
                    'taps.name as tap_name',
                    'taps.code as tap_code',
                    'taps.cluster_id',
                    'clusters.name as cluster_name',
                    'clusters.area_id',
                    'areas.name as area_name',
                    'warehouses.name as warehouse_name',
                    'warehouses.id as warehouse_id'
                )
                ->orderBy('sales.id'),
            'sales.tap_id'
        )->get();

        // Hitung total order yang ditangani masing-masing sales
        $soCounts = DB::table('sales_orders')
            ->select('sales_id', DB::raw('count(*) as total_orders'), DB::raw('sum(grand_total) as total_sales_amount'))
            ->groupBy('sales_id')
            ->get()
            ->keyBy('sales_id');

        foreach ($salesList as $s) {
            $s->total_orders = $soCounts[$s->id]->total_orders ?? 0;
            $s->total_sales_amount = $soCounts[$s->id]->total_sales_amount ?? 0;
        }

        $areas = TerritoryScope::applyAreaScope(
            DB::table('areas')->orderBy('id'),
            'areas.id'
        )->get();
        $clusters = TerritoryScope::applyClusterScope(
            DB::table('clusters')->orderBy('name'),
            'clusters.id'
        )->get();
        $taps = TerritoryScope::applyTapScope(
            DB::table('taps')
                ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                ->where('taps.status', 'ACTIVE')
                ->select('taps.*', 'clusters.area_id')
                ->orderBy('taps.name'),
            'taps.id'
        )->get();
        $salesTypes = DB::table('sales_types')->orderBy('id')->get();
        $salesLevels = DB::table('sales_levels')->orderBy('id')->get();

        return view('master.sales', compact('salesList', 'areas', 'clusters', 'taps', 'salesTypes', 'salesLevels'));
    }

    public function storeSalesType(Request $request)
    {
        $request->validate([
            'type_name' => 'required|string|max:50|unique:sales_types,type_name',
            'type_desc' => 'nullable|string|max:150',
        ]);

        DB::table('sales_types')->insert([
            'type_name' => trim($request->type_name),
            'type_desc' => $request->type_desc ? trim($request->type_desc) : null,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.sales-team', ['tab' => 'types'])
            ->with('success', 'Tipe sales baru "' . $request->type_name . '" berhasil ditambahkan!');
    }

    public function updateSalesType(Request $request, $id)
    {
        $request->validate([
            'type_name' => 'required|string|max:50|unique:sales_types,type_name,' . $id,
            'type_desc' => 'nullable|string|max:150',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::table('sales_types')->where('id', $id)->update([
            'type_name' => trim($request->type_name),
            'type_desc' => $request->type_desc ? trim($request->type_desc) : null,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.sales-team', ['tab' => 'types'])
            ->with('success', 'Tipe sales "' . $request->type_name . '" berhasil diperbarui!');
    }

    public function deleteSalesType($id)
    {
        $type = DB::table('sales_types')->where('id', $id)->first();
        if ($type) {
            $inUse = DB::table('sales')->where('sales_type', $type->type_name)->count();
            if ($inUse > 0) {
                return redirect()->route('master.sales-team', ['tab' => 'types'])
                    ->with('error', 'Tipe sales "' . $type->type_name . '" tidak dapat dihapus karena sedang digunakan oleh ' . $inUse . ' sales force.');
            }
            DB::table('sales_types')->where('id', $id)->delete();
        }

        return redirect()->route('master.sales-team', ['tab' => 'types'])
            ->with('success', 'Tipe sales berhasil dihapus!');
    }

    public function storeSalesLevel(Request $request)
    {
        $request->validate([
            'level_name' => 'required|string|max:30|unique:sales_levels,level_name',
            'level_desc' => 'nullable|string|max:100',
        ]);

        DB::table('sales_levels')->insert([
            'level_name' => strtoupper(trim($request->level_name)),
            'level_desc' => $request->level_desc ? strtoupper(trim($request->level_desc)) : null,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.sales-team', ['tab' => 'levels'])
            ->with('success', 'Level sales baru "' . strtoupper(trim($request->level_name)) . '" berhasil ditambahkan!');
    }

    public function updateSalesLevel(Request $request, $id)
    {
        $request->validate([
            'level_name' => 'required|string|max:30|unique:sales_levels,level_name,' . $id,
            'level_desc' => 'nullable|string|max:100',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::table('sales_levels')->where('id', $id)->update([
            'level_name' => strtoupper(trim($request->level_name)),
            'level_desc' => $request->level_desc ? strtoupper(trim($request->level_desc)) : null,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.sales-team', ['tab' => 'levels'])
            ->with('success', 'Level sales "' . strtoupper(trim($request->level_name)) . '" berhasil diperbarui!');
    }

    public function deleteSalesLevel($id)
    {
        $level = DB::table('sales_levels')->where('id', $id)->first();
        if ($level) {
            $inUse = DB::table('sales')->where('sales_level', $level->level_name)->count();
            if ($inUse > 0) {
                return redirect()->route('master.sales-team', ['tab' => 'levels'])
                    ->with('error', 'Level sales "' . $level->level_name . '" tidak dapat dihapus karena sedang digunakan oleh ' . $inUse . ' sales force.');
            }
            DB::table('sales_levels')->where('id', $id)->delete();
        }

        return redirect()->route('master.sales-team', ['tab' => 'levels'])
            ->with('success', 'Level sales berhasil dihapus!');
    }

    public function storeSales(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'tap_id' => 'required|exists:taps,id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'sales_limit' => 'nullable|numeric|min:0',
            'sales_type' => 'nullable|string|max:50',
            'sales_level' => 'nullable|string|max:30',
            'bakulku_phone' => 'nullable|string|max:20',
            'ngrs_type' => 'nullable|string|max:30',
        ]);

        if (!TerritoryScope::canAccessTap((int) $request->tap_id)) {
            return redirect()->route('master.sales-team')->with('error', 'Akses ditolak: Anda tidak memiliki otorisasi wilayah untuk TAP tersebut.');
        }

        $nextId = DB::table('sales')->count() + 1;
        $salesCode = 'SLS-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        $limit = $request->sales_limit ?? 20000000;

        DB::beginTransaction();
        try {
            $salesId = DB::table('sales')->insertGetId([
                'tap_id' => $request->tap_id,
                'code' => $salesCode,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ?? '0812' . rand(10000000, 99999999),
                'address' => $request->address,
                'sales_limit' => $limit,
                'sisa_limit' => $limit,
                'sales_type' => $request->sales_type ?? 'CANVASSER',
                'sales_level' => $request->sales_level ?? 'SF',
                'bakulku_phone' => $request->bakulku_phone,
                'ngrs_type' => $request->ngrs_type ?? 'Digipos',
                'hadir' => $request->has('hadir') ? (bool)$request->hadir : true,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Buat otomatis Gudang Kanvas Sales Motor untuk sales ini
            DB::table('warehouses')->insert([
                'tap_id' => $request->tap_id,
                'sales_id' => $salesId,
                'code' => 'WHS-' . str_pad($salesId, 2, '0', STR_PAD_LEFT),
                'name' => "Gudang Kanvas Sales - {$request->name}",
                'type' => 'SALES',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('master.sales-team')->with('success', "Sales [{$salesCode}] {$request->name} berhasil didaftarkan dan Gudang Kanvas Motor otomatis dibentuk.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mendaftarkan sales: ' . $e->getMessage());
        }
    }

    public function updateSales(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'tap_id' => 'required|exists:taps,id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'sales_limit' => 'nullable|numeric|min:0',
            'sales_type' => 'nullable|string|max:50',
            'sales_level' => 'nullable|string|max:30',
            'bakulku_phone' => 'nullable|string|max:20',
            'ngrs_type' => 'nullable|string|max:30',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        if (!TerritoryScope::canAccessTap((int) $request->tap_id)) {
            return redirect()->route('master.sales-team')->with('error', 'Akses ditolak: Anda tidak memiliki otorisasi wilayah untuk TAP tersebut.');
        }

        $updateData = [
            'name' => $request->name,
            'tap_id' => $request->tap_id,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'sales_type' => $request->sales_type ?? 'CANVASSER',
            'sales_level' => $request->sales_level ?? 'SF',
            'bakulku_phone' => $request->bakulku_phone,
            'ngrs_type' => $request->ngrs_type ?? 'Digipos',
            'hadir' => $request->has('hadir') ? (bool)$request->hadir : true,
            'status' => $request->status,
            'updated_at' => now(),
        ];

        if ($request->filled('sales_limit')) {
            $updateData['sales_limit'] = $request->sales_limit;
            $current = DB::table('sales')->where('id', $id)->first();
            if ($current && $current->sisa_limit > $request->sales_limit) {
                $updateData['sisa_limit'] = $request->sales_limit;
            }
        }

        DB::table('sales')->where('id', $id)->update($updateData);

        return redirect()->route('master.sales-team')->with('success', "Data Sales [{$request->name}] berhasil diperbarui.");
    }

    /**
     * Master Pelanggan / Toko Outlet (PT Wahana Putra Yudha)
     */
    public function customers(Request $request)
    {
        $query = DB::table('customers')
            ->leftJoin('taps', 'customers.tap_id', '=', 'taps.id')
            ->leftJoin('clusters', 'taps.cluster_id', '=', 'clusters.id')
            ->leftJoin('areas', 'clusters.area_id', '=', 'areas.id')
            ->leftJoin('sales', 'customers.sales_id', '=', 'sales.id')
            ->select(
                'customers.*',
                'taps.name as tap_name',
                'clusters.id as cluster_id',
                'clusters.name as cluster_name',
                'areas.id as area_id',
                'areas.name as area_name',
                'sales.name as sales_name',
                'sales.code as sales_code'
            );

        // Batasi data customer sesuai cakupan wilayah user
        TerritoryScope::applyTapScope($query, 'customers.tap_id');

        if ($request->filled('area_id')) {
            $query->where('clusters.area_id', $request->area_id);
        }
        if ($request->filled('cluster_id')) {
            $query->where('taps.cluster_id', $request->cluster_id);
        }
        if ($request->filled('tap_id')) {
            $query->where('customers.tap_id', $request->tap_id);
        }
        if ($request->filled('type') && $request->type !== 'ALL') {
            $query->where('customers.type', $request->type);
        }
        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('customers.name', 'like', "%{$q}%")
                    ->orWhere('customers.code', 'like', "%{$q}%")
                    ->orWhere('customers.digipos_id', 'like', "%{$q}%")
                    ->orWhere('customers.nomor_rs', 'like', "%{$q}%")
                    ->orWhere('customers.phone', 'like', "%{$q}%")
                    ->orWhere('customers.customer_pic', 'like', "%{$q}%")
                    ->orWhere('customers.address', 'like', "%{$q}%")
                    ->orWhere('sales.name', 'like', "%{$q}%")
                    ->orWhere('taps.name', 'like', "%{$q}%")
                    ->orWhereExists(function ($rsSub) use ($q) {
                        $rsSub->select(DB::raw(1))
                            ->from('customer_rs_numbers')
                            ->whereColumn('customer_rs_numbers.customer_id', 'customers.id')
                            ->where('customer_rs_numbers.rs_number', 'like', "%{$q}%");
                    });
            });
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100]) ? (int) $request->input('per_page') : 10;
        $customers = $query->orderBy('customers.id')->paginate($perPage)->withQueryString();

        $customerIds = $customers->pluck('id')->all();

        // Hitung outstanding piutang hanya untuk customer di halaman aktif
        $outstandingAr = !empty($customerIds)
            ? DB::table('accounts_receivable')
                ->whereIn('customer_id', $customerIds)
                ->where('status', '!=', 'PAID')
                ->where('balance_amount', '>', 0)
                ->select('customer_id', DB::raw('sum(balance_amount) as total_unpaid'), DB::raw('count(*) as unpaid_count'))
                ->groupBy('customer_id')
                ->get()
                ->keyBy('customer_id')
            : collect();

        // Ambil nomor RS hanya untuk customer di halaman aktif
        $allRs = !empty($customerIds)
            ? DB::table('customer_rs_numbers')
                ->whereIn('customer_id', $customerIds)
                ->orderBy('is_primary', 'desc')
                ->orderBy('id', 'asc')
                ->get()
                ->groupBy('customer_id')
            : collect();

        foreach ($customers as $c) {
            $c->unpaid_ar_amount = $outstandingAr[$c->id]->total_unpaid ?? 0;
            $c->unpaid_ar_count = $outstandingAr[$c->id]->unpaid_count ?? 0;

            $c->rs_list = $allRs[$c->id] ?? collect([]);
            $primaryRs = $c->rs_list->firstWhere('is_primary', 1);
            $c->primary_rs_number = $primaryRs ? $primaryRs->rs_number : ($c->nomor_rs ?? '-');
            $c->primary_rs_name = $primaryRs ? $primaryRs->rs_name : 'RS Utama';
            $c->total_rs_count = $c->rs_list->count();
        }

        $areas = TerritoryScope::applyAreaScope(
            DB::table('areas')->where('status', 'ACTIVE')->orderBy('name'),
            'areas.id'
        )->get();
        $clusters = TerritoryScope::applyClusterScope(
            DB::table('clusters')->where('status', 'ACTIVE')->orderBy('name'),
            'clusters.id'
        )->get();
        $taps = TerritoryScope::applyTapScope(
            DB::table('taps')->where('status', 'ACTIVE')->orderBy('name'),
            'taps.id'
        )->get();
        $salesList = TerritoryScope::applyTapScope(
            DB::table('sales')->where('status', 'ACTIVE')->orderBy('name'),
            'sales.tap_id'
        )->get();

        return view('master.customers', compact('customers', 'areas', 'clusters', 'taps', 'salesList', 'perPage'));
    }

    /**
     * Ambil list nomor RS untuk 1 customer (AJAX)
     */
    public function getRsNumbers($customerId)
    {
        $customer = DB::table('customers')->where('id', $customerId)->first();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Outlet tidak ditemukan.'], 404);
        }

        $rsNumbers = DB::table('customer_rs_numbers')
            ->where('customer_id', $customerId)
            ->orderBy('is_primary', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'rs_numbers' => $rsNumbers,
        ]);
    }

    /**
     * Tambah Nomor RS / Chip Mkios baru ke Customer
     */
    public function storeRsNumber(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'rs_number' => 'required|string|max:30',
            'rs_name' => 'nullable|string|max:100',
            'chip_type' => 'nullable|string|max:30',
            'is_primary' => 'nullable|boolean',
            'notes' => 'nullable|string|max:255',
        ]);

        $customerId = $request->customer_id;
        $isPrimary = $request->boolean('is_primary');

        // Jika nomor pertama untuk customer ini, otomatis jadikan primary
        $existingCount = DB::table('customer_rs_numbers')->where('customer_id', $customerId)->count();
        if ($existingCount === 0) {
            $isPrimary = true;
        }

        if ($isPrimary) {
            // Unset primary nomor lain milik customer ini
            DB::table('customer_rs_numbers')->where('customer_id', $customerId)->update(['is_primary' => false]);
            // Sinkron ke customers.nomor_rs
            DB::table('customers')->where('id', $customerId)->update(['nomor_rs' => $request->rs_number, 'updated_at' => now()]);
        }

        $id = DB::table('customer_rs_numbers')->insertGetId([
            'customer_id' => $customerId,
            'rs_number' => $request->rs_number,
            'rs_name' => $request->rs_name ?: 'Chip RS #' . ($existingCount + 1),
            'chip_type' => $request->chip_type ?: 'ALL',
            'is_primary' => $isPrimary,
            'status' => 'ACTIVE',
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rsItem = DB::table('customer_rs_numbers')->where('id', $id)->first();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Nomor RS {$request->rs_number} berhasil ditambahkan!",
                'item' => $rsItem,
            ]);
        }

        return redirect()->back()->with('success', "Nomor RS {$request->rs_number} berhasil ditambahkan!");
    }

    /**
     * Update Nomor RS
     */
    public function updateRsNumber(Request $request, $id)
    {
        $request->validate([
            'rs_number' => 'required|string|max:30',
            'rs_name' => 'nullable|string|max:100',
            'chip_type' => 'nullable|string|max:30',
            'status' => 'required|in:ACTIVE,INACTIVE',
            'notes' => 'nullable|string|max:255',
        ]);

        $rsItem = DB::table('customer_rs_numbers')->where('id', $id)->first();
        if (!$rsItem) {
            return response()->json(['success' => false, 'message' => 'Data chip RS tidak ditemukan.'], 404);
        }

        DB::table('customer_rs_numbers')->where('id', $id)->update([
            'rs_number' => $request->rs_number,
            'rs_name' => $request->rs_name,
            'chip_type' => $request->chip_type ?: 'ALL',
            'status' => $request->status,
            'notes' => $request->notes,
            'updated_at' => now(),
        ]);

        if ($rsItem->is_primary) {
            DB::table('customers')->where('id', $rsItem->customer_id)->update(['nomor_rs' => $request->rs_number, 'updated_at' => now()]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Nomor RS {$request->rs_number} berhasil diperbarui!",
            ]);
        }

        return redirect()->back()->with('success', "Nomor RS {$request->rs_number} berhasil diperbarui!");
    }

    /**
     * Set Nomor RS sebagai Nomor RS Utama (Primary)
     */
    public function setPrimaryRsNumber($id)
    {
        $rsItem = DB::table('customer_rs_numbers')->where('id', $id)->first();
        if (!$rsItem) {
            return response()->json(['success' => false, 'message' => 'Data chip RS tidak ditemukan.'], 404);
        }

        // Reset semua nomor RS milik customer ini
        DB::table('customer_rs_numbers')->where('customer_id', $rsItem->customer_id)->update(['is_primary' => false]);
        // Set item ini jadi primary
        DB::table('customer_rs_numbers')->where('id', $id)->update(['is_primary' => true, 'updated_at' => now()]);
        // Sinkron ke customers.nomor_rs
        DB::table('customers')->where('id', $rsItem->customer_id)->update(['nomor_rs' => $rsItem->rs_number, 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Nomor RS {$rsItem->rs_number} berhasil dijadikan sebagai Nomor RS Utama toko!",
        ]);
    }

    /**
     * Hapus Nomor RS
     */
    public function deleteRsNumber($id)
    {
        $rsItem = DB::table('customer_rs_numbers')->where('id', $id)->first();
        if (!$rsItem) {
            return response()->json(['success' => false, 'message' => 'Data chip RS tidak ditemukan.'], 404);
        }

        $customerId = $rsItem->customer_id;
        $totalCount = DB::table('customer_rs_numbers')->where('customer_id', $customerId)->count();

        if ($totalCount <= 1) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus! Outlet minimal harus memiliki 1 nomor RS terdaftar.'], 422);
        }

        $wasPrimary = $rsItem->is_primary;
        DB::table('customer_rs_numbers')->where('id', $id)->delete();

        // Jika yang dihapus adalah primary, jadikan nomor berikutnya sebagai primary
        if ($wasPrimary) {
            $nextRs = DB::table('customer_rs_numbers')->where('customer_id', $customerId)->first();
            if ($nextRs) {
                DB::table('customer_rs_numbers')->where('id', $nextRs->id)->update(['is_primary' => true]);
                DB::table('customers')->where('id', $customerId)->update(['nomor_rs' => $nextRs->rs_number]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Nomor RS berhasil dihapus dari outlet.",
        ]);
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'tap_id' => 'required|exists:taps,id',
            'sales_id' => 'nullable|exists:sales,id',
            'type' => 'nullable|in:RETAIL,GROSIR',
            'is_digipos' => 'nullable',
            'digipos_id' => 'nullable|string|max:50',
            'is_consignment' => 'nullable',
            'nomor_rs' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20',
            'customer_pic' => 'nullable|string|max:100',
            'customer_ktp' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'alamat_npwp' => 'nullable|string|max:500',
            'hari_pjp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'kelurahan_name' => 'nullable|string|max:100',
            'kecamatan_name' => 'nullable|string|max:100',
            'kota_name' => 'nullable|string|max:100',
            'provinsi_name' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        if (!TerritoryScope::canAccessTap((int) $request->tap_id)) {
            return redirect()->route('master.customers')->with('error', 'Akses ditolak: Anda tidak memiliki otorisasi wilayah untuk TAP tersebut.');
        }

        $nextId = DB::table('customers')->count() + 1;
        $customerCode = 'CST-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $isDigipos = $request->boolean('is_digipos');
        $isConsignment = $request->has('is_consignment') ? $request->boolean('is_consignment') : true;
        $digiposId = $isDigipos ? ($request->digipos_id ?: 'DGP-' . rand(100000, 999999)) : null;

        DB::table('customers')->insert([
            'tap_id' => $request->tap_id,
            'sales_id' => $request->sales_id,
            'code' => $customerCode,
            'is_digipos' => $isDigipos,
            'digipos_id' => $digiposId,
            'is_consignment' => $isConsignment,
            'nomor_rs' => $request->nomor_rs ?? ($request->phone ?? '0812' . rand(10000000, 99999999)),
            'name' => $request->name,
            'type' => $request->type ?: 'RETAIL',
            'phone' => $request->phone ?? '0812' . rand(10000000, 99999999),
            'customer_pic' => $request->customer_pic,
            'customer_ktp' => $request->customer_ktp,
            'npwp' => $request->npwp ? trim($request->npwp) : null,
            'alamat_npwp' => $request->alamat_npwp ? trim($request->alamat_npwp) : null,
            'hari_pjp' => $request->hari_pjp,
            'address' => $request->address ?? 'Area Operasional TAP',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'kelurahan_name' => $request->kelurahan_name,
            'kecamatan_name' => $request->kecamatan_name,
            'kota_name' => $request->kota_name,
            'provinsi_name' => $request->provinsi_name,
            'credit_limit' => $request->credit_limit !== null ? (float) $request->credit_limit : 10000000,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.customers')->with('success', "Outlet / Toko [{$customerCode}] {$request->name} berhasil ditambahkan ke database.");
    }

    public function updateCustomer(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'tap_id' => 'required|exists:taps,id',
            'sales_id' => 'nullable|exists:sales,id',
            'type' => 'nullable|in:RETAIL,GROSIR',
            'is_digipos' => 'nullable',
            'digipos_id' => 'nullable|string|max:50',
            'is_consignment' => 'nullable',
            'nomor_rs' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20',
            'customer_pic' => 'nullable|string|max:100',
            'customer_ktp' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'alamat_npwp' => 'nullable|string|max:500',
            'hari_pjp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'kelurahan_name' => 'nullable|string|max:100',
            'kecamatan_name' => 'nullable|string|max:100',
            'kota_name' => 'nullable|string|max:100',
            'provinsi_name' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        if (!TerritoryScope::canAccessTap((int) $request->tap_id)) {
            return redirect()->route('master.customers')->with('error', 'Akses ditolak: Anda tidak memiliki otorisasi wilayah untuk TAP tersebut.');
        }

        $isDigipos = $request->boolean('is_digipos');
        $isConsignment = $request->has('is_consignment') ? $request->boolean('is_consignment') : true;
        $digiposId = $isDigipos ? ($request->digipos_id ?: null) : null;

        DB::table('customers')->where('id', $id)->update([
            'name' => $request->name,
            'tap_id' => $request->tap_id,
            'sales_id' => $request->sales_id,
            'type' => $request->type,
            'is_digipos' => $isDigipos,
            'digipos_id' => $digiposId,
            'is_consignment' => $isConsignment,
            'nomor_rs' => $request->nomor_rs,
            'phone' => $request->phone,
            'customer_pic' => $request->customer_pic,
            'customer_ktp' => $request->customer_ktp,
            'npwp' => $request->npwp ? trim($request->npwp) : null,
            'alamat_npwp' => $request->alamat_npwp ? trim($request->alamat_npwp) : null,
            'hari_pjp' => $request->hari_pjp,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'kelurahan_name' => $request->kelurahan_name,
            'kecamatan_name' => $request->kecamatan_name,
            'kota_name' => $request->kota_name,
            'provinsi_name' => $request->provinsi_name,
            'credit_limit' => $request->credit_limit !== null ? (float) $request->credit_limit : 0,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.customers')->with('success', "Data Outlet / Toko [{$request->name}] berhasil diperbarui.");
    }

    public function users(Request $request)
    {
        $usersRaw = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('sales', 'users.sales_id', '=', 'sales.id')
            ->leftJoin('taps as sales_taps', 'sales.tap_id', '=', 'sales_taps.id')
            ->select(
                'users.*',
                'roles.name as role_name',
                'roles.slug as role_slug',
                'sales.code as sales_code',
                'sales.name as sales_name',
                'sales.phone as sales_phone',
                'sales_taps.name as sales_tap'
            )
            ->orderBy('users.id')
            ->get();

        $userIds = $usersRaw->pluck('id')->toArray();

        // Ambil mapping dari tabel tunggal user_has_areas_clusters_taps_warehouses
        $allMappings = DB::table('user_has_areas_clusters_taps_warehouses')
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $users = $usersRaw->map(function ($u) use ($allMappings) {
            $userMaps = $allMappings->get($u->id, collect([]));
            $u->mapped_area_ids = $userMaps->pluck('area_id')->filter()->unique()->values()->map('intval')->toArray();
            $u->mapped_cluster_ids = $userMaps->pluck('cluster_id')->filter()->unique()->values()->map('intval')->toArray();
            $u->mapped_tap_ids = $userMaps->pluck('tap_id')->filter()->unique()->values()->map('intval')->toArray();
            
            // TAP yang mode auto all warehouses (+sales baru)
            $u->auto_tap_ids = $userMaps->where('is_all_warehouses', 1)->pluck('tap_id')->filter()->unique()->values()->map('intval')->toArray();
            
            // Gudang spesifik yang dicentang manual
            $u->mapped_warehouse_ids = $userMaps->where('is_all_warehouses', 0)->pluck('warehouse_id')->filter()->unique()->values()->map('intval')->toArray();

            $u->taps_count = count($u->mapped_tap_ids);
            $u->warehouses_count = count($u->mapped_warehouse_ids);
            return $u;
        });

        $roles = DB::table('roles')->orderBy('id')->get();
        $salesList = DB::table('sales')->where('status', 'ACTIVE')->orderBy('name')->get();

        // Buat struktur pohon wilayah: Area -> Cluster -> TAP -> Warehouses
        $areas = DB::table('areas')->where('status', 'ACTIVE')->orderBy('name')->get();
        $clusters = DB::table('clusters')->where('status', 'ACTIVE')->orderBy('name')->get()->groupBy('area_id');
        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get()->groupBy('cluster_id');
        $warehouses = DB::table('warehouses')
            ->leftJoin('sales', 'warehouses.sales_id', '=', 'sales.id')
            ->select('warehouses.*', 'sales.name as sales_person_name')
            ->where('warehouses.status', 'ACTIVE')
            ->orderBy('warehouses.type')
            ->orderBy('warehouses.name')
            ->get()
            ->groupBy('tap_id');

        $territoryTree = $areas->map(function ($area) use ($clusters, $taps, $warehouses) {
            $areaClusters = $clusters->get($area->id, collect([]))->map(function ($cluster) use ($taps, $warehouses) {
                $clusterTaps = $taps->get($cluster->id, collect([]))->map(function ($tap) use ($warehouses) {
                    $tapWhs = $warehouses->get($tap->id, collect([]))->values();
                    return [
                        'id' => (int)$tap->id,
                        'name' => $tap->name,
                        'code' => $tap->code,
                        'warehouses' => $tapWhs,
                    ];
                })->values();

                return [
                    'id' => (int)$cluster->id,
                    'name' => $cluster->name,
                    'code' => $cluster->code,
                    'taps' => $clusterTaps,
                ];
            })->values();

            return [
                'id' => (int)$area->id,
                'name' => $area->name,
                'code' => $area->code,
                'clusters' => $areaClusters,
            ];
        })->values();

        return view('master.users', compact('users', 'roles', 'salesList', 'territoryTree'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email|max:150',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'sales_id' => 'nullable|exists:sales,id',
            'signature_file' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $signaturePath = $this->handleSignatureUpload($request);

        $userId = DB::table('users')->insertGetId([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
            'sales_id' => $request->sales_id,
            'phone' => $request->phone,
            'signature' => $signaturePath,
            'has_full_access' => 0,
            'is_ho_readonly' => 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->sales_id) {
            DB::table('sales')->where('id', $request->sales_id)->update([
                'user_id' => $userId,
                'updated_at' => now()
            ]);
        }

        // Sinkronisasi Spatie Role
        $createdUser = \App\Models\User::find($userId);
        $roleModel = \Spatie\Permission\Models\Role::find($request->role_id);
        if ($createdUser && $roleModel) {
            $createdUser->syncRoles([$roleModel->name]);
        }

        return redirect()->route('master.users')->with('success', "Akun pengguna [{$request->name}] berhasil dibuat. Silakan atur wilayah akses melalui tombol Mapping.");
    }

    public function updateUser(Request $request, $id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return redirect()->route('master.users')->with('error', 'Pengguna tidak ditemukan.');
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'sales_id' => 'nullable|exists:sales,id',
            'is_active' => 'required|in:0,1',
            'password' => 'nullable|string|min:6',
            'signature_file' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $signaturePath = $this->handleSignatureUpload($request, $user->signature);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'sales_id' => $request->sales_id,
            'phone' => $request->phone,
            'signature' => $signaturePath,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        DB::table('users')->where('id', $id)->update($updateData);

        if ($request->sales_id) {
            DB::table('sales')->where('id', $request->sales_id)->update([
                'user_id' => $id,
                'updated_at' => now()
            ]);
        }

        // Sinkronisasi Spatie Role
        $updatedUser = \App\Models\User::find($id);
        $roleModel = \Spatie\Permission\Models\Role::find($request->role_id);
        if ($updatedUser && $roleModel) {
            $updatedUser->syncRoles([$roleModel->name]);
        }

        return redirect()->route('master.users')->with('success', "Data profil akun [{$request->name}] berhasil diperbarui.");
    }

    /**
     * Helper untuk upload file tanda tangan atau simpan base64 canvas
     */
    private function handleSignatureUpload(Request $request, $existingSignature = null)
    {
        // 1. Opsi Hapus Tanda Tangan
        if ($request->input('signature_remove') == '1') {
            if ($existingSignature && file_exists(public_path($existingSignature))) {
                @unlink(public_path($existingSignature));
            }
            return null;
        }

        $directory = public_path('uploads/signatures');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // 2. Opsi Upload Berkas Gambar (PNG, JPG)
        if ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');
            $filename = 'sign_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);

            if ($existingSignature && file_exists(public_path($existingSignature))) {
                @unlink(public_path($existingSignature));
            }

            return 'uploads/signatures/' . $filename;
        }

        // 3. Opsi Goresan Canvas (Base64 PNG)
        if ($request->filled('signature_canvas') && str_starts_with($request->signature_canvas, 'data:image')) {
            $dataUri = $request->signature_canvas;
            $encodedData = substr($dataUri, strpos($dataUri, ',') + 1);
            $decodedData = base64_decode($encodedData);
            if ($decodedData) {
                $filename = 'sign_' . time() . '_' . uniqid() . '.png';
                file_put_contents($directory . '/' . $filename, $decodedData);

                if ($existingSignature && file_exists(public_path($existingSignature))) {
                    @unlink(public_path($existingSignature));
                }

                return 'uploads/signatures/' . $filename;
            }
        }

        return $existingSignature;
    }

    /**
     * Halaman Dedicated: Otorisasi Wilayah & Gudang Pengguna
     */
    public function userMapping($id)
    {
        $user = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select('users.*', 'roles.name as role_name', 'roles.slug as role_slug')
            ->where('users.id', $id)
            ->first();

        if (!$user) {
            return redirect()->route('master.users')->with('error', 'Pengguna tidak ditemukan!');
        }

        $userMappings = DB::table('user_has_areas_clusters_taps_warehouses')
            ->where('user_id', $id)
            ->get();

        $mappedAreaIds = $userMappings->pluck('area_id')->filter()->unique()->values()->map('intval')->toArray();
        $mappedClusterIds = $userMappings->pluck('cluster_id')->filter()->unique()->values()->map('intval')->toArray();
        $mappedTapIds = $userMappings->pluck('tap_id')->filter()->unique()->values()->map('intval')->toArray();
        $autoTapIds = $userMappings->where('is_all_warehouses', 1)->pluck('tap_id')->filter()->unique()->values()->map('intval')->toArray();
        $customWhIds = $userMappings->where('is_all_warehouses', 0)->pluck('warehouse_id')->filter()->unique()->values()->map('intval')->toArray();

        // Buat struktur pohon wilayah: Area -> Cluster -> TAP -> Warehouses
        $areas = DB::table('areas')->where('status', 'ACTIVE')->orderBy('name')->get();
        $clusters = DB::table('clusters')->where('status', 'ACTIVE')->orderBy('name')->get()->groupBy('area_id');
        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get()->groupBy('cluster_id');
        $warehouses = DB::table('warehouses')
            ->leftJoin('sales', 'warehouses.sales_id', '=', 'sales.id')
            ->select('warehouses.*', 'sales.name as sales_person_name')
            ->where('warehouses.status', 'ACTIVE')
            ->orderBy('warehouses.type')
            ->orderBy('warehouses.name')
            ->get()
            ->groupBy('tap_id');

        $territoryTree = $areas->map(function ($area) use ($clusters, $taps, $warehouses) {
            $areaClusters = $clusters->get($area->id, collect([]))->map(function ($cluster) use ($taps, $warehouses) {
                $clusterTaps = $taps->get($cluster->id, collect([]))->map(function ($tap) use ($warehouses) {
                    $tapWhs = $warehouses->get($tap->id, collect([]))->values();
                    return [
                        'id' => (int)$tap->id,
                        'name' => $tap->name,
                        'code' => $tap->code,
                        'warehouses' => $tapWhs,
                    ];
                })->values();

                return [
                    'id' => (int)$cluster->id,
                    'name' => $cluster->name,
                    'code' => $cluster->code,
                    'taps' => $clusterTaps,
                ];
            })->values();

            return [
                'id' => (int)$area->id,
                'name' => $area->name,
                'code' => $area->code,
                'clusters' => $areaClusters,
            ];
        })->values();

        return view('master.user-mapping', compact(
            'user',
            'territoryTree',
            'mappedAreaIds',
            'mappedClusterIds',
            'mappedTapIds',
            'autoTapIds',
            'customWhIds'
        ));
    }

    /**
     * Otorisasi Wilayah & Gudang Transaksi (Pohon Cascading Tree)
     */
    public function saveUserMapping(Request $request, $id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return redirect()->route('master.users')->with('error', 'Data pengguna tidak ditemukan!');
        }

        $hasFullAccess = $request->input('has_full_access') == '1';
        $isHo = $request->input('is_ho_readonly') == '1';

        // 1. Update status akun pengguna
        DB::table('users')->where('id', $id)->update([
            'has_full_access' => $hasFullAccess ? 1 : 0,
            'is_ho_readonly' => $isHo ? 1 : 0,
            'updated_at' => now(),
        ]);

        // 2. Bersihkan riwayat mapping lama di tabel tunggal
        DB::table('user_has_areas_clusters_taps_warehouses')->where('user_id', $id)->delete();

        // 3. Jika bukan akses penuh, simpan pohon wilayah terpilih
        if (!$hasFullAccess) {
            $selectedTaps = array_filter(array_map('intval', $request->input('selected_taps', [])));
            $autoTapIds = array_filter(array_map('intval', $request->input('auto_taps', [])));
            $customWhs = array_filter(array_map('intval', $request->input('custom_warehouses', [])));

            $allTaps = DB::table('taps')
                ->join('clusters', 'taps.cluster_id', '=', 'clusters.id')
                ->select('taps.id as tap_id', 'taps.cluster_id', 'clusters.area_id')
                ->get()
                ->keyBy('tap_id');

            $recordsToInsert = [];

            foreach ($selectedTaps as $tId) {
                if (!isset($allTaps[$tId])) continue;
                $tapInfo = $allTaps[$tId];

                // Cek apakah TAP ini memilih mode auto all warehouses (+sales baru)
                $isAutoAll = in_array($tId, $autoTapIds);

                if ($isAutoAll) {
                    $recordsToInsert[] = [
                        'user_id' => $id,
                        'area_id' => $tapInfo->area_id,
                        'cluster_id' => $tapInfo->cluster_id,
                        'tap_id' => $tId,
                        'warehouse_id' => null,
                        'is_all_warehouses' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    // Ambil gudang spesifik yang dicentang di TAP ini
                    $tapWhs = DB::table('warehouses')->where('tap_id', $tId)->pluck('id')->toArray();
                    $checkedWhs = array_intersect($tapWhs, $customWhs);

                    if (!empty($checkedWhs)) {
                        foreach ($checkedWhs as $whId) {
                            $recordsToInsert[] = [
                                'user_id' => $id,
                                'area_id' => $tapInfo->area_id,
                                'cluster_id' => $tapInfo->cluster_id,
                                'tap_id' => $tId,
                                'warehouse_id' => $whId,
                                'is_all_warehouses' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    } else {
                        // Jika 0 gudang dicentang (misal TAP monitoring)
                        $recordsToInsert[] = [
                            'user_id' => $id,
                            'area_id' => $tapInfo->area_id,
                            'cluster_id' => $tapInfo->cluster_id,
                            'tap_id' => $tId,
                            'warehouse_id' => null,
                            'is_all_warehouses' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            if (!empty($recordsToInsert)) {
                DB::table('user_has_areas_clusters_taps_warehouses')->insert($recordsToInsert);
            }
        }

        $statusMsg = $hasFullAccess 
            ? "Hak akses akun [{$user->name}] diatur ke: Akses Penuh (Semua Wilayah & Gudang)."
            : ($isHo 
                ? "Akun [{$user->name}] dikonfigurasi sebagai Mode Monitoring (Read-Only: hanya melihat laporan wilayah)."
                : "Hak akses wilayah dan gudang untuk akun [{$user->name}] berhasil diperbarui.");

        return redirect()->route('master.users.mapping', $id)->with('success', $statusMsg);
    }

    // ========================================================
    // MASTER TAMBAHAN 1: REKENING BANK & KAS PT
    // ========================================================
    public function banks(Request $request)
    {
        if (DB::table('company_banks')->count() === 0) {
            DB::table('company_banks')->insert([
                [
                    'bank_name' => 'Bank Central Asia (BCA)',
                    'account_number' => '8830-192-831',
                    'account_holder' => 'PT WAHANA PUTRA YUDHA',
                    'branch_name' => 'KCU Tangerang Kisamaun',
                    'account_type' => 'BANK',
                    'balance' => 450000000,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'bank_name' => 'Bank Mandiri',
                    'account_number' => '155-00-9823101-2',
                    'account_holder' => 'PT WAHANA PUTRA YUDHA',
                    'branch_name' => 'KC Bintaro Sektor 7',
                    'account_type' => 'BANK',
                    'balance' => 280000000,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'bank_name' => 'Bank Syariah Indonesia (BSI)',
                    'account_number' => '712-093-8491',
                    'account_holder' => 'PT WAHANA PUTRA YUDHA',
                    'branch_name' => 'KC BSD City Serpong',
                    'account_type' => 'BANK',
                    'balance' => 125000000,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'bank_name' => 'Kas Operasional Kasir',
                    'account_number' => 'KAS-TGR-01',
                    'account_holder' => 'Bendahara Kasir TAP Tangerang',
                    'branch_name' => 'Kantor TAP Tangerang',
                    'account_type' => 'CASH',
                    'balance' => 15450000,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'bank_name' => 'Kas Operasional Kasir',
                    'account_number' => 'KAS-SRP-02',
                    'account_holder' => 'Bendahara Kasir TAP Serpong',
                    'branch_name' => 'Kantor TAP Serpong',
                    'account_type' => 'CASH',
                    'balance' => 8750000,
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $banks = DB::table('company_banks')->orderBy('account_type')->orderBy('bank_name')->get();

        $kpi = [
            'total_accounts' => $banks->count(),
            'total_balance' => $banks->where('status', 'ACTIVE')->sum('balance'),
            'bank_count' => $banks->where('account_type', 'BANK')->count(),
            'cash_count' => $banks->where('account_type', 'CASH')->count(),
        ];

        return view('master.banks', compact('banks', 'kpi'));
    }

    public function storeBank(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:60',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'required|string|max:100',
            'branch_name' => 'nullable|string|max:100',
            'account_type' => 'required|in:BANK,CASH',
            'balance' => 'required|numeric|min:0',
        ]);

        DB::table('company_banks')->insert([
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number ?: 'KAS-' . strtoupper(substr(uniqid(), -4)),
            'account_holder' => $request->account_holder,
            'branch_name' => $request->branch_name,
            'account_type' => $request->account_type,
            'balance' => $request->balance,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.banks')->with('success', "Rekening [{$request->bank_name} - {$request->account_holder}] berhasil ditambahkan.");
    }

    // ========================================================
    // MASTER TAMBAHAN 2: VENDOR & PRINCIPAL
    // ========================================================
    public function vendors(Request $request)
    {
        if (DB::table('vendors')->count() === 0) {
            DB::table('vendors')->insert([
                [
                    'code' => 'VDR-001',
                    'name' => 'PT Telekomunikasi Selular (Telkomsel)',
                    'category' => 'PRINCIPAL_TELCO',
                    'contact_person' => 'Bpk. Arya Wibowo (Account Manager)',
                    'phone' => '021-5240123',
                    'email' => 'corporate.sales@telkomsel.co.id',
                    'address' => 'Telkom Landmark Tower, Jl. Gatot Subroto No. 52, Jakarta Selatan',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'VDR-002',
                    'name' => 'CV Mandiri Perkasa Printing',
                    'category' => 'PRINTING_VOUCHER',
                    'contact_person' => 'Bpk. Bambang Sutrisno',
                    'phone' => '0812-8899-2211',
                    'email' => 'sales@mandiriprinting.com',
                    'address' => 'Kawasan Industri Manis Blok C No. 4, Jatiuwung, Tangerang',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'VDR-003',
                    'name' => 'PT Graha Media Distribusi',
                    'category' => 'MERCHANDISE_POSM',
                    'contact_person' => 'Ibu Ratna Dewi',
                    'phone' => '021-5830911',
                    'email' => 'cs@grahamedia.co.id',
                    'address' => 'Ruko ITC BSD Sektor 7 No. 12, Serpong, Tangerang Selatan',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'VDR-004',
                    'name' => 'PT Logistik Nusantara Express',
                    'category' => 'LOGISTIK_ARMADA',
                    'contact_person' => 'Bpk. Dedi Kurniawan',
                    'phone' => '0811-9022-3344',
                    'email' => 'ops@logistiknusantara.co.id',
                    'address' => 'Jl. Daan Mogot Km. 19 No. 88, Batuceper, Tangerang',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $vendors = DB::table('vendors')->orderBy('code')->get();

        $kpi = [
            'total_vendors' => $vendors->count(),
            'active_vendors' => $vendors->where('status', 'ACTIVE')->count(),
            'principal_count' => $vendors->where('category', 'PRINCIPAL_TELCO')->count(),
            'supplies_count' => $vendors->whereIn('category', ['PRINTING_VOUCHER', 'MERCHANDISE_POSM', 'LOGISTIK_ARMADA'])->count(),
        ];

        return view('master.vendors', compact('vendors', 'kpi'));
    }

    public function storeVendor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:60',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
        ]);

        $nextNum = DB::table('vendors')->count() + 1;
        $code = 'VDR-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        DB::table('vendors')->insert([
            'code' => $code,
            'name' => $request->name,
            'category' => $request->category,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.vendors')->with('success', "Vendor [{$code} - {$request->name}] berhasil didaftarkan.");
    }

    // ========================================================
    // MASTER TAMBAHAN 3: SKEMA HARGA (PRICE PROFILE)
    // ========================================================
    public function priceProfiles(Request $request)
    {
        if (DB::table('price_profiles')->count() === 0) {
            DB::table('price_profiles')->insert([
                [
                    'code' => 'PRC-RS',
                    'name' => 'Tier Retail Outlet (RS)',
                    'segment' => 'RS',
                    'discount_percentage' => 0.00,
                    'min_order_qty' => 1,
                    'description' => 'Skema harga normal untuk konter pulsa retail umum / outlet sell-through.',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'PRC-BOS',
                    'name' => 'Tier Grosir / Bakul (BOS)',
                    'segment' => 'BOS',
                    'discount_percentage' => 2.50,
                    'min_order_qty' => 50,
                    'description' => 'Skema harga khusus pembelian grosir partai (Bakul Kuota) dengan volume order tinggi.',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'PRC-SD',
                    'name' => 'Tier Sub-Distributor (SD)',
                    'segment' => 'SD',
                    'discount_percentage' => 4.50,
                    'min_order_qty' => 250,
                    'description' => 'Skema harga distributor cabang sub-wilayah dengan komitmen buffer stok wilayah cluster.',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $profiles = DB::table('price_profiles')->orderBy('code')->get();

        $kpi = [
            'total_profiles' => $profiles->count(),
            'active_profiles' => $profiles->where('status', 'ACTIVE')->count(),
            'rs_count' => DB::table('customers')->where('status', 'ACTIVE')->count(),
        ];

        return view('master.price_profiles', compact('profiles', 'kpi'));
    }

    public function storePriceProfile(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:30|unique:price_profiles,code',
            'name' => 'required|string|max:100',
            'segment' => 'required|in:RS,BOS,SD',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'min_order_qty' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        DB::table('price_profiles')->insert([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'segment' => $request->segment,
            'discount_percentage' => $request->discount_percentage,
            'min_order_qty' => $request->min_order_qty,
            'description' => $request->description,
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.price-profiles')->with('success', "Skema harga [{$request->code} - {$request->name}] berhasil ditambahkan.");
    }

    // ========================================================
    // MASTER TAMBAHAN 4, 5, 6: PREVIEWS & SEGMENTS
    // ========================================================
    public function customerSegments()
    {
        return view('master.customer_segments');
    }

    public function territories()
    {
        return view('master.territories');
    }

    public function approvalRules()
    {
        return view('master.approval_rules');
    }

    /**
     * Universal Switch Toggle Status (ACTIVE <-> INACTIVE) untuk Semua Data Master
     */
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'entity' => 'required|string|in:customers,sales,products,product_categories,product_prices,programs,warehouses,banks,vendors,taps,price_profiles,users,customer_rs,sales_types,sales_levels',
            'id' => 'required|integer',
        ]);

        $entity = $request->entity;
        $id = $request->id;

        $mapping = [
            'customers' => ['table' => 'customers', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Toko / Outlet'],
            'sales' => ['table' => 'sales', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Salesman'],
            'products' => ['table' => 'products', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Produk'],
            'product_categories' => ['table' => 'product_categories', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Tipe Produk'],
            'product_prices' => ['table' => 'product_prices', 'name_col' => 'id', 'status_col' => 'status', 'label' => 'Harga Wilayah'],
            'programs' => ['table' => 'programs', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Program Harga'],
            'warehouses' => ['table' => 'warehouses', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Gudang'],
            'banks' => ['table' => 'company_banks', 'name_col' => 'bank_name', 'status_col' => 'status', 'label' => 'Rekening Bank'],
            'vendors' => ['table' => 'vendors', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Vendor'],
            'taps' => ['table' => 'taps', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Kantor Cabang TAP'],
            'price_profiles' => ['table' => 'price_profiles', 'name_col' => 'name', 'status_col' => 'status', 'label' => 'Skema Harga'],
            'users' => ['table' => 'users', 'name_col' => 'name', 'status_col' => 'is_active', 'label' => 'Pengguna', 'is_boolean' => true],
            'customer_rs' => ['table' => 'customer_rs_numbers', 'name_col' => 'rs_number', 'status_col' => 'status', 'label' => 'Nomor Chip RS'],
            'sales_types' => ['table' => 'sales_types', 'name_col' => 'type_name', 'status_col' => 'status', 'label' => 'Tipe Sales'],
            'sales_levels' => ['table' => 'sales_levels', 'name_col' => 'level_name', 'status_col' => 'status', 'label' => 'Level Sales'],
        ];

        $config = $mapping[$entity];
        $item = DB::table($config['table'])->where('id', $id)->first();

        if (!$item) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $isBoolean = $config['is_boolean'] ?? false;
        $statusCol = $config['status_col'];
        $nameCol = $config['name_col'];
        $itemName = $item->$nameCol ?? "ID #{$id}";

        if ($isBoolean) {
            $newVal = $item->$statusCol ? 0 : 1;
            $newStatusText = $newVal ? 'ACTIVE' : 'INACTIVE';
            $isActive = (bool) $newVal;
        } else {
            $currentVal = strtoupper($item->$statusCol ?? 'ACTIVE');
            $newVal = ($currentVal === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
            $newStatusText = $newVal;
            $isActive = ($newVal === 'ACTIVE');
        }

        DB::table($config['table'])->where('id', $id)->update([
            $statusCol => $newVal,
            'updated_at' => now(),
        ]);

        // Catat jejak audit log jika tabel activity_logs ada
        if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            DB::table('activity_logs')->insert([
                'user_id' => 1,
                'user_name' => 'Administrator ERP',
                'user_role' => 'Super Admin',
                'action' => 'TOGGLE_STATUS_' . strtoupper($entity),
                'module' => 'MASTER',
                'doc_number' => "MST-{$id}",
                'severity' => $isActive ? 'INFO' : 'WARNING',
                'description' => "Mengubah status {$config['label']} '{$itemName}' menjadi " . ($isActive ? 'AKTIF (ACTIVE/00)' : 'NON-AKTIF (INACTIVE/99)'),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'user_agent' => $request->userAgent() ?? 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $statusLabel = $isActive ? 'AKTIF' : 'NON-AKTIF';
        $message = "Status {$config['label']} '{$itemName}' berhasil diubah menjadi {$statusLabel}!";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $isActive,
                'status' => $newStatusText,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Master Rencana Kunjungan (PJP)
     */
    public function pjp(Request $request)
    {
        $salesList = DB::table('sales')->where('status', 'ACTIVE')->orderBy('name')->get();
        $taps = DB::table('taps')->where('status', 'ACTIVE')->orderBy('name')->get();
        $days = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU'];

        $query = DB::table('customer_pjps')
            ->join('sales', 'customer_pjps.sales_id', '=', 'sales.id')
            ->join('customers', 'customer_pjps.customer_id', '=', 'customers.id')
            ->leftJoin('taps', 'customers.tap_id', '=', 'taps.id')
            ->select(
                'customer_pjps.*',
                'sales.code as sales_code',
                'sales.name as sales_name',
                'sales.phone as sales_phone',
                'customers.code as customer_code',
                'customers.name as customer_name',
                'customers.address as customer_address',
                'customers.customer_pic',
                'customers.digipos_id',
                'customers.type as customer_type',
                'taps.name as tap_name'
            );

        if ($request->filled('sales_id') && $request->sales_id !== 'ALL') {
            $query->where('customer_pjps.sales_id', $request->sales_id);
        }

        if ($request->filled('day') && $request->day !== 'ALL') {
            $query->where('customer_pjps.day', strtoupper($request->day));
        }

        if ($request->filled('tap_id') && $request->tap_id !== 'ALL') {
            $query->where('customers.tap_id', $request->tap_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('customers.name', 'like', "%{$s}%")
                    ->orWhere('customers.code', 'like', "%{$s}%")
                    ->orWhere('customers.digipos_id', 'like', "%{$s}%")
                    ->orWhere('sales.name', 'like', "%{$s}%")
                    ->orWhere('sales.code', 'like', "%{$s}%");
            });
        }

        $perPage = (int) $request->input('per_page', 25);
        $pjps = $query->orderBy('sales.name')
            ->orderByRaw("FIELD(customer_pjps.day, 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU')")
            ->orderBy('customer_pjps.sequence')
            ->paginate($perPage)
            ->withQueryString();

        $totalSchedules = DB::table('customer_pjps')->count();
        $totalCoveredOutlets = DB::table('customer_pjps')->distinct('customer_id')->count('customer_id');
        $totalActiveSales = DB::table('customer_pjps')->distinct('sales_id')->count('sales_id');

        $customersList = DB::table('customers')
            ->select('id', 'code', 'name', 'address')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        return view('master.pjp', compact(
            'pjps',
            'salesList',
            'taps',
            'days',
            'customersList',
            'totalSchedules',
            'totalCoveredOutlets',
            'totalActiveSales',
            'perPage'
        ));
    }

    public function storePjp(Request $request)
    {
        $request->validate([
            'sales_id' => 'required|exists:sales,id',
            'customer_id' => 'required|exists:customers,id',
            'day' => 'required|in:SENIN,SELASA,RABU,KAMIS,JUMAT,SABTU,MINGGU',
            'notes' => 'nullable|string|max:255',
        ]);

        $exists = DB::table('customer_pjps')
            ->where('sales_id', $request->sales_id)
            ->where('customer_id', $request->customer_id)
            ->where('day', $request->day)
            ->first();

        if ($exists) {
            return redirect()->back()->with('error', 'Toko ini sudah terdaftar pada jadwal hari tersebut untuk sales terpilih.');
        }

        DB::table('customer_pjps')->insert([
            'sales_id' => $request->sales_id,
            'customer_id' => $request->customer_id,
            'day' => $request->day,
            'sequence' => 1,
            'notes' => $request->notes ? trim($request->notes) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Jadwal kunjungan baru berhasil ditambahkan.');
    }

    public function updatePjp(Request $request, $id)
    {
        $request->validate([
            'day' => 'required|in:SENIN,SELASA,RABU,KAMIS,JUMAT,SABTU,MINGGU',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::table('customer_pjps')->where('id', $id)->update([
            'day' => $request->day,
            'notes' => $request->notes ? trim($request->notes) : null,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Jadwal kunjungan berhasil diperbarui.');
    }

    public function deletePjp($id)
    {
        DB::table('customer_pjps')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Jadwal kunjungan berhasil dihapus.');
    }

    public function downloadPjpTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Template_Mapping_PJP.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'KODE_SALES',
                'NAMA_SALES',
                'KODE_OUTLET',
                'NAMA_OUTLET',
                'HARI_KUNJUNGAN',
                'CATATAN'
            ]);

            $sampleSales = DB::table('sales')->where('status', 'ACTIVE')->take(2)->get();
            $sampleCustomers = DB::table('customers')->where('status', 'ACTIVE')->take(4)->get();

            if ($sampleSales->count() > 0 && $sampleCustomers->count() > 0) {
                $days = ['SENIN', 'SELASA', 'RABU', 'KAMIS'];
                $s1 = $sampleSales[0];
                $seq = 0;
                foreach ($sampleCustomers->take(3) as $c) {
                    fputcsv($handle, [
                        $s1->code,
                        $s1->name,
                        $c->code,
                        $c->name,
                        $days[$seq] ?? 'SENIN',
                        'Rute reguler'
                    ]);
                    $seq++;
                }

                if ($sampleSales->count() > 1 && $sampleCustomers->count() > 3) {
                    $s2 = $sampleSales[1];
                    $c4 = $sampleCustomers[3];
                    fputcsv($handle, [
                        $s2->code,
                        $s2->name,
                        $c4->code,
                        $c4->name,
                        'JUMAT',
                        'Kunjungan akhir pekan'
                    ]);
                }
            } else {
                fputcsv($handle, ['SLS-001', 'Rian Hidayat', 'CST-001', 'Berkah Cell', 'SENIN', 'Contoh']);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function previewPjpUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'txt', 'xlsx'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format file harus berupa .xlsx atau .csv',
            ], 422);
        }

        $rawRows = $this->parsePjpFile($file->getRealPath(), $ext);
        if (empty($rawRows) || count($rawRows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File kosong atau tidak memiliki baris data setelah header.',
            ], 422);
        }

        // Drop header
        array_shift($rawRows);

        $salesList = DB::table('sales')->select('id', 'code', 'name')->get()->keyBy(function ($item) {
            return strtoupper(trim($item->code));
        });
        $customersList = DB::table('customers')->select('id', 'code', 'name')->get()->keyBy(function ($item) {
            return strtoupper(trim($item->code));
        });

        $validDays = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU'];
        $parsedRows = [];
        $affectedSales = [];
        $validCount = 0;
        $invalidCount = 0;

        foreach ($rawRows as $idx => $cols) {
            if (empty(array_filter($cols, 'strlen'))) {
                continue;
            }

            $salesCode = strtoupper(trim($cols[0] ?? ''));
            $salesNameInput = trim($cols[1] ?? '');
            $customerCode = strtoupper(trim($cols[2] ?? ''));
            $customerNameInput = trim($cols[3] ?? '');
            $day = strtoupper(trim($cols[4] ?? ''));
            $notes = trim($cols[5] ?? '');
            if (!empty($cols[6])) {
                $notes = trim($cols[6]);
            }

            $errors = [];

            $salesObj = $salesList->get($salesCode);
            if (!$salesObj) {
                $errors[] = "Sales [{$salesCode}] tidak ditemukan";
            }

            $customerObj = $customersList->get($customerCode);
            if (!$customerObj) {
                $errors[] = "Outlet [{$customerCode}] tidak ditemukan";
            }

            if (!in_array($day, $validDays)) {
                $errors[] = "Hari '{$day}' tidak valid";
            }

            $isValid = empty($errors);
            if ($isValid) {
                $validCount++;
                if (!isset($affectedSales[$salesObj->id])) {
                    $affectedSales[$salesObj->id] = [
                        'id' => $salesObj->id,
                        'code' => $salesObj->code,
                        'name' => $salesObj->name,
                        'total_outlets' => 0,
                    ];
                }
                $affectedSales[$salesObj->id]['total_outlets']++;
            } else {
                $invalidCount++;
            }

            $parsedRows[] = [
                'row_num' => $idx + 2,
                'sales_id' => $salesObj ? $salesObj->id : null,
                'sales_code' => $salesCode,
                'sales_name' => $salesObj ? $salesObj->name : ($salesNameInput ?: '-'),
                'customer_id' => $customerObj ? $customerObj->id : null,
                'customer_code' => $customerCode,
                'customer_name' => $customerObj ? $customerObj->name : ($customerNameInput ?: '-'),
                'day' => $day,
                'sequence' => $seq,
                'notes' => $notes,
                'is_valid' => $isValid,
                'error_msg' => implode(', ', $errors),
            ];
        }

        $previewToken = 'pjp_import_' . uniqid();
        session()->put($previewToken, [
            'affected_sales_ids' => array_keys($affectedSales),
            'valid_rows' => array_values(array_filter($parsedRows, function ($r) { return $r['is_valid']; })),
            'total_valid' => $validCount,
        ]);

        return response()->json([
            'success' => true,
            'preview_token' => $previewToken,
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'invalid_count' => $invalidCount,
            'affected_sales' => array_values($affectedSales),
            'rows' => $parsedRows,
        ]);
    }

    public function confirmPjpUpload(Request $request)
    {
        $request->validate([
            'preview_token' => 'required|string',
        ]);

        $cached = session()->get($request->preview_token);
        if (!$cached || empty($cached['valid_rows'])) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi pratinjau kedaluwarsa atau tidak ada data valid. Silakan upload ulang.',
            ], 422);
        }

        $affectedSalesIds = $cached['affected_sales_ids'];
        $validRows = $cached['valid_rows'];

        DB::beginTransaction();
        try {
            // HANYA hapus jadwal lama milik sales yang ada di file Excel ini
            DB::table('customer_pjps')
                ->whereIn('sales_id', $affectedSalesIds)
                ->delete();

            $insertBatch = [];
            $now = now();
            foreach ($validRows as $row) {
                $insertBatch[] = [
                    'sales_id' => $row['sales_id'],
                    'customer_id' => $row['customer_id'],
                    'day' => $row['day'],
                    'sequence' => $row['sequence'] ?? 1,
                    'notes' => $row['notes'] ?: null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($insertBatch, 200) as $chunk) {
                DB::table('customer_pjps')->insert($chunk);
            }

            DB::commit();
            session()->forget($request->preview_token);

            $salesCount = count($affectedSalesIds);
            $rowCount = count($insertBatch);

            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbarui jadwal PJP untuk {$salesCount} sales dengan total {$rowCount} kunjungan toko.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan ke database: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function parsePjpFile($filePath, $extension)
    {
        $rows = [];
        $extension = strtolower($extension);

        if (in_array($extension, ['csv', 'txt'])) {
            $handle = fopen($filePath, 'r');
            if (!$handle) return [];
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = (strpos($firstLine, ';') !== false && strpos($firstLine, ',') === false) ? ';' : ',';

            while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
                $rows[] = array_map(function ($val) {
                    return trim($val ?? '');
                }, $data);
            }
            fclose($handle);
            return $rows;
        }

        if ($extension === 'xlsx') {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) !== true) {
                return [];
            }

            $sharedStrings = [];
            $sharedXmlContent = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedXmlContent) {
                $xml = simplexml_load_string($sharedXmlContent);
                if ($xml && isset($xml->si)) {
                    foreach ($xml->si as $si) {
                        if (isset($si->t)) {
                            $sharedStrings[] = (string) $si->t;
                        } elseif (isset($si->r)) {
                            $text = '';
                            foreach ($si->r as $r) {
                                $text .= (string) $r->t;
                            }
                            $sharedStrings[] = $text;
                        } else {
                            $sharedStrings[] = '';
                        }
                    }
                }
            }

            $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
            if (!$sheetXmlContent) {
                $zip->close();
                return [];
            }

            $xml = simplexml_load_string($sheetXmlContent);
            if (!$xml || !isset($xml->sheetData)) {
                $zip->close();
                return [];
            }

            foreach ($xml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $c) {
                    $attr = $c->attributes();
                    $type = isset($attr['t']) ? (string) $attr['t'] : '';
                    $val = isset($c->v) ? (string) $c->v : '';

                    if ($type === 's' && isset($sharedStrings[(int) $val])) {
                        $cellValue = $sharedStrings[(int) $val];
                    } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                        $cellValue = (string) $c->is->t;
                    } else {
                        $cellValue = $val;
                    }

                    $cellRef = isset($attr['r']) ? (string) $attr['r'] : '';
                    preg_match('/([A-Z]+)/', $cellRef, $matches);
                    $colLetters = $matches[1] ?? '';
                    $colIndex = 0;
                    for ($i = 0; $i < strlen($colLetters); $i++) {
                        $colIndex = $colIndex * 26 + (ord($colLetters[$i]) - 64);
                    }
                    $colIndex = max(0, $colIndex - 1);
                    $rowData[$colIndex] = trim($cellValue);
                }
                if (!empty($rowData)) {
                    $maxKey = max(array_keys($rowData));
                    $fullRow = [];
                    for ($k = 0; $k <= $maxKey; $k++) {
                        $fullRow[$k] = $rowData[$k] ?? '';
                    }
                    $rows[] = $fullRow;
                }
            }

            $zip->close();
            return $rows;
        }

        return [];
    }
}
