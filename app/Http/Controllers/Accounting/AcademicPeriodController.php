<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\AcademicPeriodRequest;
use App\Models\Accounting\AcademicPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AcademicPeriodController extends Controller
{
    public function __construct()
    {
        $this->middleware(['teamAccount', 'teamSA']);
    }

    public function index(): View
    {
        $periods = AcademicPeriod::orderBy('ordering')->get();
        $data['periods'] = $periods;
        $data['nextOrdering'] = ($periods->max('ordering') ?? 0) + 1;
        return view('pages.accountant.fees.periods', $data);
    }

    public function store(AcademicPeriodRequest $request): \Symfony\Component\HttpFoundation\Response
    {
        $payload = $request->validated();
        $payload['code'] = $payload['code'] ?: $this->generateCode($payload['name'] ?? 'period');
        AcademicPeriod::create($payload);
        return Qs::jsonStoreOk();
    }

    public function update(AcademicPeriodRequest $request, AcademicPeriod $period): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $payload = $request->validated();
            $payload['code'] = $payload['code'] ?: $period->code;
            $period->update($payload);
            return Qs::jsonUpdateOk();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) { // 1062 is standard for Duplicate Entry
                return response()->json(['message' => 'Duplicate entry: A period with this code or name already exists.', 'errors' => ['code' => ['Duplicate entry detected.']]], 500); 
            }
            throw $e;
        }
    }

    protected function generateCode(string $name): string
    {
        $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', Str::slug($name, '')));
        if (! $base) {
            $base = 'PERIOD';
        }
        $base = substr($base, 0, 5);

        $counter = 1;
        do {
            $suffix = str_pad((string) $counter, 3, '0', STR_PAD_LEFT);
            $candidate = $base . $suffix;
            $exists = AcademicPeriod::where('code', $candidate)->exists();
            $counter++;
        } while ($exists);

        return $candidate;
    }

    public function destroy(AcademicPeriod $period): RedirectResponse
    {
        if ($period->is_locked) {
            return back()->with('flash_warning', 'Locked periods cannot be deleted.');
        }

        $period->delete();
        return Qs::deleteOk('accounting.periods.index');
    }
}
