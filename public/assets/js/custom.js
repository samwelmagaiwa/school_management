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
