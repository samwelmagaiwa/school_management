<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\FeeCategoryRequest;
use App\Models\Accounting\FeeCategory;

class FeeCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('teamAccount');
    }

    public function index()
    {
        $data['categories'] = FeeCategory::orderBy('name')->get();
        return view('pages.accountant.fees.categories', $data);
    }

    public function store(FeeCategoryRequest $request)
    {
        $payload = $request->validated();
        $data = $this->applyCodeRule($payload);
        FeeCategory::create($data);
        return Qs::jsonStoreOk();
    }

    public function update(FeeCategoryRequest $request, FeeCategory $feeCategory)
    {
        $payload = $request->validated();
        unset($payload['code']);
        $data = $this->applyCodeRule($payload, $feeCategory);
        $feeCategory->update($data);
        return Qs::jsonUpdateOk();
    }

    public function destroy(FeeCategory $feeCategory)
    {
        $feeCategory->delete();

        if (request()->expectsJson()) {
            return Qs::jsonDeleteOk();
        }

        return Qs::deleteOk('accounting.fee-categories.index');
    }

    protected function applyCodeRule(array $data, ?FeeCategory $existing = null): array
    {
        $name = strtoupper(trim($data['name'] ?? ''));

        if (! $existing) {
            $data['code'] = $this->generateCode($name);
        } else {
            $data['code'] = $existing->code ?: $this->generateCode($name);
        }

        return $data;
    }

    protected function generateCode(string $name): string
    {
        $base = substr(str_replace(' ', '', $name ?: 'CAT'), 0, 3) ?: 'CAT';
        $base = strtoupper($base);
        $counter = 1;

        do {
            $candidate = sprintf('%s%03d', $base, $counter);
            $exists = FeeCategory::where('code', $candidate)->exists();
            $counter++;
        } while ($exists);

        return $candidate;
    }
}
