$(function(){
	var mySwiper = new Swiper('.swiper-container', {
	paginationClickable :true,
	autoplay : 10000,
	speed:1,
	pagination: '.swiper-pagination',
	nextButton: '.swiper-button-next',
	prevButton: '.swiper-button-prev',
	loop: true,
	onInit: function(swiper){ //Swiper2.x的初始化是onFirstInit
		swiperAnimateCache(swiper); //隐藏动画元素 
		swiperAnimate(swiper); //初始化完成开始动画
	}, 
		onSlideChangeEnd: function(swiper){ 
		swiperAnimate(swiper); //每个slide切换结束时也运行当前slide动画
	}, 
		onSlideChangeEnd: function(swiper){ 
		swiperAnimate(swiper); //每个slide切换结束时也运行当前slide动画
		} 
	}) 
	$('.scrollable').each(function () {
		var $this = $(this);
		$(this).ace_scroll({
			size: $this.attr('data-size') || 100,
			//styleClass: 'scroll-left scroll-margin scroll-thin scroll-dark scroll-light no-track scroll-visible'
		})
	})
})