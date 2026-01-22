<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Helpers\Qs;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $d = [];
        
        // Inventory Stats
        $d['total_items'] = DB::table('inventory_items')->count();
        $d['total_categories'] = DB::table('inventory_categories')->count();
        $d['total_warehouses'] = DB::table('warehouses')->count();
        $d['low_stock_items'] = DB::table('inventory_items')->whereRaw('quantity <= min_stock_level')->count();
        $d['pending_requisitions'] = DB::table('inventory_requisitions')->where('status', 'pending')->count();

        // Recent Stock Movements
        $d['recent_movements'] = DB::table('inventory_stock_movements')
            ->join('inventory_items', 'inventory_stock_movements.item_id', '=', 'inventory_items.id')
            ->select('inventory_items.name as item_name', 'inventory_stock_movements.quantity', 'inventory_stock_movements.type', 'inventory_stock_movements.created_at')
            ->orderBy('inventory_stock_movements.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('pages.inventory.dashboard', $d);
    }
}
