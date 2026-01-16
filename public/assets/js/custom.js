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
