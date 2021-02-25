/*
 * Customizer Scripts
 * Need to rewrite and clean up this file.
 */

jQuery(document).ready(function() {

    /**
     * Change description
     */	 
	jQuery(wcast_customizer.trigger_click).trigger( "click" );    
	jQuery('#customize-theme-controls #accordion-section-themes').hide();
	
	if(wcast_customizer.wcast_enable_delivered_email == 'yes'){
		jQuery('#customize-control-wcast_delivered_email_settings-wcast_enable_delivered_status_email input').prop('disabled', true);
	}

	if(jQuery("#customize-control-tracking_info_settings-show_track_label input").prop("checked") != true){	
		jQuery('#customize-control-tracking_info_settings-track_header_text').hide();
	}
	
	if(jQuery("#customize-control-tracking_info_settings-hide_trackig_header input").prop("checked") == true){	
		jQuery('#customize-control-tracking_info_settings-header_text_change').hide();
	}
	
	if(jQuery("#customize-control-woocommerce_customer_delivered_order_settings-wcast_enable_delivered_ga_tracking input").prop("checked") != true){	
		jQuery('#customize-control-woocommerce_customer_delivered_order_settings-wcast_delivered_analytics_link').hide();
	}
	if(jQuery('#customize-control-tracking_info_settings-hide_table_header input').prop("checked") == true){
		jQuery('#customize-control-tracking_info_settings-provider_header_text').hide();
		jQuery('#customize-control-tracking_info_settings-tracking_number_header_text').hide();
		jQuery('#customize-control-tracking_info_settings-shipped_date_header_text').hide();
		jQuery('#customize-control-tracking_info_settings-show_track_label').hide();		
		jQuery('#customize-control-tracking_info_settings-track_header_text').hide();
		jQuery('#customize-control-tracking_info_settings-table_header_font_size').hide();
		jQuery('#customize-control-tracking_info_settings-table_header_font_color').hide(); 
	} else{
		jQuery('#customize-control-tracking_info_settings-provider_header_text').show();
		jQuery('#customize-control-tracking_info_settings-tracking_number_header_text').show();
		jQuery('#customize-control-tracking_info_settings-shipped_date_header_text').show();		
		jQuery('#customize-control-tracking_info_settings-track_header_text').show();
		jQuery('#customize-control-tracking_info_settings-table_header_font_size').show();
		jQuery('#customize-control-tracking_info_settings-table_header_font_color').show(); 
		if(jQuery("#customize-control-tracking_info_settings-show_track_label input").prop("checked") == true){	
			jQuery('#customize-control-tracking_info_settings-track_header_text').show();
		} else{
			jQuery('#customize-control-tracking_info_settings-track_header_text').hide();
		}
	}
	
	if(jQuery('#customize-control-tracking_info_settings-tracking_number_link input').prop("checked") == true){	
		jQuery('#customize-control-tracking_info_settings-show_track_label').hide();	
		jQuery('#customize-control-tracking_info_settings-tracking_link_bg_color').hide();		
		jQuery('#customize-control-tracking_info_settings-track_header_text').hide();
	} else{
		if(jQuery("#customize-control-tracking_info_settings-show_track_label input").prop("checked") == true && jQuery('#customize-control-tracking_info_settings-tracking_number_link input').prop("checked") != true){	
			jQuery('#customize-control-tracking_info_settings-track_header_text').show();
		}	
		jQuery('#customize-control-tracking_info_settings-show_track_label').show();
		jQuery('#customize-control-tracking_info_settings-tracking_link_bg_color').show();
	}
	
	var tracking_template = jQuery(".tracking_template_select").val();	
	if(tracking_template == 'simple_list'){						
		jQuery('#customize-control-tracking_info_settings-display_shipment_provider_image').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-display_shipment_provider_name').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-remove_date_from_tracking').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_header_block').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-hide_table_header').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-provider_header_text').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-tracking_number_header_text').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-shipped_date_header_text').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-shipped_date_header_text').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-tracking_number_link').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-show_track_label').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-track_header_text').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_header_font_size').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_header_font_color').css('pointer-events','none');
		jQuery('#customize-control-table_header').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_padding').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_bg_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_border_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_border_size').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-header_content_text_align').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_content_font_size').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_content_font_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_content_line_height').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_content_font_weight').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-shipment_link_header').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-tracking_link_font_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-tracking_link_bg_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-tracking_link_border').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-simple_content_header').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-simple_layout_content').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-simple_content_variables').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-simple_provider_font_size').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-simple_provider_font_color').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-show_provider_border').css('pointer-events','all');
		if(jQuery('#customize-control-tracking_info_settings-show_provider_border input').prop("checked") == true){
			jQuery('#customize-control-tracking_info_settings-provider_border_color').css('pointer-events','all');
		}		
	} else{		
		jQuery('#customize-control-tracking_info_settings-simple_content_header').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-simple_layout_content').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-simple_content_variables').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-simple_provider_font_size').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-simple_provider_font_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-show_provider_border').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-provider_border_color').css('pointer-events','none');			
	}
});

jQuery(document).on("click", "#customize-control-tracking_info_settings-show_provider_border input", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-tracking_info_settings-provider_border_color').show();	
	} else{
		jQuery('#customize-control-tracking_info_settings-provider_border_color').hide();	
	}
});

jQuery(document).on("click", "#customize-control-tracking_info_settings-hide_trackig_header input", function(){
	if(jQuery(this).prop("checked") == true){	
		jQuery('#customize-control-tracking_info_settings-header_text_change').hide();
	} else{
		jQuery('#customize-control-tracking_info_settings-header_text_change').show();
	}	
});	
	
jQuery(document).on("change", ".tracking_template_select", function(){
	var tracking_template = jQuery(this).val();
	
	if(tracking_template == 'simple_list'){		
		jQuery('#customize-control-tracking_info_settings-table_content_header').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-display_shipment_provider_image').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-display_shipment_provider_name').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-remove_date_from_tracking').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_header_block').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-hide_table_header').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-provider_header_text').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-tracking_number_header_text').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-shipped_date_header_text').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-shipped_date_header_text').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-tracking_number_link').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-show_track_label').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-track_header_text').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_header_font_size').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_header_font_color').css('pointer-events','none');
		jQuery('#customize-control-table_header').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_padding').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_bg_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_border_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_border_size').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-header_content_text_align').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_content_font_size').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_content_font_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_content_line_height').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_content_font_weight').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-shipment_link_header').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-tracking_link_font_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-tracking_link_bg_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-tracking_link_border').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-simple_content_header').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-simple_layout_content').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-simple_content_variables').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-simple_provider_font_size').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-simple_provider_font_color').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-show_provider_border').css('pointer-events','all');
		if(jQuery('#customize-control-tracking_info_settings-show_provider_border input').prop("checked") == true){
			jQuery('#customize-control-tracking_info_settings-provider_border_color').css('pointer-events','all');	
		}
	} else{		
		jQuery('#customize-control-tracking_info_settings-simple_content_header').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-simple_layout_content').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-simple_content_variables').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-simple_provider_font_size').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-simple_provider_font_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-show_provider_border').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-provider_border_color').css('pointer-events','none');
		jQuery('#customize-control-tracking_info_settings-table_content_header').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-display_shipment_provider_image').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-display_shipment_provider_name').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-remove_date_from_tracking').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-table_header_block').css('pointer-events','all');
		jQuery('#customize-control-show_table_header').css('pointer-events','all');							
		jQuery('#customize-control-tracking_info_settings-tracking_number_link').css('pointer-events','all');
		jQuery('#customize-control-table_header').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-table_padding').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-table_bg_color').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-table_border_color').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-table_border_size').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-header_content_text_align').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-table_content_font_size').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-table_content_font_color').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-table_content_line_height').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-table_content_font_weight').css('pointer-events','all');
		jQuery('#customize-control-tracking_info_settings-hide_table_header').css('pointer-events','all');
		if(jQuery('#customize-control-tracking_info_settings-hide_table_header input').prop("checked") != true){
			jQuery('#customize-control-tracking_info_settings-provider_header_text').css('pointer-events','all');
			jQuery('#customize-control-tracking_info_settings-tracking_number_header_text').css('pointer-events','all');
			jQuery('#customize-control-tracking_info_settings-shipped_date_header_text').css('pointer-events','all');			
			jQuery('#customize-control-tracking_info_settings-table_header_font_size').css('pointer-events','all');
			jQuery('#customize-control-tracking_info_settings-table_header_font_color').css('pointer-events','all'); 
		}		
		if(jQuery("#customize-control-tracking_info_settings-show_track_label input").prop("checked") == true && jQuery('#customize-control-tracking_info_settings-tracking_number_link input').prop("checked") != true){	
			jQuery('#customize-control-tracking_info_settings-track_header_text').css('pointer-events','all');
		}	
		if(jQuery('#customize-control-tracking_info_settings-tracking_number_link input').prop("checked") != true){	
			jQuery('#customize-control-tracking_info_settings-show_track_label').css('pointer-events','all');
			jQuery('#customize-control-tracking_info_settings-shipment_link_header').css('pointer-events','all');
			jQuery('#customize-control-tracking_info_settings-tracking_link_font_color').css('pointer-events','all');
			jQuery('#customize-control-tracking_info_settings-tracking_link_bg_color').css('pointer-events','all');
			jQuery('#customize-control-tracking_info_settings-tracking_link_border').css('pointer-events','all');
		}
	}	
});
jQuery(document).on("change", "#customize-control-tracking_info_settings-tracking_number_link input", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-tracking_info_settings-show_track_label').hide();
		jQuery('#customize-control-tracking_info_settings-track_header_text').hide();
		jQuery('#customize-control-tracking_info_settings-tracking_link_bg_color').hide();		
	} else{
		if(jQuery("#customize-control-tracking_info_settings-show_track_label input").prop("checked") == true){
			jQuery('#customize-control-tracking_info_settings-track_header_text').show();
		}	
		jQuery('#customize-control-tracking_info_settings-show_track_label').show();		
		jQuery('#customize-control-tracking_info_settings-tracking_link_bg_color').show();		
	}
});

jQuery(document).on("change", "#customize-control-tracking_info_settings-show_track_label input", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-tracking_info_settings-track_header_text').show();
	} else{
		jQuery('#customize-control-tracking_info_settings-track_header_text').hide();
	}
});

jQuery(document).on("change", "#customize-control-tracking_info_settings-hide_table_header input", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-tracking_info_settings-provider_header_text').hide();
		jQuery('#customize-control-tracking_info_settings-tracking_number_header_text').hide();
		jQuery('#customize-control-tracking_info_settings-shipped_date_header_text').hide();
		jQuery('#customize-control-tracking_info_settings-show_track_label').hide();
		jQuery('#customize-control-tracking_info_settings-show_track_label').hide();
		jQuery('#customize-control-tracking_info_settings-track_header_text').hide();
		jQuery('#customize-control-tracking_info_settings-table_header_font_size').hide();
		jQuery('#customize-control-tracking_info_settings-table_header_font_color').hide(); 
	} else{
		jQuery('#customize-control-tracking_info_settings-provider_header_text').show();
		jQuery('#customize-control-tracking_info_settings-tracking_number_header_text').show();
		jQuery('#customize-control-tracking_info_settings-shipped_date_header_text').show();
		jQuery('#customize-control-tracking_info_settings-show_track_label').show();
		jQuery('#customize-control-tracking_info_settings-track_header_text').show();
		jQuery('#customize-control-tracking_info_settings-table_header_font_size').show();
		jQuery('#customize-control-tracking_info_settings-table_header_font_color').show(); 
		if(jQuery("#customize-control-tracking_info_settings-show_track_label input").prop("checked") == true){	
			jQuery('#customize-control-tracking_info_settings-track_header_text').show();
		}
	}
});
jQuery(document).on("change", "#customize-control-woocommerce_customer_delivered_order_settings-wcast_enable_delivered_ga_tracking input", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-woocommerce_customer_delivered_order_settings-wcast_delivered_analytics_link').show();
	} else{
		jQuery('#customize-control-woocommerce_customer_delivered_order_settings-wcast_delivered_analytics_link').hide();
	}
});	
jQuery(document).on("change", "#_customize-input-customizer_delivered_order_settings_enabled", function(){	
	if(jQuery(this).prop("checked") == true){
		jQuery('#customize-control-wcast_delivered_email_settings-wcast_enable_delivered_status_email input').prop('disabled', true);
	} else{
		jQuery('#customize-control-wcast_delivered_email_settings-wcast_enable_delivered_status_email input').removeAttr('disabled');
	}
});
    // Handle mobile button click
    function custom_size_mobile() {
    	// get email width.
    	var email_width = '684';
    	var ratio = email_width/304;
    	var framescale = 100/ratio;
    	var framescale = framescale/100;
    	jQuery('#customize-preview iframe').width(email_width+'px');
    	jQuery('#customize-preview iframe').css({
				'-webkit-transform' : 'scale(' + framescale + ')',
				'-moz-transform'    : 'scale(' + framescale + ')',
				'-ms-transform'     : 'scale(' + framescale + ')',
				'-o-transform'      : 'scale(' + framescale + ')',
				'transform'         : 'scale(' + framescale + ')'
		});
    }
	jQuery('#customize-footer-actions .preview-mobile').click(function(e) {
		custom_size_mobile();
	});
		jQuery('#customize-footer-actions .preview-desktop').click(function(e) {
		jQuery('#customize-preview iframe').width('100%');
		jQuery('#customize-preview iframe').css({
				'-webkit-transform' : 'scale(1)',
				'-moz-transform'    : 'scale(1)',
				'-ms-transform'     : 'scale(1)',
				'-o-transform'      : 'scale(1)',
				'transform'         : 'scale(1)'
		});
	});
	jQuery('#customize-footer-actions .preview-tablet').click(function(e) {
		jQuery('#customize-preview iframe').width('100%');
		jQuery('#customize-preview iframe').css({
				'-webkit-transform' : 'scale(1)',
				'-moz-transform'    : 'scale(1)',
				'-ms-transform'     : 'scale(1)',
				'-o-transform'      : 'scale(1)',
				'transform'         : 'scale(1)'
		});
	});
	
(function ( api ) {
    api.section( 'customer_delivered_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {	
			
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'customer_partial_shipped_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.partial_shipped_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_custom_completed_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.custom_completed_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_shipped_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.shipped_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'customer_updated_tracking_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.updated_tracking_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'ast_tracking_general_section', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.tracking_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'ast_tracking_simple_section', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.tracking_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'ast_tracking_table_section', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.tracking_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'ast_tracking_per_item', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.tracking_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'customer_failure_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_failure_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_intransit_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_intransit_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_onhold_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_onhold_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_outfordelivery_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_outfordelivery_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_delivered_status_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_delivered_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_returntosender_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_returntosender_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'customer_availableforpickup_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.customer_availableforpickup_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );
(function ( api ) {
    api.section( 'admin_late_shipments_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.admin_late_shipments_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );


jQuery(document).on("change", ".preview_order_select", function(){
	var wcast_preview_order_id = jQuery(this).val();
	var data = {
		action: 'update_email_preview_order',
		wcast_preview_order_id: wcast_preview_order_id,	
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: data,
		type: 'POST',
		success: function(response) {			
			jQuery(".preview_order_select option[value="+wcast_preview_order_id+"]").attr('selected', 'selected');			
		},
		error: function(response) {
			console.log(response);			
		}
	});	
});