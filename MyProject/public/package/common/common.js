
//requires jQuery
var common ={
	
	mask:{
		id:'__mask_for_loading',
		up:function(con){
			common.mask.down();
			var w=$(window).width();
			var h=$(window).height();
            con = con || '';
			var html = '<div id="'+common.mask.id+'" style="z-index:50000;top:0;left:0;width:'+w+'px;height:'+h+'px;position:fixed; opacity:0.7;background:#000;text-align:center" > <div style="margin-top:'+(h/2)+'px;margin-left:'+(w/2-16)+'px" class="loader"></div><b style="line-height:4px;opacity:1;color:#ffff00">'+con+'</b></div>' ;
			
//				var html = '<div id="'+common.mask.id+'" style="z-index:50000;top:0;left:0;width:'+w+'px;height:'+h+'px;position:fixed; opacity:0.6;background:#eee url(/media/image/ajax-loader.gif) 50% 50% no-repeat;" ></div>' ;
			
			$('body').append(html)
			$('#'+common.mask.id).width();
			return true;
		},
		down:function(){
			$('#'+common.mask.id).remove();
		}
		
	}
	
	
	
}