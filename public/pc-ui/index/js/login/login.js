/**
 * 
 */
jQuery(function($) {
 $(document).on('click', '.toolbar a[data-target]', function(e) {
	e.preventDefault();
	var target = $(this).data('target');
	$('.widget-box.visible').removeClass('visible');//hide others
	$(target).addClass('visible');//show target
 });
});
//you don't need this, just used for changing background
jQuery(function($) {
 $('#btn-login-dark').on('click', function(e) {
	$('body').attr('class', 'login-layout');
	$('#id-text2').attr('class', 'white');
	$('#id-company-text').attr('class', 'blue');
	e.preventDefault();
 });
 $('#btn-login-light').on('click', function(e) {
	$('body').attr('class', 'login-layout light-login');
	$('#id-text2').attr('class', 'grey');
	$('#id-company-text').attr('class', 'blue');
	e.preventDefault();
 });
 $('#btn-login-blur').on('click', function(e) {
	$('body').attr('class', 'login-layout blur-login');
	$('#id-text2').attr('class', 'white');
	$('#id-company-text').attr('class', 'light-blue');
	e.preventDefault();
 });
});

$(function () {
    $('.ajaxForm4').ajaxForm({
        success: complete2, // 这是提交后的方法
        dataType: 'json'
    });
});
function complete2(data) {
	var loading=layer.load(0,{shade: [0.2, '#cccc']});
    if (data.code == 1) {
        layer.alert(data.msg, {icon: 6}, function (index) {
            layer.close(index);
			$('.widget-box.visible').removeClass('visible');//hide others
			$("#login-box").addClass('visible');//show target
        });
        layer.close(loading);
    } else {
        layer.alert(data.msg, {icon: 5}, function (index) {
            layer.close(index);
            $("#verify").val('');
            $("#verify_img").click();
        });
        layer.close(loading);
    }
}

$(function () {
    $('.ajaxForm3').ajaxForm({
        success: complete3, // 这是提交后的方法
        dataType: 'json'
    });
});
//失败不跳转,验证码刷新
function complete3(data) {
    if (data.code == 1) {
        window.location.href = data.url;//'index.php#Index/index';
    } else {	 
//        $("#verify").val('');
        $("#verify_img").click();
    	//alert(data.msg);
        layer.alert(data.msg, {icon: 5});
        //window.location.reload()
    }
}
