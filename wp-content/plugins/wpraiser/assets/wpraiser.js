jQuery( document ).ready(function() {

	// on load, by url fragment
	if(window.location.hash.length > 0) {
		
		// menu
		jQuery('.wpraiser-menu-item').not(jQuery('#wpraiser-nav-' + window.location.hash.substring(1)).closest('a').addClass('wpraiser-menu-active')).removeClass('wpraiser-menu-active');
		jQuery(window.location.hash).removeClass("wpraiser-hidden");
		jQuery('.wpraiser-page').not(jQuery(window.location.hash)).addClass("wpraiser-hidden");
		wpraiser_show_submit_button(window.location.hash);
		
		// scroll to top (needs a short delay when there are notifications)
		setTimeout(function(){ jQuery(document).scrollTop(0); }, 50);
		
		// append fragment to cache purge menu on wp-admin
		jQuery('#wp-admin-bar-wpraiser_menu').find('a').each(function() {
			if (jQuery(this).attr('href').indexOf('#') == -1) {
				jQuery(this).attr("href", jQuery(this).attr("href") + window.location.hash);
			}
		});
		
		// initialize status page
		if(window.location.hash == '#status') {
			wpraiser_get_logs();
		}
		
	} else {
		jQuery('#wpraiser-nav-dashboard').addClass('wpraiser-menu-active');
		jQuery('#dashboard').removeClass("wpraiser-hidden");
	}
	

	// on menu click
    jQuery('.wpraiser-header-nav').on('click', 'a', function (e) {
		
		// prevent default
		e.preventDefault();
		
		// rewrite fragment without scrolling
		window.location.hash = jQuery(this).attr('href');
		
		// set minimum height
		if (jQuery('#adminmenuwrap').not(jQuery('.mobile')).length) {
			jQuery('#wpraiser-wrapper-out').css('min-height', jQuery('#adminmenuwrap').not(jQuery('.mobile')).height() + 'px');
		}
		
		// set active class for menu		
        jQuery('.wpraiser-menu-item').not(jQuery(this).closest('a').addClass('wpraiser-menu-active')).removeClass('wpraiser-menu-active');
		
		// show or hide tab contents by id
		jQuery(jQuery(this).attr('href')).removeClass("wpraiser-hidden");
		jQuery('.wpraiser-page').not(jQuery(jQuery(this).attr('href'))).addClass("wpraiser-hidden");
		
		// show submit button
		wpraiser_show_submit_button(window.location.hash);
			
		// scroll to top (needs a short delay when there are notifications)
		setTimeout(function(){ jQuery(document).scrollTop(0); }, 50);
		
    });
	
	// refresh page on click
	jQuery('.wpraiser-refresh-page').on('click', function() {
		location.reload(true);
    });
	
	// initialize status page on click
    jQuery('#wpraiser-nav-status, .wpraiser-refresh-status').on('click', function() {
		jQuery('.log-css textarea, .log-js textarea').val('loading...');
		jQuery('.log-cache textarea').val('loading...');
		wpraiser_get_logs();
    });
	
	// make submit button visible only in certain sections
	function wpraiser_show_submit_button(id){
		var skip = ["#dashboard", "#status"];
		if (skip.indexOf(id) > -1) {
			jQuery('.wpraiser-save-changes').not(jQuery('.wpraiser-hidden')).addClass("wpraiser-hidden");
		} else {
			jQuery('.wpraiser-save-changes').removeClass("wpraiser-hidden");
		}
	}


	// get logs via ajax
	function wpraiser_get_logs() {
		
		// ajax request
		var data = { 'action': 'wpraiser_get_logs' };
		jQuery.post(ajaxurl, data, function(resp) {
			if(resp.success == 'OK') { 

				// cache log
				jQuery('.log-cache textarea').val(resp.cache_log);
				jQuery('.log-cache textarea').scrollTop(0);
				
				// css log
				jQuery('.log-css textarea').val(resp.css_log);
				jQuery('.log-css textarea').scrollTop(0);
							
				// js log
				jQuery('.log-js textarea').val(resp.js_log);
				jQuery('.log-js textarea').scrollTop(0);
				
			} else {
				// error log
				console.error(resp.success);	
			}
		});
	}
	
	
});


jQuery( document ).ready(function() {

	// help section
	jQuery( ".accordion" ).accordion({ active: false, collapsible: true, heightStyle: "content" });

});