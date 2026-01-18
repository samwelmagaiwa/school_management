<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\FeeStructure;
use App\Models\Accounting\FeeStructureTerm;
use App\Models\Accounting\FeeInstallment;
use App\Models\Accounting\FeeInstallmentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Qs;

class FeeStructureTermController extends Controller
{
    public function __construct()
    {
        $this->middleware('teamAccount');
    }

    /**
     * Store terms for a fee structure.
     * Request format:
     * {
     *   "terms": [
     *     {"name": "Term 1", "sequence": 1, "total_amount": 600000, "installments_enabled": true},
     *     {"name": "Term 2", "sequence": 2, "total_amount": 600000, "installments_enabled": false}
     *   ]
     * }
     */
    public function storeTerms(Request $request, FeeStructure $structure)
    {
        // Only admins can manage terms
        abort_unless(Qs::userIsTeamSA(), 403);

        $data = $request->validate([
            'terms' => 'required|array|min:1',
            'terms.*.name' => 'required|string|max:191',
            'terms.*.sequence' => 'required|integer|min:1',
            'terms.*.total_amount' => 'required|numeric|min:0',
            'terms.*.installments_enabled' => 'required|boolean',
        ]);

        DB::transaction(function () use ($structure, $data) {
            // Delete existing terms and their cascading data
            $structure->terms()->delete();

            // Create new terms
            foreach ($data['terms'] as $termData) {
                $structure->terms()->create($termData);
            }

            // Update structure's total year amount
            $structure->update([
                'total_year_amount' => collect($data['terms'])->sum('total_amount')
            ]);
        });

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'msg' => 'Terms saved successfully. Next: Configure Installments.']);
        }

        return back()->with('flash_success', 'Terms saved successfully.');
    }

    /**
     * Store installments for a term.
     * Request format:
     * {
     *   "installments": [
     *     {"label": "Installment 1", "fixed_amount": 300000, "due_date": "2024-01-15", "sequence": 1},
     *     {"label": "Installment 2", "fixed_amount": 300000, "due_date": "2024-02-15", "sequence": 2}
     *   ]
     * }
     */
    public function storeInstallments(Request $request, FeeStructureTerm $term)
    {
        abort_unless(Qs::userIsTeamSA(), 403);

        $data = $request->validate([
            'installments' => 'required|array|min:1',
            'installments.*.label' => 'required|string|max:191',
            'installments.*.fixed_amount' => 'required|numeric|min:0',
            'installments.*.due_date' => 'required|date',
            'installments.*.sequence' => 'required|integer|min:1',
        ]);

        // Custom validation: Sum of installments must equal term total
        $totalInstallments = collect($data['installments'])->sum('fixed_amount');
        if ($totalInstallments != $term->total_amount) {
            return back()->withErrors([
                'installments' => "Total installment amount ({$totalInstallments}) must equal term amount ({$term->total_amount})"
            ])->withInput();
        }

        // If installments disabled, only allow 1 installment
        if (!$term->installments_enabled && count($data['installments']) > 1) {
            return back()->withErrors([
                'installments' => "This term has installments disabled. Only one full payment is allowed."
            ])->withInput();
        }

        DB::transaction(function () use ($term, $data) {
            // Delete existing installments
            $term->installments()->delete();

            // Create new installments
            foreach ($data['installments'] as $installmentData) {
                $term->installments()->create([
                    'fee_structure_term_id' => $term->id,
                    'label' => $installmentData['label'],
                    'sequence' => $installmentData['sequence'],
                    'fixed_amount' => $installmentData['fixed_amount'],
                    'due_date' => $installmentData['due_date'],
                ]);
            }
        });

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'msg' => 'Installments saved successfully.']);
        }

        return back()->with('flash_success', 'Installments saved successfully.');
    }

    /**
     * Store items for an installment.
     * Request format:
     * {
     *   "items": [
     *     {"name": "Transport", "amount": 50000, "fee_item_id": 1},
     *     {"name": "Meals", "amount": 200000, "fee_item_id": 2}
     *   ]
     * }
     */
    public function storeInstallmentItems(Request $request, FeeInstallment $installment)
    {
        abort_unless(Qs::userIsTeamSA(), 403);

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:191',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.fee_item_id' => 'nullable|exists:fee_items,id',
        ]);

        // Custom validation: No duplicate item names within the same installment
    $names = collect($data['items'])->pluck('name');
    if ($names->duplicates()->isNotEmpty()) {
        $duplicate = $names->duplicates()->first();
        $msg = "Duplicate item found: '{$duplicate}'. Each item can only be added once per installment.";
        if ($request->ajax()) {
            return response()->json(['ok' => false, 'msg' => $msg]);
        }
        return back()->withErrors(['items' => $msg])->withInput();
    }

    // Custom validation: Sum of items must equal installment amount
        $totalItems = collect($data['items'])->sum('amount');
        if ($totalItems != $installment->fixed_amount) {
            if ($request->ajax()) {
                return response()->json(['ok' => false, 'msg' => "Total items amount ({$totalItems}) must equal installment amount ({$installment->fixed_amount})"]);
            }
            return back()->withErrors([
                'items' => "Total items amount ({$totalItems}) must equal installment amount ({$installment->fixed_amount})"
            ])->withInput();
        }

        DB::transaction(function () use ($installment, $data) {
            // Delete existing items
            $installment->items()->delete();

            // Create new items
            foreach ($data['items'] as $itemData) {
                $installment->items()->create($itemData);
            }
        });

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'msg' => 'Items saved successfully. Configuration complete!']);
        }

        return back()->with('flash_success', 'Items saved successfully.');
    }

    /**
     * Delete a term and all its cascading data.
     */
    public function destroyTerm(FeeStructureTerm $term)
    {
        abort_unless(Qs::userIsTeamSA(), 403);

        $term->delete();

        return back()->with('flash_success', 'Term deleted successfully.');
    }
}
