<?php

namespace App\Services;

use App\Models\Dorm;
use App\Models\DormAllocation;
use App\Models\DormBed;
use App\Models\DormRoom;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class HostelService
{
    public function createRoom(Dorm $dorm, array $data): DormRoom
    {
        return DB::transaction(function () use ($dorm, $data) {
            $room = $dorm->rooms()->create($data);
            $dorm->increment('room_count');
            return $room;
        });
    }

    public function updateRoom(DormRoom $room, array $data): DormRoom
    {
        $room->update($data);
        return $room;
    }

    public function createBeds(DormRoom $room, array $labels): void
    {
        DB::transaction(function () use ($room, $labels) {
            $beds = [];
            foreach ($labels as $label) {
                $beds[] = [
                    'label' => $label,
                    'dorm_id' => $room->dorm_id,
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DormBed::insert(array_map(function ($bed) use ($room) {
                return array_merge($bed, ['dorm_room_id' => $room->id]);
            }, $beds));

            $room->increment('bed_count', count($labels));
            $room->dorm->increment('bed_count', count($labels));
        });
    }

    public function assignBed(StudentRecord $student, DormBed $bed, ?string $notes = null): DormAllocation
    {
        return DB::transaction(function () use ($student, $bed, $notes) {
            if ($bed->status !== 'available' || ! $bed->is_active) {
                throw ValidationException::withMessages([
                    'dorm_bed_id' => 'Selected bed is not available.',
                ]);
            }

            if ($student->current_allocation_id && optional($student->currentAllocation)->status === 'active') {
                throw ValidationException::withMessages([
                    'dorm_bed_id' => 'Student already has an active allocation. Vacate it first.',
                ]);
            }

            $allocation = DormAllocation::create([
                'student_record_id' => $student->id,
                'dorm_id' => $bed->dorm_id,
                'dorm_room_id' => $bed->dorm_room_id,
                'dorm_bed_id' => $bed->id,
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
                'status' => 'active',
                'notes' => $notes,
            ]);

            $bed->update([
                'status' => 'occupied',
                'current_allocation_id' => $allocation->id,
            ]);

            $student->update([
                'dorm_id' => $bed->dorm_id,
                'dorm_room_id' => $bed->dorm_room_id,
                'dorm_bed_id' => $bed->id,
                'current_allocation_id' => $allocation->id,
                'allocation_status' => 'assigned',
                'dorm_room_no' => $bed->room->name,
            ]);

            return $allocation;
        });
    }

    public function vacateBed(StudentRecord $student, ?string $notes = null): void
    {
        DB::transaction(function () use ($student, $notes) {
            $allocation = $student->currentAllocation;
            if (! $allocation || $allocation->status !== 'active') {
                throw ValidationException::withMessages([
                    'allocation' => 'No active allocation found for this student.',
                ]);
            }

            $allocation->update([
                'status' => 'vacated',
                'vacated_at' => now(),
                'vacated_by' => Auth::id(),
                'notes' => $notes,
            ]);

            $bed = $allocation->bed;
            if ($bed) {
                $bed->update([
                    'status' => 'available',
                    'current_allocation_id' => null,
                ]);
            }

            $student->update([
                'dorm_bed_id' => null,
                'current_allocation_id' => null,
                'allocation_status' => 'vacated',
            ]);
        });
    }

    public function bedsByRoom(int $roomId)
    {
        return DormBed::where('dorm_room_id', $roomId)
            ->orderBy('label')
            ->get(['id', 'label', 'status', 'is_active']);
    }
}
