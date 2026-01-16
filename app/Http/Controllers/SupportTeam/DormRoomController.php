<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dorm\DormRoomRequest;
use App\Models\Dorm;
use App\Models\DormRoom;
use App\Services\HostelService;

class DormRoomController extends Controller
{
    public function __construct(protected HostelService $hostel)
    {
        $this->middleware(['custom.hostel']);
    }

    public function store(DormRoomRequest $request, Dorm $dorm)
    {
        $room = $this->hostel->createRoom($dorm, $request->validated());

        if ($request->has('bed_labels')) {
            $labels = array_filter(array_map('trim', explode('\n', $request->bed_labels)));
            if ($labels) {
                $this->hostel->createBeds($room, $labels);
            }
        }

        return Qs::jsonStoreOk();
    }

    public function update(DormRoomRequest $request, Dorm $dorm, DormRoom $room)
    {
        abort_unless($room->dorm_id === $dorm->id, 404);

        $this->hostel->updateRoom($room, $request->validated());

        return Qs::jsonUpdateOk();
    }

    public function destroy(Dorm $dorm, DormRoom $room)
    {
        abort_unless($room->dorm_id === $dorm->id, 404);

        $room->delete();

        $dorm->decrement('room_count');

        return back()->with('flash_success', __('msg.delete_ok'));
    }
}
