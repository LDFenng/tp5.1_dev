var select_levle=0,
select_num=0;
(function($) {
	$.fn.extend({  
		linkSelect:function(options){  
		     //插件参数的可控制性，外界可以修改默认参数
			var defaults = $.extend({}, default_val, options);
		   //var defaults=$.extend($.fn.linkSelect.defaults, options );
		   //遍历函数 
			var select_num = ($(options.contant).find('.link-select').size())+1, //统计联动数量
			select_option="<option value='-1'>请选择</option>";
			if(select_num>defaults.maxLevle)return false;
			$.ajax({
				url:defaults.url,
				type:(defaults.type==null)?'POST':defaults.type,
				data:(defaults.data==null)?{}:defaults.data,
				cache:(defaults.data==null)?true:defaults.cache,	
				dataType: (defaults.dataType==null)?'json':defaults.dataType,
				success: function(data){
					if(data.code==1){
						var list=data.data_list,
						data_info='';
						if(defaults.id!=null || defaults.title!=null){
							list=changeField(list,defaults);
						}
						for(key in list){
							if(defaults.attr_info){
								data_info="data-info='"+list[key]+"'";
							}
							select_option+="<option value='"+list[key].id+"' "+data_info+">"+list[key].title+"</option>";
						}
						$('<select name="'+defaults.nameField+'" id="select_id_'+select_num+'" data-levle="'+select_num+'" class="link-select '+defaults.addClass+'">'+select_option+'</select>').appendTo(defaults.contant);
						//$("#select_id_"+select_num).select2();
						select_levle++;
					}
					else select_num=0;
				}            	
			})
		},
		changeLinkSelect:function(options){
			
		}
	})
	function changeField(list,defaults){
		for(info in list){
			for(key_word in list[info]){
				if(defaults.id !=null && key_word==defaults.id){
					list[info]['id']=list[info][key_word];
				}
				if(defaults.title !=null && key_word==defaults.title){
					list[info]['title']=list[info][key_word];
				}
			}
		}
		return list;
	}
	//参数默认值
	var default_val={
		    	contant:'',	
		        url: null,
		        type: 'POST',
		        data:null,
		        cache:false,
		        dataType:'json',
		        id:'id',
		        title:'title',
		        addClass:'',
		        attrInfo:false,
		        nameField:'select_id[]',
		        maxLevle:20
	        };
})(jQuery);
