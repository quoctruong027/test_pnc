<?php
/**
 * Template Name: No Header Footer
 *
 * @package AeroCheckout
 */

?>

    <!DOCTYPE html>
    <html <?php language_attributes(); ?> class="no-js wfacp_html_canvas">
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">
		<?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?>>

	<?php
	if ( function_exists( 'wp_body_open' ) ) {
		wp_body_open();
	}

	do_action( 'wfacp_template_body_top' );


	$atts_string = WFACP_Common::get_template_container_atts();

	?>
    <div class="wfacp-template-container" <?php echo trim( $atts_string ); ?>>

		<?php
		do_action( 'wfacp_template_container_top' );
		while ( have_posts() ) :

			the_post();
			the_content();

		endwhile;
		do_action( 'wfacp_template_container_bottom' );
		?>
    </div>

	<?php do_action( 'wfacp_template_wp_footer' ); ?>

	<?php wp_footer(); ?>
    </body>
    </html>
<?php
