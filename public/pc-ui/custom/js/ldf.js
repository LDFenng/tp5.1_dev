/**
 * 通用脚本 2016-1017
 * LDF 898303969@qq.com
 */
//var modal_id ='edit_data';
$(function(){
	/**pjax核心*/
	$(document).pjax("a[data-ajax-page='true']", '#ajax_content',{
		fragment: '.page-content',
		cache: true,
		type: 'POST',
		timeout: 8000
	});
	$(document).on('pjax:send', function(data) {
		$.openload();
		//$.closeload();
	}); 
	$(document).on('pjax:complete', function(e) {
		$(".modal-backdrop").remove();
		load_plug();
	});
	
	/**分页*/
	$("body").on('click','.ajax-page',function(){
		//$(this).attr('data-ajax-page','true');
		var page = $(this).data('page'),
	    param_data = $('#ajax_page_list').serialize()+'&page='+page;
		url = $("#ajax_page_list").attr('action');
		$.phpAjax(url,param_data);
	    //$(this).attr('href',page_url);
	})
});
 
$(function(){
	/**点击菜单的变化 后期可改为以class 加ID判断*/
	$.navFunction();
});
$('body').on('click','.reload-btn',function(){
	$.pjax.reload('#ajax_content',{
		fragment: '.page-content',
		cache: false,
		type: 'POST',
		timeout: 8000
	});
})
$(document).ready(function() {
	$(document).bind("keydown",function(e){
		var e=window.event||e;
		/**禁止F5刷新*/
//		var tag = e.target.tagName.toUpperCase();
//		if(e.keyCode==116){
//			e.keyCode = 0;
//			return false;
//		}
		try{
		    if(e.keyCode == 8){  //禁止按back键后退
		    	if((tag == 'INPUT' && !$(target).attr("readonly"))||(tag == 'TEXTAREA' && !$(target).attr("readonly"))){
		    		if((target.type.toUpperCase() == "RADIO") || (target.type.toUpperCase() == "CHECKBOX")){
		    			return false ;
		    		}else{
		    			return true ;
		    		}
		    	}else{
		    		return false ;
		    	}
		   }
		  }
		catch(err){}
	});
	//监听后退事件
	if (window.history && window.history.pushState) {
		$(window).on('popstate', function() {
			if(!empty(location.href)){
				$.phpAjax(location.href);
			}
		});
	}
}); 

/**loding·····加载提示*/
var loading=null;
$.extend({ 
	openload:function($max_time=8000){
		loading=layer.load(1,{shade: [0.2, '#cccc']});
		setTimeout(function(){
			layer.close(loading)
		},$max_time)
	}
})
/**拼接面包屑*/
$.extend({
	breadcrumb:function(){
		//获取链接文本标题	
		$(".nav-list").find("li").each(function(){  //active
			if($(this).hasClass('active')){  //menu-text
				var href = $(this).children('a').attr('href'),
				is_exit=false;
				if(href!='#'){
					var link_title='',
					id=$(this).attr('id');
					$(this).children('a').find('.menu-text').text(function(index,content){
						link_title = $.trim(content);
					});
					$(".breadcrumbs").find('.btn-title').each(function(){
						 //是否已经存在
						if($(this).data('title')==link_title){
							is_exit=true;
						}
					})
					if(!is_exit){
						var bread_tpl="<div class='btn-group dropup breakcrumb-btn'>"
							+"<a href='"+href+"' data-id='"+id+"' data-title='"+link_title+"' data-ajax-page='true' class='btn btn-sm btn-info btn-white btn-title'>"+link_title+"</a>"
							+"<a class='btn btn-sm btn-info btn-white btn-icon remove-breadcrumb'>"
							+"<span class='ace-icon fa fa-times icon-only bigger-120'></span>"
							+"</a>"
							+"</div>";						
						if($(".breadcrumb").find('.breakcrumb-btn').size()>8){
							$(".breadcrumb").find('.breakcrumb-btn:first').remove();
						}else{
							$("#breadcrumbs .breadcrumb").append(bread_tpl);
						}
					}
				}
			}
		})
	}
})
$.extend({
	navFunction:function(){
		$(".nav-list").on('click','li',function(e){
			if($(this).children('a').data('ajax-page')==true){
				$('.submenu').hide();
				$(".nav-list").find('li').removeClass("active open");
				$(this).addClass('active');
				$(this).parents('.nav-list li').addClass('active open');
				$(this).each(function(){
					if($(this).parents('li ul').hasClass('submenu')){  //css('display', '');
						$(this).parents('ul').find('.submenu').hide(500).css('display', '');
					} 
				})
				$.breadcrumb();	
			}
		})		
	}
})
/**loding·····关闭加载提示*/
$.extend({ 
	closeload:function(){
		layer.close(loading);
	}
})
/**加载JS*/
$.extend({
	addScript:function(script,cache=true){
		var is_success='success';
		if(!$.isEmpty(script)){
			for(var i = 0; i < script.length; i++){
				$.ajax({
					type: 'GET', 
					url: script[i], 
					cache: cache,
					ifModified: false, 
					dataType: 'script',
					success: function(data,status){
						if(status!='success'){
							is_success=false;
						}
					} 
				})
			}
			return is_success;
		}
	}
})

/**加载CSS*/
$.extend({
	addCss:function(csses){
		if(!$.isEmpty(csses)){
			for(var i = 0; i < csses.length; i++){
				$("<link>")
				.attr({ rel: "stylesheet",
				type: "text/css",
				href: csses[i]
				}).appendTo("#ajax_content");
			}
		}
	}
})

/**实时加载插件实体化*/
function load_plug(){
	$("body").removeClass('modal-open'); //很重要！！！！！！！！！！！！
	$("body").css('padding-right','0px');
	$(".fixed-table-toolbar").find('.pull-left').addClass('col-xs-9');
	$(".columns-right").find('button').addClass('btn-white').removeClass('btn-default');
	$(".columns-right").find('button').children('span').remove();	
	$.fn.modal.Constructor.prototype.enforceFocus = function () {};
	$.breadcrumb();
	$('[data-rel="tooltip"]').tooltip();
	$('[data-toggle="popover"]').popover();
    $('textarea.limited').maxlength({
        'feedback': '.charsLeft',
        //'useInput': true
    });
    $('textarea.limited-1').maxlength({
        'feedback': '.charsLeft-1',
    });
    $('textarea.limited-2').maxlength({
        'feedback': '.charsLeft-2',
    });
    $('textarea.limited-3').maxlength({
        'feedback': '.charsLeft-3',
    });    
    $('input.limited-input').maxlength({
    	'feedback': '.charsLeft-input'
    }); 
	$('[data-toggle="table"]').bootstrapTable({
		locale:'zh-CN'
	});
	$(".select2").select2({
		language: "zh-CN"
	});
}


$.extend({ 
	/**获取根目录*/
	getRootPath_web:function(){
	  //获取当前网址，如： http://localhost:8083/uimcardprj/share/meun.jsp
	  var curWwwPath = window.document.location.href;
	  //获取主机地址之后的目录，如： uimcardprj/share/meun.jsp
	  var pathName = window.document.location.pathname;
	  var pos = curWwwPath.indexOf(pathName);
	  //获取主机地址，如： http://localhost:8083
	  var localhostPaht = curWwwPath.substring(0, pos);
	  //获取带"/"的项目名，如：/uimcardprj
	  var projectName = pathName.substring(0, pathName.substr(1).indexOf('/') + 1);
	  return (localhostPaht + projectName);
	}
}); 
$.extend({ 
    /**通用PJAX局部加载*/
	phpAjax:function(url,data={},container='#ajax_content',fragment='.page-content',is_cache=false,time_out=8000){
	    $.pjax({
	    	url: url, 
			fragment: fragment,
			container: container,
			cache: is_cache,
			data:data,
			type: 'POST',
			timeout: time_out
	    })
	}
});
$.extend({
	/**多个数组排列组合计算生成新数组*/
	doExchange:function (arr){
	    var len = arr.length;
	    // 当数组大于等于2个的时候
	    if(len >= 2){
	        // 第一个数组的长度
	        var len1 = arr[0].length;
	        // 第二个数组的长度
	        var len2 = arr[1].length;
	        // 2个数组产生的组合数
	        var lenBoth = len1 * len2;
	        //  申明一个新数组,做数据暂存
	        var items = new Array(lenBoth);
	        // 申明新数组的索引
	        var index = 0;
	        // 2层嵌套循环,将组合放到新数组中
	        for(var i=0; i<len1; i++){
	            for(var j=0; j<len2; j++){
	                items[index] = [arr[0][i] +"═╪═"+ arr[1][j]]; 
	                index++;
	            }
	        }
	        // 将新组合的数组并到原数组中
	        var newArr = new Array(len -1);
	        for(var i=2;i<arr.length;i++){
	            newArr[i-1] = arr[i];
	        }
	        newArr[0] = items;
	        // 执行回调
	        return $.doExchange(newArr);
	    }else{
	        return arr[0];
	    }
	}	
});
/**判断数组是否为空*/
$.extend({
	isEmpty:function(value){
		return (Array.isArray(value) && value.length === 0) || (Object.prototype.isPrototypeOf(value) && Object.keys(value).length === 0);
	}
})

$(function(){
	$('[data-toggle="table"]').bootstrapTable({
		locale:'zh-CN'
	});
	//重置表格按钮大小
	$(".columns-right").find('button').addClass('btn-white').removeClass('btn-default');
	$(".columns-right").find('button').children('span').remove();
})

/* 时间配置 */
$(function () { 
	//两日期联动;
	$('body').on('focus','.date-plug',function(){
		if($(this).hasClass('time')){
			var format='yyyy-mm-dd hh:ii',
			minView=0;
		}
		else{
			var format='yyyy-mm-dd',
			minView=2;
		}
		var picker_date_1 = $('.start-date').datetimepicker({
			format:format,
	        language:  'zh-CN',
	        weekStart: 1,
	        todayBtn:  1,
			autoclose: 1,
			todayHighlight: 1,
			//startView: 2,
			minView: minView,
			//forceParse: 0
	    });	
		var picker_date_2 = $('.end-date').datetimepicker({
			format:format,
	        language:  'zh-CN',
	        weekStart: 1,
	        todayBtn:  1,
			autoclose: 1,
			todayHighlight: 1,
			//startView: 2,
			minView: minView,
			//forceParse: 0
	    });	
	    //动态设置最小值  
		picker_date_1.on('changeDate', function (e) {  
			picker_date_2.datetimepicker('setStartDate',e.date);  
	    });  
	    //动态设置最大值  
		picker_date_2.on('changeDate', function (e) {  
	    	picker_date_1.datetimepicker('setEndDate',e.date); 
	        //picker1.data('DateTimePicker').maxDate(e.date);  
	    });
	})
	$('body').on('focus','.input-date-format',function(){
	    //日期
		$('.input-date-format').datetimepicker({
			format:'yyyy-mm-dd',
	        language:  'zh-CN',
	        weekStart: 1,
	        todayBtn:  1,
			autoclose: 1,
			minView: 2,
			todayHighlight: 1
	    });
	})		
	$('body').on('focus','.input-date-time',function(){
	    //时间与日期
		$('.input-date-time').datetimepicker({
			format:'yyyy-mm-dd hh:ii',
	        language:  'zh-CN',
	        weekStart: 1,
	        todayBtn:  1,
			autoclose: 1,
			todayHighlight: 1
	    });
	})	
	$('body').on('focus','.input-time-format',function(){
		//单独时间
		$(this).val(null);
	    $('.input-time-format').datetimepicker({ 
	    	format:'hh:ii',
	        language:  'zh-CN',
	        todayBtn:  1,
			autoclose: 1,
			startView:'hour',
			minView: 0,
			maxView: 1
	    })
	})
	load_plug();
	/* textarea字数提示 */
	$('body').on('input propertychange','.limited',function(){
		load_plug();
	})
	$('body').on('input propertychange','.limited-1',function(){
		load_plug();
	})
	$('body').on('input propertychange','.limited-2',function(){
		load_plug();
	})
	$('body').on('input propertychange','.limited-3',function(){
		load_plug();
	})
	
	/**下拉框搜索*/
    $('body').on('change','.ajax-change', function () {
		var param_data = $("#ajax_page_list").serialize(),
		url = $("#ajax_page_list").attr('action');
		console.log(url);
		$.phpAjax(url,param_data);
		load_plug();
		return false;
    })
	/**输入框搜索*/
	$('body').on('click','.ajax-search-form',function () {
		var param_data = $(this).parents("form").serialize(),
		url = $("#ajax_page_list").attr('action');
	    //page_url = url+'?'+param_data;
		$.phpAjax(url,param_data);
		load_plug();
		return false;
	    //$(this).attr('href',page_url);
    });
	
	/**表格全选全不选反选*/
	$("body").on('click',".check-all",function(){  //全选 或者 全不选、反选
		$(".check-data").each(function(){
			if($(this).is(':checked')){
				$(this).prop("checked", false);
			}
			else{
				$(this).prop("checked", true); 
			}
		});
	});

	/** 列表排序*/
	$('body').on('click','.btn-order',function () {
		$.openload();
        var $url=$(this).attr("href");
        if(!$url){
            $url=$(this).parents('form').attr('action');
        }   
		var data=$("input.list-order").serializeArray(),
		obj = {};
		$.each(data,function(i,v){
			obj[v.name] = v.value;
		}) 
		console.log(obj);
        $.post($url,{'data':JSON.stringify(obj)}, function (data) {
            if (data.code==1) {
                layer.alert(data.msg, {icon: 6}, function (index) {
                    layer.close(index);
                    if(!empty(data.url))$.phpAjax(data.url);
                });
            }else{
                layer.alert(data.msg, {icon: 5}, function (index) {
                    layer.close(index);
                });
            }
        }, "json");
        $.closeload();
        load_plug();
        return false;
    });


	/**改变状态*/
	$('body').on('click','.status-btn',function () {
		$.openload();
        var _this=$(this),
        $url = this.href,
        val = $(this).data('id');
        $.post($url, {x: val}, function (data) {
            if (data.code==1) {
            	_this.empty().text(data.tip);
            	_this.attr('title',data.tip);
                if (data.msg == 1) {
                	_this.removeClass(data.css_1).addClass(data.css_2);

                } else {
                	_this.addClass(data.css_1).removeClass(data.css_2);
                }
                $.closeload();
                if(!empty(data.url))$.phpAjax(data.url);
            } else {
                layer.alert(data.msg, {icon: 5});
                if(!empty(data.url))window.location.href=data.url;
                $.closeload();
            }
        }, "json");
        return false;
    });	
	/** 完全隐藏时modal*/
	$('body').on('hidden.bs.modal','.modal', function (data) {
		$(".select2").select2({
			dropdownParent: $("#ajax_content"),
			language: "zh-CN"
		});
		$("#edit_data").removeData("bs.modal");
	});
	/** 调用时modal*/
	$('body').on('hide.bs.modal','.modal', function (data) {
		$(".modal-backdrop").remove();
	});
	/** 调用时modal*/
	$('body').on('show.bs.modal','.modal', function (data) {
		$(".select2").select2({
			dropdownParent: $("#edit_data"),
			language: "zh-CN"
		});
		//if(data.currentTarget.id != 'upload_file')modal_id = data.currentTarget.id;
	});
	/** 完全打开modal前事件*/
	$('body').on('shown.bs.modal','.modal', function (data) {
		//modal_id = data.currentTarget.id;
		$(".select2").select2({
			dropdownParent: $("#edit_data"),
			language: "zh-CN"
		});
		var result_data = data.currentTarget.innerText,
		reg_str = /<html/i.test(result_data); //result_data.indexOf("<");
		if(!reg_str){
		    try {
		    	var result_data = JSON.parse(result_data);
		    } catch (e) {
		        return false;
		    }
			if(result_data.code==0){
				data.preventDefault(); 
        		$('#edit_data').modal('hide');
        		$('#file_modal').modal('hide');
                layer.alert(result_data.msg, {icon: 5}, function (index) {
                    layer.close(index);
                    //$.phpAjax('#');
                    if(!empty(result_data.url)){
                    	var result = result_data.url.match("login/login");
                        if(result=='null'){
                        	$.phpAjax(result_data.url);
                        }else{
                          window.location.href=result_data.url;
                        }
                    }
                });
			}
		}
	});
	
	/**图片裁剪上传*/
	$("body").on('click','.crop-img',function(){
		var img_src = $(this).attr('src'),
		url=$("#crop_img_url").val(),
		input_id = $(this).data('input-id'),
		src_id = $(this).data('src-id');
	    $("#file_modal").modal({  
	        remote: url+"?input_id="+ input_id+'&src_id='+src_id+'&img_src='+img_src 
	    });  
	})
	
	/**文件上传*/
	$("body").on('click','.upload-file',function(){
		var input_id = $(this).data('input-id'),
		url=$("#upload_file_url").val(),
		file_type = $(this).data('file-type'),
		file_info = $(this).data('file-info');
		if(!empty(file_info))var info="&d="+file_info;
	    $("#file_modal").modal({  
	        remote: url+"?a="+ input_id+"&b="+file_type+info
	    });  
	})
	
	/**打开素材*/
	$("body").on('click','.select-file',function(){
		var z = $(this).data('file-z'),
		url=$("#select_file_url").val(),
		input_id = $(this).data('input-id');
	    $("#file_modal").modal({  
	        remote: url+"?z="+z+"&a="+input_id
	    }); 
	})
	
}); 

(function ($) {
	$('body').on('change','.submit-change',function () {		
        var $form = $(this).parents("form");	
        $form.submit();
    });
})(jQuery);


/*删除数据*/
$("body").on('click','.del-data',function(){
	var id_array = [],
	msg=empty($(this).data('info'))?'确定删除？':msg;
	var del_url = $(".check-all").val();
	$(".check-data").each(function(i){
		if($(this).is(':checked')){
			id_array.push($(this).val());
		}
    }); 
	if($.isEmpty(id_array)){
		layer.msg('选择要删除的数据'); 
		return false;
	}
	layer.confirm(msg,{icon: 3}, function(){
		$.openload();
    	$.ajax({
    		type:"POST",
    		url:del_url,
    		data:{'ids':id_array},   
    		dataType:'json',
    		success: function(data){
    			if(data.code==1){
        			$(".check-data").each(function(i){
        				if($(this).is(':checked')){
        					$(this).parents('tr').remove();
        				}
        			})
        			layer.msg('删除成功！'); 
        			$.closeload();
        			if(!empty(data.url))$.phpAjax(data.url);
    			}
    			else{
    				layer.msg(data.msg); 
    				$.closeload();
    			}
    		}
    	});
	});
})

/*************************************************************************** 所有ajaxForm提交 ********************************************************/
/** modal表单操作 */
$("body").on('click','.ajax-form',function(){	
    $('.ajax-form').ajaxForm({
        success: complete, // 这是提交后的方法
        type:'POST',
        dataType: 'json'
    });    
})
/**modal提交表单*/
function complete(data) {
	$.openload();
    if (data.code == 1) {
        layer.alert(data.msg, {icon: 6}, function (index) {
            layer.close(index);
            $('#edit_data').modal('hide');
            if(!empty(data.url))$.phpAjax(data.url);  
            $.closeload();
        });
    } else {
        layer.alert(data.msg, {icon: 5}, function (index) {
            layer.close(index);
            $.closeload();
            if(!empty(data.url))$.phpAjax(data.url);  
            //$('.modal').modal('hide');
        });
        return false;
    }
}

/**普通编辑页面提交表单*/
$("body").on('click','.ajax-form-1',function(){	
    $('.ajax-form-1').ajaxForm({
        success: complete_1, // 这是提交后的方法
        type:'POST',
        dataType: 'json'
    });    
})
/**普通编辑页面提交表*/
function complete_1(data) {
	$.openload();
    if (data.code == 1) {
        if(data.modal==true){
            $('#edit_data').modal({remote:data.url});
            $('#edit_data').modal('show');
            $.closeload();
       }
       else{
           layer.alert(data.msg, {icon: 6}, function (index) {
              	$('#edit_data').modal('hide');
              	if(!empty(data.url))$.phpAjax(data.url,null);	
              	layer.close(index);
              	$.closeload();
           });       
       }
    } else {
        layer.alert(data.msg, {icon: 5}, function (index) {
            layer.close(index);
            $.closeload();
            //$('.modal').modal('hide');
        });
        return false;
    }
}

/**普通编辑页面提交表单*/
$("body").on('click','.ajax-form-2',function(){	
    $('.ajax-form-2').ajaxForm({
        success: complete_2, // 这是提交后的方法
        type:'POST',
        dataType: 'json'
    });    
})
/**普通编辑页面提交表（全局刷新）*/
function complete_2(data) {
	$.closeload();
    if (data.code == 1) {
       layer.alert(data.msg, {icon: 6}, function (index) {
          	layer.close(index);
          	setTimeout(function(){
          		if(!empty(data.url)){
          			window.location.href=data.url;
          		}
          		else{
          			$('#edit_data').modal('hide');
          		}
          	},200)		
       });             
    } else {
        layer.alert(data.msg, {icon: 5}, function (index) {
            layer.close(index);
            //$('.modal').modal('hide');
        });
        return false;
    }
}

/************************************************************* 所有带确认的ajax提交btn ********************************************************/
/* get执行并返回结果 */
$(function () {
	$('body').on('click','.get-btn',function (e) {
		e.preventDefault();
		$.openload();
        var $url = this.href;
        $.get($url, function (data) {
            if (data.code == 1) {
            	layer.alert(data.msg, {icon: 6});
                if(!empty(data.url))$.phpAjax(data.url);
                $.closeload(); 
            } else {
            	layer.alert(data.msg, {icon: 5});
            	$.closeload();
                if(!empty(data.url))$.phpAjax(data.url);
            }
        }, "json");
        return false;
    });
});

/**表格导出（下载）*/
$(function(){
	$("body").on('click','.exprot-execl',function(e){
		e.preventDefault();
		var url = this.href;
		var data = $("#ajax_page_list").serialize(),
		jump_url= url+'?'+data;
		//alert();
		window.location.href= jump_url;
	})
})

/* get执行并返回结果，(带有确认按钮) */
$(function () {
	$('body').on('click','.confirm-btn',function () {
		$.openload();
        var $url = this.href,
            $info = $(this).data('info');
        layer.confirm($info, {icon: 3}, function (index) {
            layer.close(index);
            $.get($url, function (data) {
                if (data.code==1) {
                    layer.alert(data.msg, {icon: 6}, function (index) {
                        layer.close(index);
                        $('#edit_data').modal('hide');
                        $('#file_model').modal('hide');
                        if(!empty(data.url))$.phpAjax(data.url);
                    });
                } else {
                    layer.alert(data.msg, {icon: 5}, function (index) {
                        layer.close(index);
                        if(!empty(data.url))$.phpAjax(data.url);
                    });
                }
                $.closeload();
            }, "json");
        });
        $.closeload();
        return false;
    });
});

/**全局通用ajax事件*/
$(document).ajaxStart(onStart)
   .ajaxComplete(onComplete)
   .ajaxSuccess(onSuccess);  
    function onStart(event) {
    	$.openload();
    }
    function onComplete(event, xhr, settings) {
    	load_plug();
    	$.closeload();
    }
    function onSuccess(event, xhr, settings) {
    	load_plug();
    	$.closeload();
    }
 
/*************************************************************************** 数据备份还原********************************************************/
/* 数据库备份、优化、修复 */
(function ($) {
	$("body").on('click','a[id^=optimize_]',function(){
        $.get(this.href, function (data) {
            if (data.code==1) {
                layer.alert(data.msg, {icon: 6});
            } else {
                layer.alert(data.msg, {icon: 5});
            }
        });
        return false;
	})
	$("body").on('click','a[id^=repair_]',function(){
        $.get(this.href, function (data) {
            if (data.code==1) {
                layer.alert(data.msg, {icon: 6});
            } else {
                layer.alert(data.msg, {icon: 5});
            }
        });
        return false;
	})

    var $form = $("#export-form"), $export = $("#export"), tables;
	$("body").on('click','#optimize',function(){
		$(this).addClass('disabled').prop('disabled', true);	
        $.post(this.href, $form.serialize(), function (data) {
            if (data.code==1) {
                layer.alert(data.msg, {icon: 6}, function (index) {
                    layer.close(index);
                });
            } else {
                layer.alert(data.msg, {icon: 5}, function (index) {
                    layer.close(index);
                });
            }
            $.openload();
            setTimeout(function () {
                $('#top-alert').find('button').click();
                $(this).removeClass('disabled').prop('disabled', false);
                $.closeload();
            }, 3000);
        }, "json");       
        return false;
	})
	$("body").on('click','#repair',function(){
        $.post(this.href, $form.serialize(), function (data) {
            if (data.code==1) {
                layer.alert(data.msg, {icon: 6}, function (index) {
                    layer.close(index);
                });
            } else {
                layer.alert(data.msg, {icon: 5}, function (index) {
                    layer.close(index);
                });
            }
            $.openload();
            setTimeout(function () {
                $('#top-alert').find('button').click();
                $(this).removeClass('disabled').prop('disabled', false);
                $.closeload();
            }, 1500);
        }, "json");
        return false;
	})
	$("body").on('click','#export',function(){
		//alert(11);
        $export.children().addClass("disabled");
        $export.children().text("正在发送备份请求...");
        $.post(
            $form.attr("action"),
            $form.serialize(),
            function (data) {
                if (data.code==1) {
                    tables = data.tables;
                    $export.children().text(data.msg + "开始备份，请不要关闭本页面！");
                    backup(data.tab);
                    window.onbeforeunload = function () {
                        return "正在备份数据库，请不要关闭！"
                    }
                } else {
                    layer.alert(data.msg, {icon: 5});
                    $export.children().removeClass("disabled");
                    $export.children().text("立即备份");
                    setTimeout(function () {
                        $('#top-alert').find('button').click();
                        $(that).removeClass('disabled').prop('disabled', false);
                    }, 1500);
                }
            },
            "json"
        );
        return false;
	})

    function backup(tab, status) {
        status && showmsg(tab.id, "开始备份...(0%)");
        $.get($form.attr("action"), tab, function (data) {
            if (data.code==1) {
                showmsg(tab.id, data.msg);
                if (!$.isPlainObject(data.tab)) {
                    $export.children().removeClass("disabled");
                    $export.children().text("备份完成，点击重新备份");
                    window.onbeforeunload = null;
                }
                else{
                	backup(data.tab, tab.id != data.tab.id);
                }   
            } else {
                //updateAlert(data.msg, 'alert-error');
                $export.children().removeClass("disabled");
                $export.children().text("立即备份");
                setTimeout(function () {
                    $('#top-alert').find('button').click();
                    $(that).removeClass('disabled').prop('disabled', false);
                }, 1500);
            }
        }, "json");
    }

    function showmsg(id, msg) {
        $tr=$form.find("input[value=" + tables[id] + "]").closest("tr");
        $tr.find(".green").html("");
        $tr.find(".info").html("");
        $tr.find(".backup").html(msg);
    }
})(jQuery);

(function($){
    $.fn.serializeJson=function(){
        var serializeObj={};
        var array=this.serializeArray();
        var str=this.serialize();
        $(array).each(function(){
            if(serializeObj[this.name]){
                if($.isArray(serializeObj[this.name])){
                    serializeObj[this.name].push(this.value);
                }else{
                    serializeObj[this.name]=[serializeObj[this.name],this.value];
                }
            }else{
                serializeObj[this.name]=this.value;
            }
        });
        return serializeObj;
    };
})(jQuery);
     
    
    

