<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockMovement;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function create()
    {
        $data['items'] = Item::where('status', 'Active')->get();
        $data['warehouses'] = Warehouse::all();
        
        return view('pages.inventory.stocks.create', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'warehouse_id' => 'required|exists:inventory_warehouses,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Find or create stock record
        // Handle batch_number carefully - null values need special treatment
        $searchCriteria = [
            'item_id' => $validated['item_id'],
            'warehouse_id' => $validated['warehouse_id'],
        ];
        
        // Only add batch_number to search if it's provided
        if (isset($validated['batch_number'])) {
            $searchCriteria['batch_number'] = $validated['batch_number'];
            $stock = Stock::firstOrNew($searchCriteria);
        } else {
            // For null batch, find stock with null batch or create new
            $stock = Stock::where('item_id', $validated['item_id'])
                ->where('warehouse_id', $validated['warehouse_id'])
                ->whereNull('batch_number')
                ->first();
            
            if (!$stock) {
                $stock = new Stock($searchCriteria);
                $stock->batch_number = null;
            }
        }

        // Update quantity
        $stock->quantity = ($stock->quantity ?? 0) + $validated['quantity'];
        
        if (isset($validated['expiry_date'])) {
            $stock->expiry_date = $validated['expiry_date'];
        }
        
        $stock->save();

        // Log the movement
        StockMovement::create([
            'item_id' => $validated['item_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'user_id' => Auth::id(),
            'type' => 'receive',
            'quantity' => $validated['quantity'],
            'reference' => $validated['reference'] ?? 'STOCK-IN-' . now()->format('YmdHis'),
            'description' => $validated['description'] ?? 'Stock received into warehouse',
        ]);

        return back()->with('flash_success', 'Stock received successfully!');
    }
}
