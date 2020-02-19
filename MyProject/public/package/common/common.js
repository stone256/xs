
//requires jQuery
var common ={

	mask:{
		id:'__mask_for_loading',
		on:false,
		up:function(con){
			if(common.mask.on) return;
			common.mask.on = true;
			var w=$(window).width();
			var h1=$(window).height();
			var h2=$(document).height();
			var h = Math.max(h1, h2);
			var loader = con ? '' : '<div style="margin-top:'+(h/2)+'px;margin-left:'+(w/2-16)+'px" class="loader"></div>' ;
			var opacity = con ? "1" : "0.7"
			var msg = con ? '<b class="blinking" style="line-height:4px;font-weight:400;color:#fff"><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>'+(con || '')+'</b>' : '';
			var html = '<div id="'+common.mask.id+'" style="z-index:500;top:0;left:0;width:'+w+'px;height:'+h+'px;position:fixed; opacity:'+opacity+';background:#444;text-align:center" >'+loader+msg+'</div>' ;
			$('body').append(html)
			$('#'+common.mask.id).width();
			return true;
		},
		down:function(){
			common.mask.on = false;
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
	random : (min, max)=> {
	  return Math.floor(Math.random() * (max - min)) + min;
  	},
	ND:(start, end)=>{
		start = start || 0
		end = end || 1
		let u = 0, v = 0;
		while(u === 0) u = Math.random(); //Converting [0,1) to (0,1)
		while(v === 0) v = Math.random();
		let num = Math.sqrt( -2.0 * Math.log( u ) ) * Math.cos( 2.0 * Math.PI * v );
		num = num / 10.0 + 0.5; // Translate to 0 -> 1
		if (num > 1 || num < 0) return common.ND(start, end); // resample between 0 and 1
		return parseInt(start+num*end);
	}

}

function _d(a){
	console.log(a);
}
