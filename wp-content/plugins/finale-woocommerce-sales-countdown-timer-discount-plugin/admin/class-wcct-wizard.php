<?php

/**
 * Class WCCT_Wizard
 * Class controls rendering and behaviour of wizard for the Finale
 */
class WCCT_Wizard {

	public static $is_wizard_done;
	public static $step;
	public static $suffix;
	public static $steps;
	public static $license_state = null;
	public static $key = '';

	public static function init() {

		add_action( 'admin_init', array( __CLASS__, 'steps' ), 4 );
		add_action( 'admin_init', array( __CLASS__, 'setup_wizard' ), 5 );

	}


	public static function steps() {
		self::$steps = array(
			'welcome'  => array(
				'name' => __( 'Welcome', 'wcct_setup' ),
				'view' => array( __CLASS__, 'wcct_setup_introduction' ),
			),
			'activate' => array(
				'name' => __( 'Activate', 'wcct_setup' ),
				'view' => array( __CLASS__, 'wcct_setup_activate' ),
			),
			'ready'    => array(
				'name' => __( 'Ready', 'wcct_setup' ),
				'view' => array( __CLASS__, 'wcct_setup_ready' ),
			),

		);
		self::$steps = apply_filters( 'wcct_wizard_steps', self::$steps );

		return self::$steps;
	}

	public static function render_page() {

	}

	/**
	 * Show the setup wizard
	 */
	public static function setup_wizard() {

		if ( empty( $_GET['page'] ) || 'xlplugins' !== $_GET['page'] ) {
			return;
		}
		if ( empty( $_GET['tab'] ) || 'finale-woocommerce-sales-countdown-timer-discount-plugin' . '-wizard' !== $_GET['tab'] ) {
			return;
		}

		ob_end_clean();

		self::$step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( self::$steps ) );

		//enqueue style for admin notices
		wp_enqueue_style( 'wp-admin' );
		wp_enqueue_style( 'install' );
		wp_enqueue_style( 'dashicons' );

		ob_start();
		self::setup_wizard_header();
		self::setup_wizard_steps();
		$show_content = true;
		echo '<div class="wcct-setup-content">';

		if ( $show_content ) {
			self::setup_wizard_content();
		}
		echo '</div>';
		self::setup_wizard_footer();
		exit;
	}

	/**
	 * Setup Wizard Header
	 */
	public static function setup_wizard_header() {
		?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width"/>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title><?php _e( 'Plugin &rsaquo; Setup Wizard', 'wcct_setup' ); ?></title>
			<?php wp_print_scripts( 'wcct-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>

        </head>
		<?php self::setup_css(); ?>
        <body class="wcct-setup wp-core-ui">
        <h1 id="wc-logo"><img width="200px;" src="//storage.googleapis.com/xlplugins/xlplugins-w200.png"/></h1>
		<?php
	}

	/**
	 * Output the steps
	 */
	public static function setup_wizard_steps() {
		$ouput_steps = self::$steps;
		array_shift( $ouput_steps );
		?>
        <ol class="wcct-setup-steps">
			<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
                <li class="
				<?php
				$show_link = false;
				if ( $step_key === self::$step ) {
					echo 'active';
				} elseif ( array_search( self::$step, array_keys( self::$steps ) ) > array_search( $step_key, array_keys( self::$steps ) ) ) {
					echo 'done';
					$show_link = true;
				}
				?>
				">
					<?php

					echo esc_html( $step['name'] );

					?>
                </li>
			<?php endforeach; ?>
        </ol>
		<?php
	}

	/**
	 * Setup Wizard Footer
	 */
	public static function setup_wizard_footer() {
		?>
        <a class="wc-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php _e( 'Return to the WordPress Dashboard', 'wcct_setup' ); ?></a>
        </body>
		<?php
		@do_action( 'admin_footer' );
		do_action( 'admin_print_footer_scripts' );
		?>
        </html>
		<?php
	}


	public static function wcct_setup_introduction() {
		?>
        <h1><?php _e( 'Thank you for choosing Finale from XLPlugins.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></h1>
        <p class="lead"><?php printf( __( 'This wizard will help you activate your license and tell you about important links & support options.' ), wp_get_theme() ); ?></p>
        <p>It should take less than a minute to set up.</p>
        <p class="wcct-setup-actions step">
            <a href="<?php echo esc_url( self::get_next_step_link() ); ?>"
               class="button-primary button button-large button-next"><?php _e( 'Let\'s Go!' ); ?></a>

        </p>
		<?php
	}

	public static function wcct_setup_ready() {
		?>
        <h1><?php printf( __( 'Thank You For Activating %s' ), 'Finale' ); ?></h1>
        <p><?php printf( __( 'You are all set to start your first campaign.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) ); ?></p>
        <p><?php printf( __( 'Few Important Links -', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) ); ?>
            <br/>
            Click on these links below and keep them handy. You are ready to go!
        <ul>
            <li>
                <a target="_blank" href="https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/getting-started/setup-your-first-finale-campaign/">Set up
                    your first Campaign </a>
            </li>
            <li>
                <a target="_blank" href="https://xlplugins.com/woocommerce-discounts-deals/">Learn about variety of campaigns you can deploy </a>
            </li>
            <li>
                <a target="_blank" href="https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/getting-started/">Explore documentation to learn about all
                    the features</a>
            </li>
            <li>
                <a target="_blank" href="https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/troubleshooting-guides/">Troubleshooting guides to solve
                    common issues</a>
            </li>
            <li>
                <a target="_blank" href="https://xlplugins.com/support/">Raise a support ticket</a>
            </li>
        </p>

        </ul>
        <p class="wcct-setup-actions step">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer' ) ); ?>"
               class="button-primary button button-large button-next"><?php _e( 'Go to Campaigns' ); ?></a>

        </p>
		<?php
	}

	public static function wcct_setup_activate() {

		?>
        <h2> Activate Finale</h2>
        <form id="wcct_verify_license" action="" method="POST">
            <input type="hidden" name="_step_name" value="license_key">
            <div class="about-text">
                <p>
                    Enter your Finale License Key below. Your key unlocks access to dashboard updates and support.
                    <br/>You can find your key on the Account <a target="_blank" href="https://xlplugins.com/">Dashboard Page</a> site.
                </p>
                <p>
                    <input style="width: 100%; padding: 10px;" type="text" required="required" class="regular-text" id="license_key" value="<?php echo self::$key; ?>" name="license_key" placeholder="Enter Your License Key">
					<?php
					if ( self::$license_state === false ) {
						echo '<span class="wcct_invalid_license">Invalid Key. Ensure that your are using valid license key. Try again.</span>';
					}
					?>
                </p>
				<?php
				$optin = get_option( 'xlp_is_opted' );
				if ( 'yes' != $optin ) {
					$country_code            = WC()->countries->get_base_country();
					$tax_supported_countries = WC()->countries->get_european_union_countries();
					$check                   = in_array( $country_code, $tax_supported_countries, true );

					?>
                    <p style="margin-bottom: 10px;"><input name="xlp_is_opted" type="checkbox" <?php echo ( true === $check ) ? 'checked' : ''; ?> > Help Finale: Woocommerce sales countdown timer
                        discount plugin improve with usage tracking.</p>
                    <p>Gathering usage data allows us to make Finale: Woocommerce sales countdown timer discount plugin better - Your store will be considered as we evaluate new features, judge the
                        quality of an update, or determine if an improvement makes sense. If you would rather opt-out, and do not check this box, we will not know this store exists and will not
                        collect any usage data. <a target="_blank" href="https://xlplugins.com/data-collection-policy/?utm_source=finale-pro&utm_campaign=wizard&utm_medium=text&utm_term=optin">Read
                            more about what we collect.</a></p>
					<?php
				}
				?>
                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'woocommerce-settings' ); ?>"/>
                <input type="hidden" name="_redirect_link" value="<?php echo self::get_next_step_link(); ?>"/>
            </div>
            <div>
                <p class="wcct-setup-actions step">
                    <input class="button-primary button button-large button-next" type="submit" value="Activate" name="wcct_verify_license"></div>
            </p>
            <p>
                Unable to find license key? <br/>
                Follow <a target="_blank" href="https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/getting-started/installation/">this step by step
                    guide</a> to find the license key.
            </p>

            <p><strong>Note:</strong> This is just a one time activation process. <i>You plugin would continue to work as it is even if your license key is expired.</i> Ofcourse,you would loose access
                to support and future updates if your license expires.</p>
        </form>
		<?php
	}

	public static function get_next_step_link() {
		$keys = array_keys( self::$steps );

		return add_query_arg( 'step', $keys[ array_search( self::$step, array_keys( self::$steps ) ) + 1 ], remove_query_arg( 'translation_updated' ) );
	}

	public static function setup_css() {
		?>
        <style>
            .qm-no-js {
                display: none !important;
            }

            li[data-slug="woocommerce"] > span,
            tr[data-content="attachment"] {
                display: none !important;
            }

            .wp-core-ui .woocommerce-button {
                background-color: #bb77ae !important;
                border-color: #A36597 !important;;
                -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, .25), 0 1px 0 #A36597 !important;;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, .25), 0 1px 0 #A36597 !important;;
                text-shadow: 0 -1px 1px #A36597, 1px 0 1px #A36597, 0 1px 1px #A36597, -1px 0 1px #A36597 !important;;
                opacity: 1;
            }

            .wcct-setup-content ul {
                list-style: disc
            }

            .wcct-setup-content h1 {
                line-height: 30px;
            }

            .wcct-setup-content p.lead {
                font-size: 1.2em;
                color: #000;
                border-bottom: 1px solid #eee;
                padding-bottom: 15px;
            }

            .wcct-setup-content p.success {
                color: #7eb62e !important;
            }

            .wcct-setup-content p.error {
                color: red !important;
            }

            /*
			tr[data-content="product_variation"]{
			  display: none!important;
			} */

            .wcct-setup-content p, .wcct-setup-content table {
                font-size: 1em;
                line-height: 1.75em;
                color: #666
            }

            body {
                margin: 30px auto 24px;
                box-shadow: none;
                background: #f1f1f1;
                padding: 0
            }

            #wc-logo {
                border: 0;
                margin: 0 0 24px;
                padding: 0;
                text-align: center
            }

            #wc-logo img {
                max-width: 50%
            }

            .wcct-setup-content {
                box-shadow: 0 1px 3px rgba(0, 0, 0, .13);
                padding: 24px 24px 0;
                background: #fff;
                overflow: hidden;
                zoom: 1
            }

            .wcct-setup-content h1, .wcct-setup-content h2, .wcct-setup-content h3, .wcct-setup-content table {
                margin: 0 0 24px;
                border: 0;
                padding: 0;
                color: #666;
                clear: none
            }

            .wcct-setup-content table {
                margin: 0;
            }

            .wcct-setup-content p {
                margin: 0 0 24px
            }

            .wcct-setup-content a {
                color: #0091cd
            }

            .wcct-setup-content a:focus, .wcct-setup-content a:hover {
                color: #111
            }

            .wcct-setup-content .form-table th {
                width: 35%;
                vertical-align: top;
                font-weight: 400
            }

            .wcct-setup-content .form-table td {
                vertical-align: top
            }

            .wcct-setup-content .form-table td input, .wcct-setup-content .form-table td select {
                width: 100%;
                box-sizing: border-box
            }

            .wcct-setup-content .form-table td input[size] {
                width: auto
            }

            .wcct-setup-content .form-table td .description {
                line-height: 1.5em;
                display: block;
                margin-top: .25em;
                color: #999;
                font-style: italic
            }

            .wcct-setup-content .form-table td .input-checkbox, .wcct-setup-content .form-table td .input-radio {
                width: auto;
                box-sizing: inherit;
                padding: inherit;
                margin: 0 .5em 0 0;
                box-shadow: none
            }

            .wcct-setup-content .form-table .section_title td {
                padding: 0
            }

            .wcct-setup-content .form-table .section_title td h2, .wcct-setup-content .form-table .section_title td p {
                margin: 12px 0 0
            }

            .wcct-setup-content .form-table td, .wcct-setup-content .form-table th {
                padding: 12px 0;
                margin: 0;
                border: 0
            }

            .wcct-setup-content .form-table td:first-child, .wcct-setup-content .form-table th:first-child {
                padding-right: 1em
            }

            .wcct-setup-content .form-table table.tax-rates {
                width: 100%;
                font-size: .92em
            }

            .wcct-setup-content .form-table table.tax-rates th {
                padding: 0;
                text-align: center;
                width: auto;
                vertical-align: middle
            }

            .wcct-setup-content .form-table table.tax-rates td {
                border: 1px solid #eee;
                padding: 6px;
                text-align: center;
                vertical-align: middle
            }

            .wcct-setup-content .form-table table.tax-rates td input {
                outline: 0;
                border: 0;
                padding: 0;
                box-shadow: none;
                text-align: center
            }

            .wcct-setup-content .form-table table.tax-rates td.sort {
                cursor: move;
                color: #ccc
            }

            .wcct-setup-content .form-table table.tax-rates td.sort:before {
                content: "\f333";
                font-family: dashicons
            }

            .wcct-setup-content .form-table table.tax-rates .add {
                padding: 1em 0 0 1em;
                line-height: 1em;
                font-size: 1em;
                width: 0;
                margin: 6px 0 0;
                height: 0;
                overflow: hidden;
                position: relative;
                display: inline-block
            }

            .wcct-setup-content .form-table table.tax-rates .add:before {
                content: "\f502";
                font-family: dashicons;
                position: absolute;
                left: 0;
                top: 0
            }

            .wcct-setup-content .form-table table.tax-rates .remove {
                padding: 1em 0 0 1em;
                line-height: 1em;
                font-size: 1em;
                width: 0;
                margin: 0;
                height: 0;
                overflow: hidden;
                position: relative;
                display: inline-block
            }

            .wcct-setup-content .form-table table.tax-rates .remove:before {
                content: "\f182";
                font-family: dashicons;
                position: absolute;
                left: 0;
                top: 0
            }

            .wcct-setup-content .wcct-setup-plugins {
                width: 100%;
                border-top: 1px solid #eee
            }

            .wcct-setup-content .wcct-setup-plugins thead th {
                display: none
            }

            .wcct-setup-content .wcct-setup-plugins .plugin-name {
                width: 30%;
                font-weight: 700
            }

            .wcct-setup-content .wcct-setup-plugins td, .wcct-setup-content .wcct-setup-plugins th {
                padding: 14px 0;
                border-bottom: 1px solid #eee
            }

            .wcct-setup-content .wcct-setup-plugins td:first-child, .wcct-setup-content .wcct-setup-plugins th:first-child {
                padding-right: 9px
            }

            .wcct-setup-content .wcct-setup-plugins th {
                padding-top: 0
            }

            .wcct-setup-content .wcct-setup-plugins .page-options p {
                color: #777;
                margin: 6px 0 0 24px;
                line-height: 1.75em
            }

            .wcct-setup-content .wcct-setup-plugins .page-options p input {
                vertical-align: middle;
                margin: 1px 0 0;
                height: 1.75em;
                width: 1.75em;
                line-height: 1.75em
            }

            .wcct-setup-content .wcct-setup-plugins .page-options p label {
                line-height: 1
            }

            @media screen and (max-width: 782px) {
                .wcct-setup-content .form-table tbody th {
                    width: auto
                }
            }

            .wcct-setup-content .twitter-share-button {
                float: right
            }

            .wcct-setup-content .wcct-setup-next-steps {
                overflow: hidden;
                margin: 0 0 24px
            }

            .wcct-setup-content .wcct-setup-next-steps h2 {
                margin-bottom: 12px
            }

            .wcct-setup-content .wcct-setup-next-steps .wcct-setup-next-steps-first {
                float: left;
                width: 50%;
                box-sizing: border-box
            }

            .wcct-setup-content .wcct-setup-next-steps .wcct-setup-next-steps-last {
                float: right;
                width: 50%;
                box-sizing: border-box
            }

            .wcct-setup-content .wcct-setup-next-steps ul {
                padding: 0 2em 0 0;
                list-style: none;
                margin: 0 0 -.75em
            }

            .wcct-setup-content .wcct-setup-next-steps ul li a {
                display: block;
                padding: 0 0 .75em
            }

            .wcct-setup-content .wcct-setup-next-steps ul .setup-product a {
                text-align: center;
                font-size: 1em;
                padding: 1em;
                line-height: 1.75em;
                height: auto;
                margin: 0 0 .75em;
                opacity: 1;
            }

            .wcct-setup-content .wcct-setup-next-steps ul .setup-product a.button-primary {
                background-color: #0091cd;
                border-color: #0091cd;
                -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, .2), 0 1px 0 rgba(0, 0, 0, .15);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, .2), 0 1px 0 rgba(0, 0, 0, .15)
            }

            .wcct-setup-content .wcct-setup-next-steps ul li a:before {
                color: #82878c;
                font: 400 20px/1 dashicons;
                speak: none;
                display: inline-block;
                padding: 0 10px 0 0;
                top: 1px;
                position: relative;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                text-decoration: none !important;
                vertical-align: top
            }

            .wcct-setup-content .wcct-setup-next-steps ul .documentation a:before {
                content: "\f331"
            }

            .wcct-setup-content .wcct-setup-next-steps ul .howto a:before {
                content: "\f223"
            }

            .wcct-setup-content .wcct-setup-next-steps ul .rating a:before {
                content: "\f155"
            }

            .wcct-setup-content .wcct-setup-next-steps ul .support a:before {
                content: "\f307"
            }

            .wcct-setup-content .updated, .wcct-setup-content .woocommerce-language-pack, .wcct-setup-content .woocommerce-tracker {
                padding: 24px 24px 0;
                margin: 0 0 24px;
                overflow: hidden;
                background: #f5f5f5
            }

            .wcct-setup-content .updated p, .wcct-setup-content .woocommerce-language-pack p, .wcct-setup-content .woocommerce-tracker p {
                padding: 0;
                margin: 0 0 12px
            }

            .wcct-setup-content .updated p:last-child, .wcct-setup-content .woocommerce-language-pack p:last-child, .wcct-setup-content .woocommerce-tracker p:last-child {
                margin: 0 0 24px
            }

            .wcct-setup-steps {
                padding: 0 0 24px;
                margin: 0;
                list-style: none;
                overflow: hidden;
                color: #ccc;
                width: 100%;
                display: -webkit-inline-flex;
                display: -ms-inline-flexbox;
                display: inline-flex
            }

            .wcct-setup-steps li {
                width: 50%;
                float: left;
                padding: 0 0 .8em;
                margin: 0;
                text-align: center;
                position: relative;
                border-bottom: 4px solid #ccc;
                line-height: 1.4em
            }

            .wcct-setup-steps li:before {
                content: "";
                border: 4px solid #ccc;
                border-radius: 100%;
                width: 4px;
                height: 4px;
                position: absolute;
                bottom: 0;
                left: 50%;
                margin-left: -6px;
                margin-bottom: -8px;
                background: #fff
            }

            .wcct-setup-steps li a {
                text-decoration: none;
            }

            .wcct-setup-steps li.active {
                border-color: #0091cd;
                color: #0091cd
            }

            .wcct-setup-steps li.active a {
                color: #0091cd
            }

            .wcct-setup-steps li.active:before {
                border-color: #0091cd
            }

            .wcct-setup-steps li.done {
                border-color: #0091cd;
                color: #0091cd
            }

            .wcct-setup-steps li.done a {
                color: #0091cd
            }

            .wcct-setup-steps li.done:before {
                border-color: #0091cd;
                background: #0091cd
            }

            .wcct-setup .wcct-setup-actions {
                overflow: hidden
            }

            .wcct-setup .wcct-setup-actions .button {
                float: right;
                font-size: 1.25em;
                padding: .5em 1em;
                line-height: 1em;
                margin-right: .5em;
                height: auto
            }

            .wcct-setup .wcct-setup-actions .button-primary {
                margin: 0;
                float: right;
                opacity: 1;
            }

            .wc-return-to-dashboard {
                font-size: .85em;
                color: #b5b5b5;
                margin: 1.18em 0;
                display: block;
                text-align: center
            }

            .dtbaker_loading_button_current {
                color: #CCC !important;
                text-align: center;

            }

            .wcct-wizard-plugins li {
                position: relative;
            }

            .wcct-wizard-plugins li span {
                padding: 0 0 0 10px;
                font-size: 0.9em;
                color: #0091cd;
                display: inline-block;
                position: relative;

            }

            .wcct-wizard-plugins.installing li .spinner {
                visibility: visible;
            }

            .wcct-wizard-plugins li .spinner {
                display: inline-block;
                position: absolute;

            }

            .wcct-setup-pages {
                width: 100%;
            }

            .wcct-setup-pages .check {
                width: 35px;
            }

            .wcct-setup-pages .item {
                width: 90px;
            }

            .wcct-setup-pages td,
            .wcct-setup-pages th {
                padding: 5px;
            }

            .wcct-setup-pages .status {
                display: none;
            }

            .wcct-setup-pages.installing .status {
                display: table-cell;
            }

            .wcct-setup-pages.installing .status span {
                display: inline-block;
                position: relative;
            }

            .wcct-setup-pages.installing .description {
                display: none;
            }

            .wcct-setup-pages.installing .spinner {
                visibility: visible;
            }

            .wcct-setup-pages .spinner {
                display: inline-block;
                position: absolute;

            }

            .theme-presets {
                background-color: rgba(0, 0, 0, .03);
                padding: 10px 20px;
                margin-left: -25px;
                margin-right: -25px;
                margin-bottom: 20px;
            }

            .theme-presets ul {
                list-style: none;
                margin: 0px 0 15px 0;
                padding: 0;
                overflow-x: auto;
                display: block;
                white-space: nowrap;
            }

            .theme-presets ul li {
                list-style: none;
                display: inline-block;
                padding: 6px;
                margin: 0;
                vertical-align: bottom;
            }

            .theme-presets ul li.current {
                background: #000;
                border-radius: 5px;
            }

            .theme-presets ul li a {
                float: left;
                line-height: 0;
            }

            .theme-presets ul li a img {
                width: 160px;
                height: auto;
            }

            .wcct_invalid_license {
                font-style: italic;
                color: #dc3232;
            }
        </style>
		<?php
	}

	/**
	 * Output the content for the current step
	 */
	public static function setup_wizard_content() {
		isset( self::$steps[ self::$step ] ) ? call_user_func( self::$steps[ self::$step ]['view'] ) : false;
	}

	public static function set_license_state( $state = false ) {
		self::$license_state = $state;
	}


	public static function set_license_key( $key = false ) {
		self::$key = $key;
	}


}

WCCT_Wizard::init();
