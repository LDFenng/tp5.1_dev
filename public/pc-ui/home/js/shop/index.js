$(function(){
	new Swiper('#top_slide', {
		paginationClickable :true,
		autoplayDisableOnInteraction:false,
		autoplay : 5000,
		speed:100,
		pagination: '.swiper-pagination',
		nextButton: '.swiper-button-next',
		prevButton: '.swiper-button-prev',
		loop: true
	}) 
	new Swiper('#slide_1', {
		paginationClickable :true,
		autoplayDisableOnInteraction:false,
		direction : 'vertical',
		autoplay : 1000,
		speed:1,
		loop: true
	}) 	
})