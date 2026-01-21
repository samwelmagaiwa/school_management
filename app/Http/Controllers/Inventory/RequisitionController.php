<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Requisition;
use App\Models\Inventory\RequisitionItem;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockMovement;
use App\Helpers\Qs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RequisitionController extends Controller
{
    public function index()
    {
        // Users see their own, Storekeepers/Admins see all
        $user = Auth::user();
        if($user->user_type == 'storekeeper' || $user->user_type == 'super_admin' || $user->user_type == 'admin') {
            $d['reqs'] = Requisition::with(['requester', 'items.item'])->latest()->get();
        } else {
            $d['reqs'] = Requisition::with(['items.item'])->where('requester_id', $user->id)->latest()->get();
        }
        
        return view('pages.inventory.requisitions.index', $d);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'date_needed' => 'required|date',
            'reason' => 'required|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        // Create Header
        $requisition = Requisition::create([
            'reference_code' => 'REQ-' . time(),
            'requester_id' => Auth::id(),
            'date_needed' => $data['date_needed'],
            'reason' => $data['reason'],
            'status' => 'Pending',
        ]);

        // Create Items
        foreach($data['items'] as $item) {
            RequisitionItem::create([
                'requisition_id' => $requisition->id,
                'item_id' => $item['item_id'],
                'quantity_requested' => $item['qty'],
            ]);
        }

        return Qs::jsonStoreOk();
    }

    public function approve($id)
    {
        $req = Requisition::find($id);
        if($req->status != 'Pending') return Qs::json('Already processed', false);

        $req->update([
            'status' => 'Approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        
        return Qs::jsonUpdateOk();
    }

    public function issue(Request $request, $id)
    {
        $requisition = Requisition::with('items.item')->findOrFail($id);
        
        if($requisition->status !== 'Approved') {
            return back()->with('flash_danger', 'Only approved requisitions can be issued.');
        }

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:inventory_warehouses,id',
        ]);

        // Check stock availability
        foreach($requisition->items as $reqItem) {
            $stock = Stock::where('item_id', $reqItem->item_id)
                ->where('warehouse_id', $validated['warehouse_id'])
                ->first();
            
            if (!$stock || $stock->quantity < $reqItem->quantity_requested) {
                return back()->with('flash_danger', 'Insufficient stock for item: ' . $reqItem->item->name);
            }
        }

        // Deduct stock and log movements
        foreach($requisition->items as $reqItem) {
            $stock = Stock::where('item_id', $reqItem->item_id)
                ->where('warehouse_id', $validated['warehouse_id'])
                ->first();
            
            $stock->quantity -= $reqItem->quantity_requested;
            $stock->save();

            // Log movement
            StockMovement::create([
                'item_id' => $reqItem->item_id,
                'warehouse_id' => $validated['warehouse_id'],
                'user_id' => Auth::id(),
                'type' => 'issue',
                'quantity' => -$reqItem->quantity_requested,
                'reference' => $requisition->reference_code,
                'description' => 'Issued against requisition: ' . $requisition->reference_code,
            ]);
        }

        // Update requisition status
        $requisition->update([
            'status' => 'Issued',
        ]);

        return back()->with('flash_success', 'Stock issued successfully!');
    }
}
