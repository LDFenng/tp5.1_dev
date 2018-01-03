/**联动查询2版*/
(function($) {
	var select_num=0,
	default_option={
		method:'',	
		contant:null,	
		url: null,
		type: 'POST',
		pid:'0',
		cache:false,
		dataType:'json',
		valField:'id',
		title:'title',
		addClass:'',
		attrInfo:false,
		pidField:'pid',
		nameField:'select_id',
		maxLevle:20
	};
	
	$.fn.linkSelect = function(options) {
        // 方法调用
		settings = $.extend({}, default_option, options);
		// 并且each方法会遍历所有DOM对象，使得我们可以单个处理包装集中的所有DOM对象
		return this.each(function() { 
			// 这里的this是一个DOM对象 转化为JQuery对象  
			init($(this));
		});
		function init(target){
			//这里写插件的逻辑，可以动态添加DOM节点和为节点添加CSS样式等
	        if (methods[settings.type]) {
	            return methods[settings.type](target);
	        } else if (typeof settings.type === 'object' || !settings.type) {
	            return methods['link'](target);
	        } else {
	            $.error('Method' + method + 'does not exist');
	        }
	        target=null;   
		}
	}
	
	methods = {
		link: function(target) {  
			
			
		},
		changeLink: function(target) {
			$(target+'_*:last').find()
			var levle_num = _this.data('levle');
			//移除老节点
			$("select[id^='"+default_option.nameField+"_']").each(function(e){
				//清除重选后的 老节点
				if(levle_num<$(this).data('levle')){
					$('#select2-'+default_option.nameField+'_'+$(this).data('levle')+'-container').parents('.select2-container').remove();
					$(this).remove();
				}
			})
			select_num = $("select[id^='"+default_option.nameField+"_']").size()+1; //统计联动数量
			if(select_num>default_option.maxLevle){
				return false;	
			}			
			getLinkData();
			_this=null;
		},
		destroy: function() {
			return $(this).each(function() {
				var _this = $(this);
				_this.removeData('linkSelect');
			});
		}		
	}
	
	
	function getLinkData(target){
		if(settings.url==null){
			$.error( '参数url不可为空！');
			return this;
		}
		if(settings.nameField==null){
			$.error( '表单name不可为空！');
			return this;
		} 
		getAjaxData(target);
	};
	var changeField=function (list){
		for(info in list){
			for(key_word in list[info]){
				if(default_option.valField !=null && key_word==default_option.valField){
					list[info]['id']=list[info][key_word];
				}
				if(default_option.title !=null && key_word==default_option.title){
					list[info]['title']=list[info][key_word];
				}
			}
		}
		return list;
	};
	var getSelectData=function(data,target){
		var select_option="<option value='-1'>请选择</option>",
		data_info='',
		select_id=select_num+1;
		for(key in list){
			if(settings.attr_info){
				data_info="data-info='"+list[key]+"'";
			}
			//由父级ID获取子级
			if(list[key][settings.pidField]==settings.pid){
				select_option+="<option value='"+list[key].id+"' "+data_info+">"+list[key].title+"</option>";
			}
		}
		$(target).append('<select name="'+settings.nameField+'[]" id="'+target+'_'+select_id+'" class="'+default_option.addClass+'">'+select_option+'</select>');
		//$(".select2").select2();
		select_num=0;
	};
	var getAjaxData=function(target){
		$.ajax({
			url:settings.url,
			type:(settings.type==null)?'POST':settings.type,
			data:{pid:settings.pid},
			cache:(settings.data==null)?true:settings.cache,	
			dataType: (settings.dataType==null)?'json':settings.dataType,
			success: function(data){
				if(data.code==1){
					var link_data=data['data_list'];
					if(settings.id!=null || settings.title!=null){
						var link_data=changeField(data.data_list);
					}
					getSelectData(link_data,target);
				}
			}     	
		})
	}

})(jQuery);