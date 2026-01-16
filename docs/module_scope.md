# Module Scope & Hostel Operations

With the hostel overhaul, the module now goes beyond metadata and implements a complete Dorm → Room → Bed hierarchy plus allocation workflows.

## Current Capabilities
- **Dormitory catalog**: name, description, gender, capacity, notes, aggregated room/bed counters.
- **Room management**: rooms belong to a dorm, store floor, capacity, gender override, notes, and maintain bed counts.
- **Bed inventory**: each bed is tied to a room and dorm, tracks availability status (available, occupied, reserved, maintenance) and points to the current active allocation.
- **Student allocation workflow**:
  - Assignment automatically validates conflicts, locks the bed, and updates the student record with dorm/room/bed references.
  - Vacating a bed closes the allocation history, frees the bed, and updates the student record status (`allocation_status`).
  - Allocation history (`dorm_allocations`) persists the full lifecycle including timestamps and staff responsible.
- **Role-based control**: `hostel_officer`, `admin`, and `super_admin` can manage dorm assets and allocations via middleware `custom.hostel`.
- **UI continuity**: existing layouts remain intact while embedding collapsible room/bed details and cascading dorm–room–bed selectors on student forms.
- **AJAX endpoints & JS enhancements**: cascaded selects load rooms/beds in real time without reloading the page.

## Key Entities & Tables
- `dorms`: augmented with gender, capacity, room_count, bed_count, notes.
- `dorm_rooms`: stores dorm-specific rooms plus metadata.
- `dorm_beds`: granular bed inventory with status tracking and active allocation pointer.
- `dorm_allocations`: historical records of assignments (student, dorm, room, bed, timestamps, staff).
- `student_records`: references dorm_room_id, dorm_bed_id, current_allocation_id, allocation_status while keeping legacy free-text room notes.

## Allocation Rules
- One student per active bed allocation.
- Beds cannot be double-booked; validation ensures status is `available` and previous allocations are vacated first.
- Optional manual notes remain available to maintain parity with historical data.

## Next Steps / Extensibility
- Build dedicated reports (occupancy dashboards, vacancy alerts).
- Add bulk import/export utilities for bed inventory.
- Extend permissions if more staff categories require hostel visibility.

This document replaces the prior “metadata-only” scope description and should be updated whenever hostel workflows evolve further.
