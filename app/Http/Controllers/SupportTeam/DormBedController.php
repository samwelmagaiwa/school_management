<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dorm\DormBedRequest;
use App\Models\Dorm;
use App\Models\DormBed;
use App\Models\DormRoom;
use App\Services\HostelService;

class DormBedController extends Controller
{
    public function __construct(protected HostelService $hostel)
    {
        $this->middleware(['custom.hostel']);
    }

    public function store(DormBedRequest $request, Dorm $dorm, DormRoom $room)
    {
        abort_unless($room->dorm_id === $dorm->id, 404);

        $labels = array_filter(array_map('trim', explode('\n', $request->labels)));
        $this->hostel->createBeds($room, $labels);

        return Qs::jsonStoreOk();
    }

    public function update(DormBedRequest $request, Dorm $dorm, DormRoom $room, DormBed $bed)
    {
        abort_unless($room->dorm_id === $dorm->id && $bed->dorm_id === $dorm->id, 404);

        $bed->update($request->validated());

        return Qs::jsonUpdateOk();
    }

    public function destroy(Dorm $dorm, DormRoom $room, DormBed $bed)
    {
        abort_unless($bed->dorm_room_id === $room->id && $bed->dorm_id === $dorm->id, 404);

        $bed->delete();
        $room->decrement('bed_count');
        $dorm->decrement('bed_count');

        return back()->with('flash_success', __('msg.delete_ok'));
    }
}
