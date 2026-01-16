# Module Scope & Limitations

This document captures the current scope of key modules and highlights where the implementation stops at data definition level. The goal is to set the correct expectations for stakeholders and contributors without altering the existing UI or workflows.

## Hostel / Dormitory Module

### Current Capabilities
- **Dormitory catalog**: Each dormitory record stores simple metadata such as name, gender restrictions, and optional capacity.
- **Student record fields**: `StudentRecord` captures optional references (`dorm_id`, `dorm_room_no`, `bed_no`) to describe where the student resides.
- **UI representation**: Pages display the stored dormitory values for each student without additional logic.

### Intentional Limitations
- No hierarchy of *Dorm → Rooms → Beds* — only flat dorm metadata exists.
- No enforcement of capacity, gender, or bed uniqueness rules.
- No lifecycle actions (assign, change, vacate) and no history tracking.
- No role-based workflows for hostel officers or administrators.
- No status management for beds (available, occupied, reserved, maintenance, etc.).

### Rationale
This lightweight implementation keeps the dormitory feature at the metadata level so that existing UI, reports, and seed data remain unchanged. Expanding the module into a full operational subsystem would require:
- New tables for rooms, beds, and allocation history
- Business rules that prevent double-booking and enforce gender/capacity
- Allocation workflows and dedicated permissions

Until such requirements are prioritized, the current behavior is by design and should be considered “informational only.”

---

If additional modules need similar clarifications, extend this document with sections per feature to keep expectations aligned with the codebase.
