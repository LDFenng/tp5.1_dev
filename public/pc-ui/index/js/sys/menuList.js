/**
 * 
 */
$(function () {
    /*权限管理*/
	$("#ajax_content").unbind().delegate('.menu-list','click',function(event){
		event.preventDefault();
		$.openload();
		var $a=$(this),$tr=$a.parents('tr');
		var $pid=$tr.attr('id');
		if($a.find('span').hasClass('fa-minus')){
			$("tr[id^='"+$pid+"_']").hide();
			$a.find('span').removeClass('fa-minus').addClass('fa-plus');
			$.closeload();
		}else{
			if($("tr[id^='"+$pid+"_']").length>0){
				$("tr[id^='"+$pid+"_']").show();
				$a.find('span').removeClass('fa-plus').addClass('fa-minus');
				$.closeload();
			}else{
				var $url =$a.attr('href'),$id=$a.data('id'),$levle=$a.data('levle');
				$.post($url,{'pid':$id,'level':$levle,'str_id':$pid}, function (data) {
					if (data) {
						$a.find('span').removeClass('fa-plus').addClass('fa-minus');
						$("#"+$pid).after(data);
						$.closeload();
					}else{
						$a.find('span').removeClass('fa-plus').addClass('fa-minus');
						$.closeload();
					}
				}, "html");
			}
		}
        return false;
	})
});

$(function () {
    $('#save_data').ajaxForm({
        success: save_function, // 这是提交后的方法
        dataType: 'json'
    });
});
//成功失败均不跳转
function save_function(data) {
    if (data.code == 1) {
        layer.alert(data.msg, {icon: 6}, function (index) {
            layer.close(index);
        });
        $('.modal').modal('hide');
    } else {
        layer.alert(data.msg, {icon: 5}, function (index) {
            layer.close(index);
            $('.modal').modal('hide');
        });
        return false;
    }
}