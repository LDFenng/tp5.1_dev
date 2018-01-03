$(function(){
	new Swiper('#top_slide', {
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
	new Swiper('#advert_slide', {
		direction: 'vertical',
		autoplay : 3000,
		speed:1,
		loop: true,
        paginationClickable: true
	}) 	
    new Swiper('#bottom_slide', {
        slidesPerView: 5,
        paginationClickable: true,
        spaceBetween: 10,
        loop: true,
        autoplay : 3000,
        freeMode: true
    });	
})