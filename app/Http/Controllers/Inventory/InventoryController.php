<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Category;
use App\Models\Inventory\Item;
use App\Models\Inventory\Unit;
use App\Helpers\Qs;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth'); // Handled by route group
    }

    public function index()
    {
        $d['items'] = Item::with(['category', 'unit'])->get();
        $d['categories'] = Category::all();
        $d['units'] = Unit::all();
        
        return view('pages.inventory.index', $d);
    }

    public function manageCategories()
    {
        $d['categories'] = Category::all();
        return view('pages.inventory.categories', $d);
    }

    public function storeCategory(Request $req)
    {
        $data = $req->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
        ]);
        
        Category::create($data);
        return Qs::jsonStoreOk();
    }

    public function storeItem(Request $req)
    {
        $data = $req->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:inventory_categories,id',
            'unit_id' => 'nullable|exists:inventory_units,id',
            'is_asset' => 'required|boolean',
            'reorder_level' => 'nullable|integer',
        ]);

        $data['code'] = 'ITM-' . Str::upper(Str::random(6)); // Simple auto-gen
        
        Item::create($data);
        return Qs::jsonStoreOk();
    }

    public function updateItem(Request $req, $id)
    {
        $item = Item::find($id);
        if(!$item) return Qs::json('Item not found', false);

        $data = $req->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:inventory_categories,id',
            'reorder_level' => 'nullable|integer',
        ]);

        $item->update($data);
        return Qs::jsonUpdateOk();
    }
}
