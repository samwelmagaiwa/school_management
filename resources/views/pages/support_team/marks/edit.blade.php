<form class="ajax-update" action="{{ route('marks.update', [$exam_id, $my_class_id, $section_id, $subject_id]) }}" method="post">
    @csrf @method('put')
    <table class="table table-striped">
        <thead>
        <tr>
            <th>S/N</th>
            <th>Name</th>
            <th>ADM_NO</th>
            <th>1ST CA (20)</th>
            <th>2ND CA (20)</th>
            <th>EXAM (60)</th>
            <th>Absent?</th>
            <th>Reason</th>
        </tr>
        </thead>
        <tbody>
        @foreach($marks->sortBy('user.name') as $mk)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $mk->user->name }} </td>
                <td>{{ $mk->user->student_record->adm_no }}</td>

{{--                CA AND EXAMS --}}
                <td><input title="1ST CA" min="1" max="20" class="text-center mark-input" id="t1_{{ $mk->id }}" name="t1_{{ $mk->id }}" value="{{ $mk->t1 }}" type="number" {{ $mk->is_absent ? 'disabled' : '' }}></td>
                <td><input title="2ND CA" min="1" max="20" class="text-center mark-input" id="t2_{{ $mk->id }}" name="t2_{{ $mk->id }}" value="{{ $mk->t2 }}" type="number" {{ $mk->is_absent ? 'disabled' : '' }}></td>
                <td><input title="EXAM" min="1" max="60" class="text-center mark-input" id="exm_{{ $mk->id }}" name="exm_{{ $mk->id }}" value="{{ $mk->exm }}" type="number" {{ $mk->is_absent ? 'disabled' : '' }}></td>
                <td class="text-center">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input absence-checkbox" name="absent_{{ $mk->id }}" value="1" data-mark-id="{{ $mk->id }}" {{ $mk->is_absent ? 'checked' : '' }}>
                    </label>
                </td>
                <td>
                    <select class="form-control form-control-sm absence-reason" id="reason_{{ $mk->id }}" name="reason_{{ $mk->id }}" {{ !$mk->is_absent ? 'disabled' : '' }}>
                        <option value="">-- Select Reason --</option>
                        <option value="Sick" {{ $mk->exemption_reason == 'Sick' ? 'selected' : '' }}>Sick</option>
                        <option value="Suspended" {{ $mk->exemption_reason == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="Family Emergency" {{ $mk->exemption_reason == 'Family Emergency' ? 'selected' : '' }}>Family Emergency</option>
                        <option value="Medical Appointment" {{ $mk->exemption_reason == 'Medical Appointment' ? 'selected' : '' }}>Medical Appointment</option>
                        <option value="Other" {{ $mk->exemption_reason == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <div>
            <button type="button" class="btn btn-secondary btn-sm" id="markAllAbsent">
                <i class="icon-user-block"></i> Mark All Absent
            </button>
            <button type="button" class="btn btn-secondary btn-sm" id="markAllPresent">
                <i class="icon-checkmark3"></i> Mark All Present
            </button>
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Update Marks <i class="icon-paperplane ml-2"></i></button>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    // Handle absence checkbox - disable/enable mark inputs
    $('.absence-checkbox').on('change', function() {
        var markId = $(this).data('mark-id');
        var isChecked = $(this).is(':checked');
        
        // Toggle disabled state for mark inputs
        $('#t1_' + markId).prop('disabled', isChecked);
        $('#t2_' + markId).prop('disabled', isChecked);
        $('#exm_' + markId).prop('disabled', isChecked);
        
        // Toggle reason dropdown
        $('#reason_' + markId).prop('disabled', !isChecked);
        
        // Clear values if marking as absent
        if (isChecked) {
            $('#t1_' + markId).val('');
            $('#t2_' + markId).val('');
            $('#exm_' + markId).val('');
        } else {
            $('#reason_' + markId).val('');
        }
    });
    
    // Mark all students absent
    $('#markAllAbsent').on('click', function() {
        if (confirm('Mark all students as absent?')) {
            $('.absence-checkbox').each(function() {
                if (!$(this).is(':checked')) {
                    $(this).prop('checked', true).trigger('change');
                }
            });
        }
    });
    
    // Mark all students present
    $('#markAllPresent').on('click', function() {
        if (confirm('Mark all students as present?')) {
            $('.absence-checkbox').each(function() {
                if ($(this).is(':checked')) {
                    $(this).prop('checked', false).trigger('change');
                }
            });
        }
    });
    
    // Basic validation
    $('.mark-input').on('blur', function() {
        var val = parseInt($(this).val());
        var max = parseInt($(this).attr('max'));
        
        if (val > max) {
            $(this).val(max);
            alert('Value cannot exceed ' + max);
        }
    });
});
</script>

