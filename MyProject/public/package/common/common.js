
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

	},
	sound:{	//AV alerts
		_a:false ,
		_alert:{ok:{frequency:2120,duration:200}, alarm:{frequency:900,duration:200}, error:{frequency:390, duration:450}, ping:{frequency:[555, 555], duration:[200, 140]}, keyin:{frequency:15, duration:35}},
		ok:()=>{
			//	navigator.vibrate(app._alert.error.duration);
			common.sound.beep(600,common.sound._alert.ok.frequency,common.sound._alert.ok.duration);
			return true;
		},
		alarm:()=>{
			common.sound.beep(600,common.sound._alert.alarm.frequency,common.sound._alert.ok.duration);
				return true;
			},
		error: function(){
			$( "body" ).effect( "highlight");
			common.sound.beep(600, common.sound._alert.error.frequency,common.sound._alert.error.duration);
			return false;
		},
		ping: function(stop){
			var v = stop ? 1 :0;
			common.sound.beep(600, common.sound._alert.ping.frequency[v],common.sound._alert.ping.duration[v]);
			if(!stop) setTimeout(function(){ common.sound.alert.ping(1); }, 430);
			return false;
		},
		keyin: function(){
			common.sound.beep(600, common.sound._alert.keyin.frequency,common.sound._alert.keyin.duration);
			return false;
		},
		beep:(vol, freq, duration)=>{
			var a = common.sound._a || new AudioContext();
			var v=a.createOscillator()
			var u=a.createGain()
			v.connect(u)
			v.frequency.value=freq
			v.type="sine"
			u.connect(a.destination)
			u.gain.value=vol*0.01
			v.start(a.currentTime)
			v.stop(a.currentTime+duration*0.001)
		},
	},



}

function _d(a){
	console.log(a);
}
