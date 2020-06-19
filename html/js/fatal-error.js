

var ShowPagePop = {

	opacity: '0.65',			// the opacity of the pop background
	displayDuration: '600', 	// speed in milliseconds to display pop

	show: function(){

		jQuery('#page_pop_background').fadeTo(ShowPagePop.displayDuration, ShowPagePop.opacity);

			// bind close on background click
			jQuery('#page_pop_background').click(function(){
				ShowPagePop.hide();
			});
	},

	hide: function(){
		jQuery('#page_pop_background').fadeOut(ShowPagePop.displayDuration);

		// un-bind window resizing
		jQuery(window).unbind();
	}

};

