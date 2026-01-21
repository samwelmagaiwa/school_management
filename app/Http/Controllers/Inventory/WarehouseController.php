<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\Stock;
use App\Helpers\Qs;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $d['warehouses'] = Warehouse::with('keeper')->get();
        return view('pages.inventory.warehouses.index', $d);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string',
            'keeper_id' => 'nullable|exists:users,id',
        ]);

        Warehouse::create($data);
        return Qs::jsonStoreOk();
    }

    public function show($id)
    {
        $warehouse = Warehouse::with(['stocks.item', 'assets.item'])->find($id);
        if(!$warehouse) return back()->with('flash_danger', 'Warehouse not found');

        $d['warehouse'] = $warehouse;
        return view('pages.inventory.warehouses.show', $d);
    }

    public function update(Request $req, $id)
    {
        $wh = Warehouse::find($id);
        $data = $req->validate([
            'name' => 'required|string',
            'keeper_id' => 'nullable|exists:users,id',
        ]);
        
        $wh->update($data);
        return Qs::jsonUpdateOk();
    }
}
