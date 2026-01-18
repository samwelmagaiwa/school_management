<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Vendor;
use Illuminate\Http\Request;
use App\Helpers\Qs; // For Qs::jsonStoreOk() usage if needed, or back()

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('teamAccount');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:vendors,name',
            'contact_person' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'address' => 'nullable|string',
        ]);

        Vendor::create($data);

        return back()->with('flash_success', 'Vendor created successfully.');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:vendors,name,'.$vendor->id,
            'contact_person' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'address' => 'nullable|string',
        ]);

        $vendor->update($data);

        return back()->with('flash_success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->expenses()->exists()) {
            return back()->with('flash_danger', 'Cannot delete vendor because they have associated expenses.');
        }

        $vendor->delete();
        return back()->with('flash_success', 'Vendor deleted successfully.');
    }
}
