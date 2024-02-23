jQuery(document).ready(function($){
    $('.st-select2').select2({
        width: '100%'
    });

    // Load the jQuery UI datepicker.
    $('#st_exclude_date').datepicker({ 
        maxDate: new Date(),
        changeYear: true,
        dateFormat: "MM dd, yy" 
    });
});