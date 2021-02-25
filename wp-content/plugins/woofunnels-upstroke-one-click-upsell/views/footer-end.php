<?php
do_action( 'footer_before_print_scripts' );
do_action( 'wfocu_footer_before_print_scripts' );
WFOCU_Core()->assets->print_scripts();
do_action( 'footer_after_print_scripts' );
do_action( 'wfocu_footer_after_print_scripts' );
if ( true === apply_filters( 'wfocu_allow_externals_on_customizer', false ) ) {
	wp_footer();
}
?>
<style type="text/css" data-type="wfocu"></style>

</body>
</html>
