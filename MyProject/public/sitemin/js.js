(function($) {
    "use strict"; // Start of use strict

    // Scroll to top button appear
    // $(document).scroll(function() {
    //     var scrollDistance = $(this).scrollTop();
    //     if (scrollDistance > 100) {
    //         $('.scroll-to-top').fadeIn();
    //     } else {
    //         $('.scroll-to-top').fadeOut();
    //     }
    // });
    //
    // // Smooth scrolling using jQuery easing
    // $(document).on('click', 'a.scroll-to-top', function(event) {
    //     var $anchor = $(this);
    //     $('html, body').stop().animate({
    //         scrollTop: ($($anchor.attr('href')).offset().top)
    //     }, 1000, 'easeInOutExpo');
    //     event.preventDefault();
    // });
    //

	if(_keep_login == 1){
		setTimeout(function(){
			setInterval(function(){
					$.ajax({ url: '/sitemin/keepalive',
						success: function(ret){}
					});
				}, 300000);
		}, 800);
	}

})(jQuery); // End of use strict
