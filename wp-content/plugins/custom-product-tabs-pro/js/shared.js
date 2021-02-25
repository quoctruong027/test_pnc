jQuery( document ).ready( function() {
	jQuery( '.yikes-custom-dismiss' ).click( cptpro_remove_custom_message );

	jQuery( 'input[type="checkbox"]' ).iCheck({
		checkboxClass: 'icheckbox_flat-blue',
		radioClass: 'iradio_flat-blue'
	});
});

function cptpro_remove_custom_message() {
	jQuery( this ).parents( '.yikes-custom-notice' ).fadeOut();
}