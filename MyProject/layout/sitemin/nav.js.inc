
<script>
var open_counter =0;
function open_counter_change(i){
	open_counter += i;
	if(open_counter==0) {
		$('.sub-menu').removeClass('open').addClass('close');
		$('.down').removeClass('down').addClass('up');
	}
}

$(document).ready(function(){
	/**** for message alert ****/
	setTimeout(function(){
		$.ajax({
			url: "/sitemin/user/message?cmd=lead",
			dataType: 'json',
			//data: $(e.form).serialize(),
			success: function(data) {
				$('.s_message .badge').html(data.unviewed)
				if(data.unviewed >0){
					var con = '<ul class="mlevel_1 sub-menu close">';
					for(var k in  data.msg){
						con += '<li><a href="/sitemin/dashboard">'+data.msg[k].message+'</a></li>';
					}
					con += '</ul>';
					$('.s_message .badge').parent().parent().parent().append(con).click(function(){ $(this).find('ul').css({display:''}) }).addClass('has-sub system_message');
				}

			}
		});

	}, 100);

	$('#cssmenu').on('click', '.has-sub > a', function(event){
		event.preventDefault();
		$(this).parent().addClass('down');
		$(this).parent().siblings().removeClass('down').addClass('up');
		$(this).parent().siblings().find('.sub-menu').removeClass('open').addClass('close');
		$(this).next().removeClass('close').addClass('open');
	});

	$('#cssmenu  *').mouseover(function(){open_counter_change(1);}).mouseout(function(){setTimeout(function(){  open_counter_change(-1);},1000)});

	//$(".sitemin-content-wrapper").css({"margin-top":$("#cssmenu").height()+"px"})

	$('.mobile').on('click', function(event){
		$('#cssmenu').slideToggle(100);
	});


	$('.nav-space-holder').height($('.wrapper--nav').height());
})

</script>
