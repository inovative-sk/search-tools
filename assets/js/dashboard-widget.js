jQuery(document).ready(function($){
    $(".seto-dashboard-tablink").on("click", function(e){
        e.preventDefault();

        var $href = $(this).attr("href"); 

        $(".seto-dashboard-tablink").removeClass("is-active");

        $(this).addClass("is-active");

        $($href).addClass("is-active").siblings().removeClass("is-active");
    });
});