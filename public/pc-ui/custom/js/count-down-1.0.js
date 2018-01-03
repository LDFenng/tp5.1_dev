/***
 * V:1.0.0
 * LDF:www.my-top.top/ace;
 */
;(function($) {
	var settings = {
		type:'clockTime', //默认时钟		
		startTime:'',
		dayUnit:'天',
		hourUnit:'时',
		minuteUnit:'分',
		secondsUnit:'秒',
		dayClass:'',
		hourClass:'',
		minuteClass:'',
		secondClass:'',
		timesClass:'', 
		apart:1,
		timeEnd:function(data){},
		isScale:false,  //是否显示钟表刻度（仅钟表有效）
		isShowDW:true, //是否显示日期和星期
		isShowMill:true,  //是否显示毫秒盘
		isShowLOGO:true,  //是否开启显示logo
		logo:'LDF', //打开显示时有效
		web:'my-top.top'
	};
	$.fn.timesHandle = function(options) {
		settings = $.extend(settings, options || {}); // 合并默认和用户配置
		// 这里return也是为了保证链式调用
		// 并且each方法会遍历所有DOM对象，使得我们可以单个处理包装集中的所有DOM对象
		return this.each(function() { 
			// 这里的this是一个DOM对象 转化为JQuery对象  
			init($(this));
		});
		//如果你的插件很复杂，使用更多的函数分割逻辑是个好方法
		function init(target) {
			//这里写插件的逻辑，可以动态添加DOM节点和为节点添加CSS样式等
	        if (methods[settings.type]) {
	            return methods[settings.type](target);
	        } else if (typeof settings.type === 'object' || !settings.type) {
	            return methods['clockTime'](target);
	        } else {
	            $.error('Method' + method + 'does not exist');
	        }
	        target=null;
		}
	}
	
	var methods={
	    //倒计时		
		countDown:function(target){
			var starttime=new Date(settings.startTime),  //JQ 时间戳
			old_day='',old_hour='',old_minute='',old_seconds='';
			if(!settings.isCSS){
				var tpl="<span class='day "+settings.dayClass+"'></span><span class='hour "+settings.hourClass+"'></span><span class='minute "+settings.minuteClass+"'></span><span class='seconds "+settings.secondClass+"'></span><span class='times "+settings.timesClass+"'></span>";
			}
			else{
				var tpl="";
			}
			if(starttime-new Date()<0){
				alert('倒计时不可出现负数');return false;
			}
			$(target).html(tpl);
			setInterval(function (){
				var nowtime = new Date(),
				i=0;
				fiff_time = starttime - nowtime;
				if(fiff_time<=0){
					stopTime(target,'countDown');	  
					settings.timeEnd(target);
					return false;
				}
				var day = parseInt(fiff_time /1000/ 60 / 60 / 24),
				hour = parseInt(fiff_time /1000/ 60 / 60 % 24),
				minute = parseInt(fiff_time /1000/ 60 % 60),
				seconds = parseInt(fiff_time /1000% 60);
				if(day!=old_day){
					$(target).children('.day').html(day+settings.dayUnit);
				}
				if(hour!=old_hour){
					$(target).children('.hour').html(hour+settings.hourUnit);
				}
				if(minute!=old_minute){
					$(target).children('.minute').html(minute+settings.minuteUnit);
				}
				//秒数计算
				setInterval(function(){
					if(i<settings.apart){
						// $("#"+element_id).html("<span>"+day+"天"+hour+"时"+minute+"分"+seconds+"秒"+i+"</span>");
						if(seconds!=old_seconds){
							$(target).children('.minute').html(minute+settings.minuteUnit);
						}
						$(target).children('.seconds').html(seconds+settings.secondsUnit);
						if(settings.apart!=1){
							$(target).children('.times').html(i);
						}	
						i++;
				  }
				},1000/settings.apart);
				//赋值保存
				  old_day=day,old_hour=hour,old_minute=minute,old_seconds=seconds;
			},1000);
		},
		//时钟
		clockTime:function(target){
			var clock_tpl=clockTemplate();
			$(target).html(clock_tpl); 	
			setInterval(function() {
			    var time = new Date(),
				h = time.getHours() % 12;  
				m = time.getMinutes();  
				s = time.getSeconds(); 
				$(target).find(".second-hand").css({
					"-moz-transform" : "rotate("+s*6+"deg)", 
					"-webkit-transform" : "rotate("+s*6+"deg)",
					"webkitTransform":"rotate("+s*6+"deg)",
					"transform":"rotate("+s*6+"deg)"
				});
				$(target).find(".minute-hand").css({
					"-moz-transform" : "rotate("+m*6+"deg)", 
					"-webkit-transform" : "rotate("+m*6+"deg)",
					"webkitTransform":"rotate("+m*6+"deg)",
					"transform":"rotate("+m*6+"deg)"
				});
				$(target).find(".hour-hand").css({
					"-moz-transform" : "rotate("+( h*30+parseInt(m/6)*3 )+"deg)", 
					"-webkit-transform" : "rotate("+( h*30+parseInt(m/6)*3 )+"deg)",
					"webkitTransform":"rotate("+( h*30+parseInt(m/6)*3 )+"deg)",
					"transform":"rotate("+( h*30+parseInt(m/6)*3 )+"deg)"
				});
			}, 1000);  
		},
		//计时器
		timer:function(target){
			var tpl=timesTemplate();
			$(target).html(tpl);
			$(target).find(".count-timer").click(function(){
				countTime(target);
			})
			$(target).find(".start-timer").click(function(){
				startTime(target);
			})
		}
	}
	/**
	 * 时钟模板
	 */
	var clockTemplate=function (){
	    var base_tpl="<div class='clock-domai'>"
			+"<ul>";
	    if(settings.isScale){  //是否加入时间刻度
	    	base_tpl+="<li class='Numerals'></li>"  
			+"<li class='Numerals'></li>"  
			+"<li class='Numerals'></li>"  
			+"<li class='Numerals'></li>"  
			+"<li class='Numerals'></li>"  
			+"<li class='Numerals'></li>"  
			+"<li class='Numerals'></li>"  
			+"<li class='Numerals'></li>"  
			+"<li class='Numerals'></li>"  
			+"<li class='Numerals'></li>";
	    }
	    base_tpl+="<li class='Num num3'>Ⅲ</li>"  
		+"<li class='Num num6'>Ⅵ</li>"  
		+"<li class='Num num9'>Ⅸ</li>"  
		+"<li class='Num num12'>Ⅻ</li>" 
		+"<li class='hour-hand'></li>"  
		+"<li class='minute-hand'></li>"  
		+"<li class='second-hand'></li>"; 
	    if(settings.isShowDW){  //是否显示日期和星期
		    var time = new Date(),
			d = time.getDate(),
			w = weekToCN(time.getDay()); //0-6；0表示周天
	    	base_tpl+="<li class='show-date'>"+d+"</li>" 
			+"<li class='show-week'>"+w+"</li>"; 
	    }
	    if(settings.isShowMill){  //是否毫秒盘
	    	base_tpl+="<li class='milli-second'></li>"; 
	    }
	    if(settings.isShowLOGO){  //是否添加logo
	    	base_tpl+="<li class='clock-logo' data-content='"+settings.web+"'>"+settings.logo+"</li>"; 
	    }			
	    base_tpl+="<li class='clock-face'></li>"  
		+"</ul>" 
		+"</div>";
		return base_tpl;
	}
	/**
	 * 星期转换中文
	 * @param num
	 * @returns
	 */
	var weekToCN=function(num){
		switch(num){
			case 0:return '日';break;
			case 1:return '一';break;
			case 2:return '二';break;
			case 3:return '三';break;
			case 4:return '四';break;
			case 5:return '五';break;
			case 6:return '六';break;
		}
	}
	/**
	 * 停止计时
	 */
	var stopTime=function (type) {
		if(type=='countDown'){
			clearInterval(methods.countDown());
		} 
	}
	
	var min=0,
	sec=0,
	ms=0,
	times=null,
	count=0;	
	var countTime=function(taget){
		if($(taget).find('.count-timer').html()=='记次'){
			if(!times){
				alert("没有开启定时器!");
				return;
			}
			count++;
			var right1="<span class='right'>"+$(taget).find('.show-time').text()+"</span>";
			var insertStr = "<div><span class='left'>记次"+count+"</span>" +right1+"</div>";
			$(taget).find(".record-log").append(insertStr);
		}else{
			min=0;
			sec=0;
			ms=0;
			count=0;
			$(taget).find('.record-log').html('');
			$(taget).find('.show-time .minute').html('00');
			$(taget).find('.show-timer .second').html('00');
			$(taget).find('.show-timer .mill').html('00');
		}
	}
	var startTime=function(taget){
		if($(taget).find('.start-timer').html()=='启动'){
			$(taget).find('.start-timer').html('停止');
			$(taget).find('.count-timer').html('记次');
			clearInterval(times);
			times=setInterval(showTime,10)
		}else{
			$(taget).find('.start-timer').html('启动');
			$(taget).find('.count-timer').html('复位');
			clearInterval(times);
		}
	}
	var showTime=function(){
		ms++;
		if(sec==60){
			min++;sec=0;
		}
		if(ms==100){
			sec++;ms=0;
		}
		var msStr=ms;
		if(ms<10){
			msStr="0"+ms;
		}
		var secStr=sec;
		if(sec<10){
			secStr="0"+sec;
		}
		var minStr=min;
		if(min<10){
			minStr="0"+min;
		}
		$(taget).find('.show-time .minute').html(minStr);
		$(taget).find('.show-time .second').html(secStr);
		$(taget).find('.show-time .mill').html(msStr);
	}
	
	var timesTemplate=function(){
		var tpl="<div class='show-time'>"
		+"<span class='minute'>00</span>"
		+"<span>:</span>"
		+"<span class='second'>00</span>"
		+"<span>:</span>"
		+"<span  class='mill'>00</span>"
		+"</div>"
		+"<div class='timer-btn'>"
		+"<button class='count-timer' type='button'>记次</button>"
		+"<button class='start-timer' type='button'>启动</button>"
		+"</div>"
		+"<!--记录显示的次数-->"
		+"<div class='record-log'>"
		+"</div>";
		return tpl;
	}
})(jQuery);