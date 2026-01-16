<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dorm\DormAllocationRequest;
use App\Models\DormBed;
use App\Models\StudentRecord;
use App\Services\HostelService;
use Illuminate\Http\Request;

class DormAllocationController extends Controller
{
    public function __construct(protected HostelService $hostel)
    {
        $this->middleware(['custom.hostel']);
    }

    public function store(DormAllocationRequest $request, StudentRecord $student)
    {
        $bed = DormBed::findOrFail($request->dorm_bed_id);
        $this->hostel->assignBed($student, $bed, $request->notes);

        return Qs::jsonStoreOk();
    }

    public function vacate(Request $request, StudentRecord $student)
    {
        $this->hostel->vacateBed($student, $request->input('notes'));

        return Qs::jsonUpdateOk();
    }
}
