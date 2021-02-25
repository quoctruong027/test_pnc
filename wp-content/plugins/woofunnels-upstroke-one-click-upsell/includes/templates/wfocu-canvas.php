<?php
/**
 * Template Name: No Header Footer
 *
 * @package AeroCheckout
 */
?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?> class="no-js">
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
	do_action( 'woofunnels_container' );
	$attrs_string = WFOCU_Common::get_wfocu_container_attrs();
	?>
	<div class="woofunnels-container wfocu-canvas wfocu-page-template" <?php echo esc_attr( $attrs_string ); ?>>
		<?php
		do_action( 'woofunnels_container_top' );
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		do_action( 'woofunnels_container_bottom' ); ?>
	</div>
	<?php
	do_action( 'woofunnels_wp_footer' );
	wp_footer(); ?>
	</body>
	</html>
<?php
