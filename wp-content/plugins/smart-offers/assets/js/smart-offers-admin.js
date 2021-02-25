(function(){
	'use strict';
	jQuery(document).ready(function(){
		let add_new_offer_button = jQuery( '.post-type-smart_offers .page-title-action' );
		if( 1 === jQuery( add_new_offer_button ).length ) {
			let add_offer_url = jQuery( add_new_offer_button ).attr( 'href' );
			let url_has_params = ( -1 !== add_offer_url.indexOf( '?' ) );
			let add_order_bump_url = add_offer_url + ( url_has_params ? '&' : '?' ) + 'so_offer_type=order_bump';

			let add_new_offer_dropdown = '<span class="so-dropdown-wrapper">';
			add_new_offer_dropdown += '<a href="#" class="page-title-action">' + jQuery(add_new_offer_button).text() + '</a>';
			add_new_offer_dropdown += '<span class="so-dropdown">';
			add_new_offer_dropdown += '<a href="' + add_offer_url + '">' + so_admin_data.i18n_data.default_offer_text + '</a><hr/>';
			add_new_offer_dropdown += '<a href="' + add_order_bump_url + '">' + so_admin_data.i18n_data.new_order_button_text  + '</a></span></span><span class="page-title-action" style="display:none;"></span>';

			jQuery(add_new_offer_button).replaceWith( add_new_offer_dropdown );
		}

		// To highlight 'All Offers' menu when adding new offer
		jQuery('#menu-posts-smart_offers').find('a[href$=smart_offers]').parent().addClass('current');

		// Code to add media image uploader for order bump
		jQuery('.so_order_bump_upload_image').click(function(e){
			e.preventDefault();

    		let media_uploader = wp.media({
				library : {
					type : 'image'
				},
			}).on('select', function() {
				let attachment = media_uploader.state().get('selection').first().toJSON();
				if( 'undefined' !== typeof attachment.id && 'undefined' !== typeof attachment.sizes ) {
				    let attachment_url = ( 'undefined' !== typeof attachment.sizes.thumbnail.url ) ? attachment.sizes.thumbnail.url : '';
				    if( '' !== attachment_url ) {
						jQuery('#so_order_bump_attachment_id').val(attachment.id);
						jQuery('.so_order_bump_image_preview_wrapper .so_order_bump_image_preview').remove();
						jQuery('.so_order_bump_image_preview_wrapper').addClass('image_chosen').prepend('<img class="so_order_bump_image_preview" src="' + attachment_url + '" />');
				    }
				}
			})
			.open();
		});

		// Code to remove added order bump image
		jQuery('.so_order_bump_remove_image').on('click', function(){
			jQuery('.so_order_bump_image_preview_wrapper').removeClass('image_chosen');
			jQuery('.so_order_bump_image_preview_wrapper img').remove();
			jQuery('#so_order_bump_attachment_id').val('');
		});

		// Fetch selected order bump style's CSS in CodeMirror editor.
		jQuery('.so_order_bump_style').on('click', function(e){
			let order_bump_style = jQuery(this).val();
			let order_bump_style_css = jQuery(this).data('so-order-bump-style-default-css');
			let confirm_css_override = window.confirm( so_admin_data.i18n_data.order_bump_css_override_text );
			if(confirm_css_override) {
				let code_mirror_style_elem = jQuery('#so_custom_css + .CodeMirror');
				if( 1 === jQuery(code_mirror_style_elem).length ) {
					let code_mirror_style_editor = jQuery(code_mirror_style_elem)[0].CodeMirror;
					code_mirror_style_editor.setValue( order_bump_style_css );
				}

				jQuery('.so_order_bump_image_field_wrapper,.so_order_bump_intro_text_field').removeClass('disabled');
				
				// Disable order bump image and headline fields as these are not displayed in style 1.
				if( 'style-1' === order_bump_style ) {
					jQuery('.so_order_bump_image_field_wrapper,.so_order_bump_intro_text_field').addClass('disabled');
				} else if( 'style-2' === order_bump_style ) {
					// Disable headline field as it is not displayed in style 2.
					jQuery('.so_order_bump_intro_text_field').addClass('disabled');
				}

				jQuery('.so_order_bump_styles .style_wrapper').removeClass('selected new-style-selected')
				jQuery(this).closest('.style_wrapper').addClass('selected new-style-selected');
			} else {
				// Prevent the selection of order bump style if user cancels the action.
				e.preventDefault();
			}
		});
	});
})(jQuery);
