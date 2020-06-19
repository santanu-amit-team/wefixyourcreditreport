/**
 * Global class for cross-site Javascript functionality
 */
var CreditRepairSite = {

	initialize: function(){
		this.mobileNavigation();
		this.backToTopButton();
		this.expandFooterNavMobile();

		$('.ie_upgrade_close').click(function(){
			$('#ie_upgrade_banner').hide();
		}).hover(function(){
			$(this).css('cursor', 'pointer');
		});

		if($('#vendor-banner').length > 0){
			$('#vendor-banner .close_btn').on('click', function(){
				$('#vendor-banner').slideUp();
			});
		}
	},

	/**
	 * Determines whether the user is on a mobile platform or not.
	 */
	isMobile: function(){
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
			return true;
		}
		return false;
	},

	/**
	 * determines what screen width we have (returns results based on skeleton responsive media queries)
	 */
	screenWidth: function(){
		if($('.container').css('width') == '960px'){
			return 'desktop';
		}
		if($('.container').css('width') == '768px'){
			return 'tablet';
		}
		if($('.container').css('width') == '420px'){
			return 'mobile-landscape';
		}
		if($('.container').css('width') == '300px'){
			return 'mobile-potrait';
		}
		return false;
	},

	/**
	 * Navigation functionality for mobile versions of the site
	 */
	mobileNavigation: function(){
		if ($('#mobile_nav_toggle').length != 0) {
			$('#mobile_nav_toggle').on('click.mobileNavigation', function() {
				$('#mobile_nav_toggle, #navigation').toggleClass('active');
			});
		}
	},

	scrollTo: function(jelement, offset, speed){

		// logic for offset not done yet

		// set default speed
		if(!speed){
			speed = 2000;
		}

		$('html, body').animate({
			scrollTop: jelement.offset().top
		}, speed);
	},

	backToTopButton: function(){

		$('body,html').bind('scroll mousedown wheel DOMMouseScroll mousewheel keyup', function(e){
			if ( e.which > 0 || e.type == "mousedown" || e.type == "mousewheel"){
				$("html,body").stop();
			}
		});

		$(window).scroll(function(){
			if($(window).scrollTop() >= 200){
				$('#back_to_top_button').fadeIn('fast');
			}else{
				$('#back_to_top_button').fadeOut('fast');
			}
		});

		$('#back_to_top_button').click(function(){
			$('html, body').animate({
				scrollTop: 0
			}, 1000);
		}).hover(function(){
			$(this).css('cursor', 'pointer');
		});
	},

	expandFooterNavMobile: function(){

		$('.bottom_nav_section .bottom_nav_section_title').on('click.expandFooterNavMobile', function(event){
			if ( $(window).width() < 768 )
			{
				event.preventDefault();
				$(this).parent().toggleClass('expanded');
			}
		});
	}

};

/**
 * Nifty cookie management methods
 * @type {Class}
 */
var CookieManager = {

	/**
	 * Creates a cookie
	 * @param  {string} name  "The name of the cookie"
	 * @param  {string} value "The value of the cookie"
	 * @param  {int} 	days  "The number of days until the cookie expires. If not given then create a session cookie"
	 * @return {NULL}
	 */
	create: function(name, value, days){
		if(days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}else{
			var expires = "";
		}

		document.cookie = name+"="+value+expires+"; path=/";
	},

	/**
	 * Deletes a cookie
	 * @param  {string} name "The name of the cookie"
	 * @return {NULL}
	 */
	erase: function(name){
		createCookie(name,"",-1);
	},

	/**
	 * Reads a cookie
	 * @param  {string} name "The name of the cookie"
	 * @return {NULL}
	 */
	read: function(name){
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}
}

LightBoxVideo = {
	playerObject: null,
	forceLightbox: false,

	init: function(){

		$('.lightBoxVideo').on('click.lightBoxVideo', function(e){
			e.preventDefault();
			LightBoxVideo.open($(this));
			if(CreditRepairSite.isMobile()){
				LightBoxVideo.playerObject.fullscreen();
				$("#lightBoxPlayer video").bind('webkitendfullscreen', function(){
					LightBoxVideo.close();
				});
			}
		});

		$('.lightBoxOverlay, .lightBoxOverlay .close').on('click.lightBoxVideo', function(e){
			if($(e.target).is('.close, .lightBoxOverlay')){
				LightBoxVideo.close();
			}
		});

		LightBoxVideo.load();

	},

	load: function(){
		// dummy playlist videos are required to get it to load. They are overwritten on open/load
		$("#lightBoxPlayer").flowplayer({
			playlist: [
				[
					{webm: 'https://www.creditrepair.com/cdn_videos/testimonials/michael_dream/640x360.webm'},
					{mp4: 'https://www.creditrepair.com/cdn_videos/testimonials/michael_dream/640x360.mp4'}
				]
			],
			splash: true,
			//native_fullscreen: true
		});

		LightBoxVideo.playerObject = $("#lightBoxPlayer").data('flowplayer');

		LightBoxVideo.playerObject.bind('fullscreen-exit', function(e, api){
			LightBoxVideo.close();
		});

		LightBoxVideo.playerObject.bind('error', function(e, api, error){
			// if we get an error the following code closes the lightbox, removes all associated elememnts from the DOM, adds them back then the "load" method re-initiates the player
			LightBoxVideo.close();
			$("#lightBoxPlayer").remove(); // remove element from DOM
			$('.lightBoxWrapper').append('<div id="lightBoxPlayer" class="play-button"></div>'); // add element back
			LightBoxVideo.load();
		});
	},

	open: function(el){

		var url = el.attr('href').slice(0, -4);

		LightBoxVideo.playerObject.load([
			{webm: url + '.webm'},
			{mp4: url + '.mp4'}
		]);

		// add classes
		$("body").addClass("is-overlayed");
		$('.lightBoxOverlay').addClass('is-active');

		// can close with escape key
		$(document).on('keydown.lightBoxVideo', function(e){
			if (e.which == 27) {
				LightBoxVideo.close();
			}
		});

	},

	close: function(){

		if(!LightBoxVideo.playerObject.loading){
			LightBoxVideo.playerObject.unload();
		}

		// remove classes
		$("body").removeClass("is-overlayed");
		$('.lightBoxOverlay').removeClass('is-active');

		// remove esc key binding
		$(document).off('keydown.lightBoxVideo');

	}
}

if (typeof($) == 'function') {
	$(function() {
		CreditRepairSite.initialize();

		
	});
}