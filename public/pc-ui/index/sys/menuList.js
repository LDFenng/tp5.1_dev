/**
 * 
 */
$(function () {
    /*权限管理*/
	$('body').on('click','.menu-list',function () {
		var $a=$(this),$tr=$a.parents('tr');
		var $pid=$tr.attr('id');
		//alert($pid);return;
		if($a.find('span').hasClass('fa-minus')){
			$("tr[id^='"+$pid+"_']").attr('style','display:none');
			$a.find('span').removeClass('fa-minus').addClass('fa-plus');
		}else{
			if($("tr[id^='"+$pid+"_']").length>0){
				$("tr[id^='"+$pid+"_']").attr('style','');
				$a.find('span').removeClass('fa-plus').addClass('fa-minus');
			}else{
				var $url =$a.attr('href'),$id=$a.data('id'),$levle=$a.data('levle');
				$.post($url,{pid:$id,level:$levle,id:$pid}, function (data) {
					if (data) {
						$a.find('span').removeClass('fa-plus').addClass('fa-minus');
						$tr.after(data);
					}else{
						$a.find('span').removeClass('fa-plus').addClass('fa-minus');
					}
				}, "json");
			}
		}
        return false;
    });
});

$(function () {
	openload();
    $('#save_data').ajaxForm({
        success: save_function, // 这是提交后的方法
        dataType: 'json'
    });
    colseload();
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