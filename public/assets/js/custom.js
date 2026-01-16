document.addEventListener('DOMContentLoaded', function() {

    // Initialize date pickers where used
    $('.date-pick').datepicker();

    // Update Bootstrap custom-file-input label with selected file name(s)
    // so that the user immediately sees which image has been chosen
    // (e.g. "ivan_picha01.jpeg") before submitting the form.
    $(document).on('change', '.custom-file-input', function () {
        var fileName = '';

        if (this.files && this.files.length > 1) {
            var names = [];
            for (var i = 0; i < this.files.length; i++) {
                names.push(this.files[i].name);
            }
            fileName = names.join(', ');
        } else if (this.files && this.files[0]) {
            fileName = this.files[0].name;
        } else {
            fileName = 'Choose image...';
        }

        $(this).siblings('.custom-file-label').addClass('selected').text(fileName);
    });

});

$(document).on('change', '.js-dorm-select', function () {
    var dormId = $(this).val();
    var targetRoom = $($(this).data('target-room'));
    var targetBed = $($(this).data('target-bed'));

    if (!targetRoom.length || !targetBed.length) {
        return;
    }

    targetRoom.empty().append('<option value="">Loading...</option>');
    targetBed.empty().append('<option value="">Select room first</option>');

    if (!dormId) {
        targetRoom.html('<option value="">Select dorm first</option>');
        return;
    }

    $.get('/ajax/dorms/' + dormId + '/rooms', function (rooms) {
        targetRoom.empty().append('<option value="">Select room</option>');
        rooms.forEach(function (room) {
            targetRoom.append('<option value="' + room.id + '">' + room.name + ' (' + room.bed_count + ' beds)</option>');
        });

        var selectedRoom = $('.js-dorm-select[data-target-room="#' + targetRoom.attr('id') + '"]').data('selected-room');
        if (selectedRoom) {
            targetRoom.val(selectedRoom).trigger('change');
            $('.js-dorm-select[data-target-room="#' + targetRoom.attr('id') + '"]').data('selected-room', null);
        }
    });
});

$(document).on('change', '.js-dorm-room', function () {
    var roomId = $(this).val();
    var targetBed = $($(this).data('target'));
    if (!targetBed.length) {
        return;
    }

    targetBed.empty().append('<option value="">Loading...</option>');

    if (!roomId) {
        targetBed.html('<option value="">Select room first</option>');
        return;
    }

    $.get('/ajax/rooms/' + roomId + '/beds', function (beds) {
        targetBed.empty().append('<option value="">Select bed</option>');
        beds.forEach(function (bed) {
            targetBed.append('<option value="' + bed.id + '">' + bed.label + ' - ' + bed.status + '</option>');
        });

        var selectedBed = $('.js-dorm-select[data-target-bed="#' + targetBed.attr('id') + '"]').data('selected-bed');
        if (selectedBed) {
            targetBed.val(selectedBed);
            $('.js-dorm-select[data-target-bed="#' + targetBed.attr('id') + '"]').data('selected-bed', null);
        }
    });
});

$(document).ready(function () {
    $('.js-dorm-select').each(function () {
        var dormId = $(this).val();
        var selectedRoom = $(this).data('selected-room');
        if (dormId && selectedRoom) {
            $(this).trigger('change');
        }
    });
});

// Load places for a selected village/street
function getPlaces(village_id) {
    var $place = $('#place_id');
    $place.empty();
    $place.append('<option value=""></option>');

    if (!village_id) {
        return;
    }

    $.get('/ajax/get_places/' + village_id, function (data) {
        if (Array.isArray(data)) {
            data.forEach(function (item) {
                $place.append('<option value="' + item.id + '">' + item.name + '</option>');
            });
        }

        // Re-init Select2/Selectpicker if used
        if ($place.hasClass('select-search')) {
            $place.select2();
        }
    });
}
