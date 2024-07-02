jQuery(document).ready(function($){
    $('.js-seto-accept-review').click(function( event ){
        // event.preventDefault();

        $.post(ajaxurl, {
            action: "disable_review_notice",
            disable: true
            }, function(data) {
                $("#seto_notice_reviews").slideUp();
            }
        );
    });

    $('.js-seto-refuse-review').click(function( event ){
        event.preventDefault();

        $.post(ajaxurl, {
            action: "disable_review_notice",
            disable: true
            }, function(data) {
                $("#seto_notice_reviews").slideUp();
            }
        );
    });
});