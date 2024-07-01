jQuery(document).ready(function($){
    $('.js-seto-export-csv').click(function( event ){
        event.preventDefault();

        $(this).closest(".seto-stats").find('table').tableExport({
            type:'csv',
            fileName: 'search-tools-export'
        });
    });
});