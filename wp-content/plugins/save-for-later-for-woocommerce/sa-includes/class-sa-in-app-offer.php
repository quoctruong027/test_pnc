<?php
/**
 * StoreApps In app offer
 *
 * @author      StoreApps
 *
 * @package     StoreApps
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;  // Exit if accessed directly.
}

/**
 * Class for handling in app offer for StoreApps
 */
class SA_In_App_Offer {

	/**
	 * Variable to hold instance of this class
	 *
	 * @var $instance
	 */
	private static $instance = null;

	/**
	 * The plugin file
	 *
	 * @var string $plugin_file
	 */
	public $plugin_file = '';

	/**
	 * The plugin url
	 *
	 * @var string $plugin_file
	 */
	public $plugin_url = '';

	/**
	 * The prefix
	 *
	 * @var string $prefix
	 */
	public $prefix = '';

	/**
	 * The option name
	 *
	 * @var string $option_name
	 */
	public $option_name = '';

	/**
	 * The campaign
	 *
	 * @var string $campaign
	 */
	public $campaign = '';

	/**
	 * The start
	 *
	 * @var string $start
	 */
	public $start = '';

	/**
	 * The end
	 *
	 * @var string $end
	 */
	public $end = '';

	/**
	 * Is plugin page
	 *
	 * @var bool $end
	 */
	public $is_plugin_page = false;

	/**
	 * Constructor
	 *
	 * @param array $args Configuration.
	 */
	public function __construct( $args ) {

		$this->plugin_file    = ( ! empty( $args['file'] ) ) ? $args['file'] : '';
		$this->prefix         = ( ! empty( $args['prefix'] ) ) ? $args['prefix'] : '';
		$this->option_name    = ( ! empty( $args['option_name'] ) ) ? $args['option_name'] : '';
		$this->campaign       = ( ! empty( $args['campaign'] ) ) ? $args['campaign'] : '';
		$this->start          = ( ! empty( $args['start'] ) ) ? $args['start'] : '';
		$this->end            = ( ! empty( $args['end'] ) ) ? $args['end'] : '';
		$this->is_plugin_page = ( ! empty( $args['is_plugin_page'] ) ) ? $args['is_plugin_page'] : false;

		add_action( 'admin_footer', array( $this, 'admin_styles_and_scripts' ) );
		add_action( 'admin_notices', array( $this, 'in_app_offer' ) );
		add_action( 'wp_ajax_' . $this->prefix . '_dismiss_action', array( $this, 'dismiss_action' ) );

	}

	/**
	 * Get single instance of this class
	 *
	 * @param array $args Configuration.
	 * @return Singleton object of this class
	 */
	public static function get_instance( $args ) {
		// Check if instance is already exists.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $args );
		}

		return self::$instance;
	}

	/**
	 * Whether to show or not
	 *
	 * @return boolean
	 */
	public function is_show() {

		$timezone_format = _x( 'Y-m-d', 'timezone date format' );
		$current_date    = strtotime( date_i18n( $timezone_format ) );
		$start           = strtotime( $this->start );
		$end             = strtotime( $this->end );
		if ( ( $current_date >= $start ) && ( $current_date <= $end ) ) {
			$option_value  = get_option( $this->option_name, 'yes' );
			$get_post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore

			if ( ( 'product' === $get_post_type || $this->is_plugin_page ) && 'yes' === $option_value ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Admin styles & scripts
	 */
	public function admin_styles_and_scripts() {

		if ( $this->is_show() ) {

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}

			?>
			<script type="text/javascript">
				jQuery(function(){
					jQuery('.sa_offer_container').on('click', '.sa_dismiss a', function(){
						jQuery.ajax({
							url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
							type: 'post',
							dataType: 'json',
							data: {
								action: '<?php echo esc_html( $this->prefix ); ?>_dismiss_action',
								security: '<?php echo esc_html( wp_create_nonce( $this->prefix . '-dismiss-action' ) ); ?>'
							},
							success: function( response ){
								if ( response.success != undefined && response.success != '' && response.success == 'yes' ) {
									jQuery('.sa_offer_container').fadeOut(500, function(){ jQuery('.sa_offer_container').remove(); });
								}
							}
						});
						return false;
					});
				});
			</script>
			<?php

		}

	}

	/**
	 * The offer content
	 */
	public function in_app_offer() {

		if ( $this->is_show() ) {
			?>
			<div class="sa_offer_container"><?php $this->show_offer_content(); ?></div>
			<?php
		}
	}

	/**
	 * The offer content
	 */
	public function show_offer_content() {
		if ( ! wp_script_is( 'jquery' ) ) {
			wp_enqueue_script( 'jquery' );
		}

		?>
		<style type="text/css">
			.sa_offer {
				width: 70%;
				height: auto;
				margin: 1em auto;
				text-align: center;
				background-color: #d4bd49;
				font-size: 1.2em;
				letter-spacing: 3px;
				line-height: 1em;
				padding: 1.2em;
				background-image: url('<?php echo esc_url( plugins_url( 'sa-includes/images/happy-hallowen.png', $this->plugin_file ) ); ?>');
				background-repeat: no-repeat;
				background-size: 40%;
				background-position: left;
			}
			.sa_offer_content {
				margin-left: 40%;
			}
			.sa_offer_heading {
				color: #FFF;
				padding: 1em 0;
			}
			.sa_main_heading {
				color: #b70f0f;
				font-weight: 600;
				line-height: 1.2em;
				position: relative;
			}
			.sa_text {
				font-size: 0.9em;
			}
			.sa_left_text_1 {
				padding: 0.5em 0em;
				color: #000;
				font-size: 1.1em;
				margin-top: 1em;
			}
			.sa_left_text_2 {
				padding: 0.5em 0em;
				color: #000;
				font-size: 1.1em;
				font-weight: bold;
			}
			.sa_left_text_3 {
				padding: 0.5em 0em;
				color: #FFF;
			}
			.sa_right_text {
				color: #FFF;
				font-weight: 600;
				max-width: 50%;
				padding: 0.5em 3em;
				width: auto;
				margin: 0.5em 0 0.5em 0;
				display: inline-block;
				text-decoration: none;
				background: #b70f0f;
			}
			.sa_right_text:hover, .sa_right_text:active {
				color: #FFF;
			}
			.sa_dismiss {
				font-size: 0.7em;
				display: inline-block;
				width: 100%;
				text-align: right;
			}
			.sa_logo {
				width: auto;
				float: right;
				margin: 0 0 0 90%;
			}
			.sa_code {
				background-color: #FFF;
				color: #000;
				padding: 0.4em;
			}
			@media screen
				and (min-device-width: 768px) {
					.sa_offer { letter-spacing: 1px; padding: 0.5em; }
					.sa_logo img { height: 10px; }
					.sa_offer_heading { font-size: 0.5em; line-height: 0.1em; }
					.sa_main_heading { font-size: 1.8em; }
					.sa_left_text_1 { font-size: 0.6em; margin-top: 1em; padding: 0; }
					.sa_left_text_2 { font-size: 0.6em; padding: 0; }
					.sa_code { padding: 0.1em 0.4em; }
					.sa_right_text { padding: 0.3em 2em; margin: 1.3em 0 0.5em; font-size: 0.6em; }
					.sa_left_text_3 { padding: 0; font-size: 0.5em; }
					.sa_dismiss { font-size: 0.5em; }
				}
			@media screen
				and (min-device-width: 1024px) {
					.sa_offer { letter-spacing: 1px; padding: 0.7em; }
					.sa_logo img { height: 10px; }
					.sa_offer_heading { font-size: 0.5em; line-height: 0.1em; }
					.sa_main_heading { font-size: 2em; }
					.sa_left_text_1 { font-size: 0.7em; margin-top: 1em; padding: 0.1em 0; }
					.sa_left_text_2 { font-size: 0.7em; padding: 0.1em 0; }
					.sa_code { padding: 0.1em 0.4em; }
					.sa_right_text { padding: 0.3em 2em; margin: 1.3em 0 0.6em; font-size: 0.7em; }
					.sa_left_text_3 { padding: 0; font-size: 0.6em; }
					.sa_dismiss { font-size: 0.6em; }
				}
			@media screen
				and (min-device-width: 1152px) {
					.sa_offer { letter-spacing: 2px; padding: 0.8em; }
					.sa_logo img { height: 12px; }
					.sa_offer_heading { font-size: 0.6em; line-height: 0.1em; }
					.sa_main_heading { font-size: 2.4em; }
					.sa_left_text_1 { font-size: 0.8em; margin-top: 1em; padding: 0.2em 0; }
					.sa_left_text_2 { font-size: 0.8em; padding: 0.2em 0; }
					.sa_code { padding: 0.1em 0.4em; }
					.sa_right_text { padding: 0.3em 2em; margin: 1.3em 0 0.6em; font-size: 0.8em; }
					.sa_left_text_3 { padding: 0; font-size: 0.7em; }
					.sa_dismiss { font-size: 0.6em; }
				}
			@media screen
				and (min-device-width: 1280px) {
					.sa_offer { letter-spacing: 2px; padding: 0.9em; }
					.sa_logo img { height: 14px; }
					.sa_offer_heading { font-size: 0.7em; line-height: 0.1em; }
					.sa_main_heading { font-size: 2.8em; }
					.sa_left_text_1 { font-size: 0.9em; margin-top: 1em; padding: 0.3em 0; }
					.sa_left_text_2 { font-size: 0.9em; padding: 0.3em 0; }
					.sa_code { padding: 0.2em 0.4em; }
					.sa_right_text { padding: 0.5em 3em; margin: 1.5em 0 0.8em; }
					.sa_left_text_3 { padding: 0; font-size: 0.7em; }
					.sa_dismiss { font-size: 0.6em; }
				}
			@media screen
				and (min-device-width: 1360px) {
					.sa_offer { letter-spacing: 2px; padding: 1em; }
					.sa_logo img { height: 16px; }
					.sa_offer_heading { font-size: 0.7em; line-height: 0.1em; }
					.sa_main_heading { font-size: 3.1em; }
					.sa_left_text_1 { font-size: 0.9em; margin-top: 1em; padding: 0.3em 0; }
					.sa_left_text_2 { font-size: 0.9em; padding: 0.3em 0; }
					.sa_code { padding: 0.2em 0.4em; }
					.sa_right_text { padding: 0.5em 3em; margin: 1.5em 0 0.8em; }
					.sa_left_text_3 { padding: 0; font-size: 0.7em; }
					.sa_dismiss { font-size: 0.6em; }
				}
			@media screen
				and (min-device-width: 1440px) {
					.sa_offer { letter-spacing: 3px; padding: 1.1em; }
					.sa_logo img { height: 16px; }
					.sa_offer_heading { font-size: 0.8em; line-height: 0.1em; }
					.sa_main_heading { font-size: 3.1em; }
					.sa_left_text_1 { font-size: 0.9em; margin-top: 1em; padding: 0.3em 0; }
					.sa_left_text_2 { font-size: 0.9em; padding: 0.3em 0; }
					.sa_code { padding: 0.2em 0.4em; }
					.sa_right_text { padding: 0.5em 3em; margin: 1.5em 0 0.8em; }
					.sa_left_text_3 { padding: 0; font-size: 0.7em; }
					.sa_dismiss { font-size: 0.6em; }
				}
			@media screen
				and (min-device-width: 1600px) {
					.sa_logo img { height: 18px; }
					.sa_offer_heading { font-size: 0.9em; line-height: 0.1em; }
					.sa_main_heading { font-size: 3.2em; }
					.sa_left_text_1 { font-size: 1em; margin-top: 1em; padding: 0.4em 0; }
					.sa_left_text_2 { font-size: 1em; padding: 0.4em 0; }
					.sa_code { padding: 0.25em 0.4em; }
					.sa_right_text { padding: 0.6em 3em; margin: 1.5em 0 1em; }
					.sa_left_text_3 { padding: 0; font-size: 0.8em; }
					.sa_dismiss { font-size: 0.7em; }
				}
			@media screen
				and (min-device-width: 1920px) {
					.sa_logo img { height: 20px; }
					.sa_offer_heading { font-size: 1em; line-height: 0.1em; }
					.sa_main_heading { font-size: 4em; }
					.sa_left_text_1 { font-size: 1.25em; margin-top: 1em; padding: 0.4em 0; }
					.sa_left_text_2 { font-size: 1.25em; padding: 0.4em 0; }
					.sa_code { padding: 0.3em 0.4em; }
					.sa_right_text { padding: 0.8em 4em; margin: 2em 0 1em; }
					.sa_left_text_3 { padding: 0; font-size: 0.9em; }
				}
		</style>
		<div class="sa_offer">
			<div class="sa_logo">
				<img src="<?php echo esc_url( plugins_url( 'sa-includes/images/storeapps-logo.png', $this->plugin_file ) ); ?>" height="20"/>
			</div>
			<div class="sa_offer_content">
				<div class="sa_offer_heading">&mdash; <?php echo esc_html__( 'Halloween Sale' ); ?> &mdash;</div>
				<div class="sa_main_heading"><?php echo esc_html__( '20% OFF Storewide' ); ?></div>
				<div class="sa_text">
					<div class="sa_left_text_1"><?php echo esc_html__( 'Since you are loyal customer, here\'s your bonus' ); ?>:</div>
					<div class="sa_left_text_2"><?php echo esc_html__( 'Apply coupon' ); ?> <span class="sa_code">loyal6</span> <?php echo esc_html__( 'to get additional 6% off' ); ?></div>
					<a class="sa_right_text" href="https://www.storeapps.org/shop/?utm_source=in_app&utm_medium=<?php echo esc_attr( $this->prefix ); ?>_banner&utm_campaign=<?php echo esc_attr( $this->campaign ); ?>" target="_blank"><?php echo esc_html__( 'Claim This Offer' ); ?></a>
					<div class="sa_left_text_3"><?php echo esc_html__( 'Offer ends on 2nd November, 2018 - so hurry' ); ?>..</div>
				</div>
				<div class="sa_dismiss"> <!-- Do not change this class -->
					<a href="javascript:void(0)" style="color: black; text-decoration: none;" title="<?php echo esc_attr__( 'Dismiss' ); ?>"><?php echo esc_html__( 'Hide this' ); ?></a>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(function(){
				jQuery('div.sa_offer').not(':eq(0)').hide();	// To hide offer div if present multiple times.
			});
		</script>
		<?php
	}

	/**
	 * Handle dismiss action
	 */
	public function dismiss_action() {

		check_ajax_referer( $this->prefix . '-dismiss-action', 'security' );

		update_option( $this->option_name, 'no', 'no' );

		wp_send_json( array( 'success' => 'yes' ) );

	}

}
