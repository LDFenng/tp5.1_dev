/**
 * 通用脚本 2016-1017
 * LDF 898303969@qq.com
 */
//var modal_id ='edit_data';
//$(function(){
//	/**pjax核心*/
//	$(document).pjax("a[data-ajax-page='true']", '#ajax_content',{
//		fragment: '.page-content',
//		cache: true,
//		type: 'POST',
//		timeout: 8000
//	});
//	$(document).on('pjax:send', function(data) {
//		$.openload();
//		//$.closeload();
//	}); 
//	$(document).on('pjax:complete', function(e) {
//		$(".modal-backdrop").remove();
//		load_plug();
//	});	
//});
 
$(function(){
	/**点击菜单的变化 后期可改为以class 加ID判断*/
	$.navFunction();
});
//$('.reload-btn').click(function(){
//	$.pjax.reload('#ajax_content',{
//		fragment: '.page-content',
//		cache: false,
//		type: 'POST',
//		timeout: 8000
//	});
//})
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
				//$.phpAjax(location.href);
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
		$(".nav-list").delegate('li','click',function(e){
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
/**判断数组是否为空*/
$.extend({
	isEmpty:function(value){
		return (Array.isArray(value) && value.length === 0) || (Object.prototype.isPrototypeOf(value) && Object.keys(value).length === 0);
	}
})
/* 时间配置 */
$(function () { 
	/**下拉框搜索*/
    $('body').delegate('.ajax-change', 'change', function () {
		var param_data = $(this).parents("form").serialize(),
		url = $("#ajax_page_list").attr('action');
	    //page_url = url;
	
		return false;
    })
	/**输入框搜索*/
	$('body').delegate('.ajax-search-form','click',function () {
		var param_data = $(this).parents("form").serialize(),
		url = $("#ajax_page_list").attr('action');
	    //page_url = url+'?'+param_data;

		load_plug();
		return false;
	    //$(this).attr('href',page_url);
    });
	
	/**表格全选全不选反选*/
	$("body").delegate(".check-all",'click',function(){  //全选 或者 全不选、反选
		$(".check-data").each(function(){
			if($(this).is(':checked')){
				$(this).prop("checked", false);
			}
			else{
				$(this).prop("checked", true); 
			}
		});
	});

	/** 完全隐藏时modal*/
	$('body').delegate('.modal','hidden.bs.modal', function (data) {

		$("#edit_data").removeData("bs.modal");
	});
	/** 调用时modal*/
	$('body').delegate('.modal','hide.bs.modal', function (data) {
		$(".modal-backdrop").remove();
	});
	/** 调用时modal*/
	$('body').delegate('.modal','show.bs.modal', function (data) {
	});
	/** 完全打开modal前事件*/
	$('body').delegate('.modal','shown.bs.modal', function (data) {
		//modal_id = data.currentTarget.id;
	});
	
	/**图片裁剪上传*/
	$("body").delegate('.crop-img','click',function(){
		var img_src = $(this).attr('src'),
		url=$("#crop_img_url").val(),
		input_id = $(this).data('input-id'),
		src_id = $(this).data('src-id');
	    $("#file_modal").modal({  
	        remote: url+"?input_id="+ input_id+'&src_id='+src_id+'&img_src='+img_src 
	    });  
	})
	
	/**文件上传*/
	$("body").delegate('.upload-file','click',function(){
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
	$("body").delegate('.select-file','click',function(){
		var z = $(this).data('file-z'),
		url=$("#select_file_url").val(),
		input_id = $(this).data('input-id');
	    $("#file_modal").modal({  
	        remote: url+"?z="+z+"&a="+input_id
	    }); 
	})
	
}); 

(function ($) {
	$('body').delegate('.submit-change','change',function () {		
        var $form = $(this).parents("form");	
        $form.submit();
    });
})(jQuery);


/*删除数据*/
$("body").delegate('.del-data','click',function(){
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
        				
        				}
        			})
        			layer.msg('删除成功！'); 
        			
    			}
    			else{
    				layer.msg(data.msg); 
    			}
    		}
    	});
		$.closeload();
	});
})

/*************************************************************************** 所有ajaxForm提交 ********************************************************/
/** modal表单操作 */
$("body").delegate('.ajax-form','click',function(){	
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
            
            $.closeload();
        });
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
$("body").delegate('.ajax-form-1','click',function(){	
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
$("body").delegate('.ajax-form-2','click',function(){	
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
	$('body').delegate('.get-btn','click',function (e) {
		e.preventDefault();
		$.openload();
        var $url = this.href;
        $.get($url, function (data) {
            if (data.code == 1) {
            	layer.alert(data.msg, {icon: 6});

                $.closeload(); 
            } else {
            	layer.alert(data.msg, {icon: 5});
            	$.closeload();

            }
        }, "json");
        return false;
    });
});

/* get执行并返回结果，(带有确认按钮) */
$(function () {
	$('body').delegate('.confirm-btn','click',function () {
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
                        
                    });
                } else {
                    layer.alert(data.msg, {icon: 5}, function (index) {
                        layer.close(index);
             
                    });
                }
                $.closeload();
            }, "json");
        });
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
    	$.closeload();
    }
    function onSuccess(event, xhr, settings) {

    	$.closeload();
    }
 
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
     
    
   