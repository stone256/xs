jQuery(document).ready(function(){

	jQuery('.folder').click(function(ev){
		if(this.className.match(/examples_menu/)) return true;
		ev.stopPropagation();
		//ev.preventDefault();
		if(this.className.match(/open/)){
			this.className="folder";
		}else{
			this.className="folder open";
		}
		return true;
	})


          $('[data-toggle="offcanvas"]').click(function () {
            $('.row-offcanvas').toggleClass('active')
          });


          //open prent folder in menu
          $('.examples_menu.open').parents('.examples_menu').addClass('open');



});
       // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                document.getElementById("gotop").style.display = "block";
            } else {
                document.getElementById("gotop").style.display = "none";
            }
        }

        // When the user clicks on the button, scroll to the top of the document
        function topFunction() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
        }
