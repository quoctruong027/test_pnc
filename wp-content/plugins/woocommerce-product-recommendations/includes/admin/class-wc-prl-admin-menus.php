<?php
/**
 * WC_PRL_Admin_Menus class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup PRL menus in WP admin.
 *
 * @version 1.3.3
 */
class WC_PRL_Admin_Menus {

	/**
	 * Setup.
	 */
	public static function init() {
		self::add_hooks();
	}

	/**
	 * Admin hooks.
	 */
	public static function add_hooks() {

		// Tabs.
		add_action( 'all_admin_notices', array( __CLASS__, 'render_tabs' ), 5 );

		// Menu.
		add_action( 'admin_menu', array( __CLASS__, 'prl_menu' ), 10 );
		add_action( 'current_screen', array( __CLASS__, 'prl_remove_submenu_link' ), 10 );
		add_filter( 'parent_file', array( __CLASS__, 'prl_fix_menu_highlight' ) );

		// Tweak title.
		add_filter( 'admin_title', array( __CLASS__, 'tweak_page_title' ), 10, 2 );

		// Integrate WooCommerce navigation bar.
		add_action( 'admin_menu', array( __CLASS__, 'wc_admin_connect_gc_pages' ) );
		add_filter( 'woocommerce_navigation_pages_with_tabs', array( __CLASS__, 'wc_admin_navigation_pages_with_tabs' ) );
		add_filter( 'woocommerce_navigation_page_tab_sections', array( __CLASS__, 'wc_admin_navigation_page_tab_sections' ) );
	}

	/**
	 * Configure giftcard tabs.
	 *
	 * @param  array  $pages
	 * @return array
	 */
	public static function wc_admin_navigation_page_tab_sections( $pages ) {
		$pages[ 'prl' ] = array( 'section' );
		return $pages;
	}

	/**
	 * Configure giftcard page sections.
	 *
	 * @param  array  $pages
	 * @return array
	 */
	public static function wc_admin_navigation_pages_with_tabs( $pages ) {
		$pages[ 'prl_locations' ] = 'prl';
		return $pages;
	}

	/**
	 * Connect pages with navigation bar.
	 *
	 * @return void
	 */
	public static function wc_admin_connect_gc_pages() {

		if ( WC_PRL_Core_Compatibility::is_wc_admin_enabled() ) {

			wc_admin_connect_page(
				array(
					'id'        => 'woocommerce-product-recommendations',
					'screen_id' => 'woocommerce_page_prl_performance',
					'title'     => __( 'Recommendations', 'woocommerce-product-recommendations' ),
					'path'      => add_query_arg(
						array(
							'page' => 'prl_performance'
						),
						'admin.php'
					)
				)
			);

			wc_admin_connect_page(
				array(
					'id'        => 'woocommerce-product-locations',
					'parent'    => 'woocommerce-product-recommendations',
					'screen_id' => 'woocommerce_page_prl_locations-prl',
					'title'     => __( 'Locations', 'woocommerce-product-recommendations' ),
					'path'      => add_query_arg(
						array(
							'page' => 'prl_locations'
						),
						'admin.php'
					)
				)
			);

			if ( ! empty( $_GET[ 'location' ] ) ) {
				$location = WC_PRL()->locations->get_location( sanitize_text_field( $_GET[ 'location' ] ) );
			}

			wc_admin_connect_page(
				array(
					'id'        => 'woocommerce-product-locations-hooks',
					'parent'    => 'woocommerce-product-locations',
					'screen_id' => 'woocommerce_page_prl_locations-prl-hooks',
					'title'     => isset( $location ) ? $location->get_title()  : __( 'View Location', 'woocommerce-product-recommendations' )
				)
			);

			$posttype_list_base = 'edit.php';

			// WooCommerce > Engines.
			wc_admin_connect_page(
				array(
					'id'        => 'woocommerce-engines',
					'parent'    => 'woocommerce-product-recommendations',
					'screen_id' => 'edit-prl_engine',
					'title'     => __( 'Engines', 'woocommerce-product-recommendations' ),
					'path'      => add_query_arg( 'post_type', 'prl_engine', $posttype_list_base ),
				)
			);

			// WooCommerce > Engines > Add New.
			wc_admin_connect_page(
				array(
					'id'        => 'woocommerce-add-engine',
					'parent'    => 'woocommerce-engines',
					'screen_id' => 'prl_engine-add',
					'title'     => __( 'Add New', 'woocommerce-product-recommendations' ),
				)
			);

			// WooCommerce > Engines > Edit Engine.
			wc_admin_connect_page(
				array(
					'id'        => 'woocommerce-edit-engine',
					'parent'    => 'woocommerce-engines',
					'screen_id' => 'prl_engine',
					'title'     => __( 'Edit Engine', 'woocommerce-product-recommendations' ),
				)
			);
		}
	}

	/**
	 * Renders tabs on our custom post types pages.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public static function render_tabs() {
		$screen = get_current_screen();

		// Handle tabs on the relevant WooCommerce pages.
		if ( $screen && ! in_array( $screen->id, WC_PRL()->get_screen_ids(), true ) ) {
			return;
		}

		$tabs = array();

		if ( wc_prl_tracking_enabled() ) {
			$tabs[ 'performance' ] = array(
				'title' => __( 'Performance', 'woocommerce-product-recommendations' ),
				'url'   => admin_url( 'admin.php?page=prl_performance' ),
			);
		}

		$tabs[ 'engines' ]         = array(
			'title' => __( 'Engines', 'woocommerce-product-recommendations' ),
			'url'   => admin_url( 'edit.php?post_type=prl_engine' ),
		);

		$tabs[ 'locations' ]       = array(
			'title' => __( 'Locations', 'woocommerce-product-recommendations' ),
			'url'   => admin_url( 'admin.php?page=prl_locations' ),
		);

		if ( ! wc_prl_tracking_enabled() ) {
			$tabs = array_reverse( $tabs );
		}

		$tabs = apply_filters( 'woocommerce_prl_admin_tabs', $tabs );

		if ( is_array( $tabs ) ) {
			?>
			<div class="wrap woocommerce">
				<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
					<?php $current_tab = self::get_current_tab(); ?>
					<?php foreach ( $tabs as $tab_id => $tab ) : ?>
						<?php $class = $tab_id === $current_tab ? array( 'nav-tab', 'nav-tab-active' ) : array( 'nav-tab' ); ?>
						<?php printf( '<a href="%1$s" class="%2$s">%3$s</a>', esc_url( $tab[ 'url' ] ), implode( ' ', array_map( 'sanitize_html_class', $class ) ), esc_html( $tab[ 'title' ] ) ); ?>
					<?php endforeach; ?>
				</nav>
			</div>
			<?php
		}
	}

	/**
	 * Returns the current admin tab.
	 *
	 * @param  string  $current_tab
	 * @return string
	 */
	public static function get_current_tab( $current_tab = 'performance' ) {

		if ( $screen = get_current_screen() ) {
			if ( in_array( $screen->id, array( 'prl_engine', 'edit-prl_engine' ), true ) ) {
				$current_tab = 'engines';
			} elseif ( in_array( $screen->id, array( 'woocommerce_page_prl_locations' ), true ) ) {
				$current_tab = 'locations';
			} elseif ( in_array( $screen->id, array( 'woocommerce_page_prl_performance' ), true ) ) {
				$current_tab = 'performance';
			}
		}

		/**
		 * Filters the current Recommendations Admin tab.
		 *
		 * @param  string    $current_tab
		 * @param  WP_Screen $screen
		 */
		return (string) apply_filters( 'woocommerce_prl_admin_current_tab', $current_tab, $screen );
	}

	/**
	 * Fix the active menu item when editing/creating an Engine.
	 */
	public static function prl_fix_menu_highlight() {
		global $parent_file, $submenu_file;

		if ( WC_PRL()->is_current_screen() ) {
			$submenu_file = wc_prl_tracking_enabled() ? 'prl_performance' : 'prl_locations';
			$parent_file  = 'woocommerce';
		}

		return $parent_file;
	}

	/**
	 * Removes multiple submenu links for Recommendations that are not being used as a menu item.
	 */
	public static function prl_remove_submenu_link() {

		$submenu_slugs = array();

		if ( wc_prl_tracking_enabled() ) {
			$submenu_slugs[] = 'prl_locations';
		}

		foreach ( $submenu_slugs as $slug ) {
			remove_submenu_page( 'woocommerce', $slug );
		}
	}

	/**
	 * Add menu items.
	 */
	public static function prl_menu() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return false;
		}

		if ( wc_prl_tracking_enabled() ) {
			add_submenu_page( 'woocommerce', __( 'Performance', 'woocommerce-product-recommendations' ),  __( 'Recommendations', 'woocommerce-product-recommendations' ), 'manage_woocommerce', 'prl_performance', array( __CLASS__, 'performance_page' ) );
		}

		$locations_page = add_submenu_page( 'woocommerce', __( 'Locations', 'woocommerce-product-recommendations' ), wc_prl_tracking_enabled() ? null : __( 'Recommendations', 'woocommerce-product-recommendations' ), 'manage_woocommerce', 'prl_locations', array( __CLASS__, 'locations_page' ) );

		add_action( 'load-' . $locations_page, array( __CLASS__, 'locations_page_init' ) );
	}

	/**
	 * Init admin locations page. Setups the `save` feature and adds messages.
	 */
	public static function locations_page_init() {

		if ( ! empty( $_POST ) ) {

			$section = 'locations_overview';

			if ( isset( $_GET[ 'section' ] ) ) {
				$section = wc_clean( $_GET[ 'section' ] );
			}

			switch ( $section ) {
				case 'deploy':
					WC_PRL_Admin_Deploy::save();
					break;
				case 'hooks':
					WC_PRL_Admin_Hooks::save();
					break;
				case 'locations_overview':
					WC_PRL_Admin_Locations::save();
					break;
			}
		}

		do_action( 'woocommerce_prl_locations_page_init' );
	}

	/**
	 * Render "Performance" page.
	 */
	public static function performance_page() {
		WC_PRL_Admin_Performance::output();
	}

	/**
	 * Render "Locations" page.
	 */
	public static function locations_page() {

		$section = 'locations_overview';

		if ( isset( $_GET[ 'section' ] ) ) {
			$section = wc_clean( $_GET[ 'section' ] );
		}

		switch ( $section ) {
			case 'deploy':
				WC_PRL_Admin_Deploy::output();
				break;
			case 'hooks':
				WC_PRL_Admin_Hooks::output();
				break;
			case 'locations_overview':
				WC_PRL_Admin_Locations::output();
				break;
		}
	}

	/**
	 * Render "Deploy" page.
	 */
	public static function deploy_page() {
		WC_PRL_Admin_Deploy::output();
	}

	/**
	 * Render "Hooks" page.
	 */
	public static function hooks_page() {
		WC_PRL_Admin_Hooks::output();
	}

	/**
	 * Changes the admin title based on the section.
	 */
	public static function tweak_page_title( $admin_title, $title ) {

		$screen = get_current_screen();

		if ( $screen && 'woocommerce_page_prl_locations' === $screen->id ) {

			// Fix the main title issue caused by the remove_submenu_page.
			$title = __( 'Locations', 'woocommerce-product-recommendations' );
			if ( wc_prl_tracking_enabled() ) {
				$admin_title = $title . $admin_title;
			}

			if ( ! isset( $_GET[ 'section' ] ) ) {
				return $admin_title;
			}

			$section = wc_clean( $_GET[ 'section' ] );
			switch ( $section ) {
				case 'deploy':
					$admin_title = str_replace( $title, __( 'Deploy engine', 'woocommerce-product-recommendations' ), $admin_title );
					break;
			}
		}

		return $admin_title;
	}
}

WC_PRL_Admin_Menus::init();
