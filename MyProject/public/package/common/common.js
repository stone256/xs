
//requires jQuery
var common ={
	
	mask:{
		id:'__mask_for_loading',
		up:function(blank){
			//common.mask.id = ("mask_"+Math.random()).replace('.','');
			//create div
			common.mask.down();
			var w=$(window).width();
			var h=$(window).height();
			var html = '<div id="'+common.mask.id+'" style="z-index:50000;top:0;left:0;width:'+w+'px;height:'+h+'px;position:fixed; opacity:0.7;background:#000" > <div style="margin-top:'+(h/2)+'px;margin-left:'+(w/2)+'px" class="loader"></div> </div>' ;
			
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