/**
 * 
 */
$(".modal-footer").delegate('#submit_data','click',function(){
	var ids = [];
	$(".off-on").each(function(){
		if($(this).hasClass('fa-toggle-on')){
			ids.push($(this).data('id'));
		}
	});
	$("#id_str").val(ids);
})

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
$(function($){	
	$('.dd').nestable();
	//$('.dd').nestable('collapseAll'); 
	$('.dd-handle a').on('mousedown', function(e){
		e.stopPropagation();
	});	
	$('[data-rel="tooltip"]').tooltip();
	$(".dd-handle").delegate('.off-on','click',function(){
		var _this = $(this);
		var on_off = true;
		var auth_id = _this.data('id');
		if(_this.hasClass('fa-toggle-on')){
			on_off = false;
			_this.removeClass('fa-toggle-on red').addClass('fa-toggle-off bule');
			$("#tooltip_"+auth_id).attr('data-original-title','关闭');
		}
		else{
			on_off = true;
			_this.removeClass('fa-toggle-off bule').addClass('fa-toggle-on red');
			$("#tooltip_"+auth_id).attr('data-original-title','开启');
		}
		if(_this.hasClass('check-'+auth_id) && !_this.hasClass('check-two-'+auth_id)
				&& !_this.hasClass('check-three-'+auth_id) && !_this.hasClass('check-four-'+auth_id)){
				(on_off) ? on_class(auth_id) :off_class(auth_id);
		}
		else if(_this.hasClass('check-two-'+auth_id)
			&& !_this.hasClass('check-three-'+auth_id) && !_this.hasClass('check-four-'+auth_id)){
			(on_off) ? on_class('two-'+auth_id) :off_class('two-'+auth_id);
		}
		else if(_this.hasClass('check-three-'+auth_id) && !_this.hasClass('check-four-'+auth_id)){
			(on_off) ? on_class('three-'+auth_id) :off_class('three-'+auth_id);
		}
		else if(_this.hasClass('check-four-'+auth_id)){
			(on_off) ? on_class('four-'+auth_id) :off_class('four-'+auth_id);
		}
	})	
	function off_class(auth_id){
		$('.check-'+auth_id).removeClass('fa-toggle-on red').addClass('fa-toggle-off bule');
		$('.check-'+auth_id).attr('data-original-title','关闭');
	}
	function on_class(auth_id){
		$('.check-'+auth_id).removeClass('fa-toggle-off bule').addClass('fa-toggle-on red');
		$('.check-'+auth_id).attr('data-original-title','开启');
	}
	
	$(".is-check").click(function(){
		$(".off-on").each(function(){
			if($(this).hasClass('fa-toggle-on')){
				$(this).removeClass('fa-toggle-on red').addClass('fa-toggle-off bule');
				$(this).attr('data-original-title','关闭');
			}
			else{
				$(this).removeClass('fa-toggle-off bule').addClass('fa-toggle-on red');
				$(this).attr('data-original-title','开启');
			}
		})
	})
});