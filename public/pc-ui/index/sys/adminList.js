/**
 * 
 */
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
        window.location.href = data.url;
    } else {
        layer.alert(data.msg, {icon: 5}, function (index) {
            layer.close(index);
        });
        return false;
    }
}
$(function($) {
	 $('.modal-body').delegate('.change-box','click', function(e) {
		 if($("#forgot-box").is(":hidden")){
			 $("#signup-box").hide(1000);
			 $("#forgot-box").show(1000);
		 }
		 else{
			 $("#forgot-box").hide(1000);
			 $("#signup-box").show(1000); 
		 }
	 });
});

$('.input-date').datetimepicker({  
	format:'yyyy-mm-dd',
    language:  'zh-CN',
    weekStart: 1,
    todayBtn:  1,
	autoclose: 1,
	todayHighlight: 1,
	//startView: 2,
	minView: 2,
	//forceParse: 0
});