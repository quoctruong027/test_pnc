<?php
/**
 * Smart Offers Admin
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.1.1
 * @package     Smart Offers
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'SO_Admin_Offers' ) ) {

	Class SO_Admin_Offers {

		var $order_data = array();

		function __construct() {
			add_action( 'manage_smart_offers_posts_custom_column', array( $this, 'so_custom_columns' ), 2 );
			add_action( 'admin_action_duplicate_offer', array( $this, 'so_duplicate_offer' ) );
			add_action( 'restrict_manage_posts', array( $this, 'so_restrict_manage_smart_offers' ), 20 );
			add_action( 'admin_init', array( $this, 'so_reset_stats' ) );
			add_action( 'admin_notices', array( $this, 'so_reset_success_message' ) );
			add_action( 'load-edit.php', array( $this, 'so_edit_load' ) );

			add_action( 'admin_menu', array( $this, 'so_admin_menus') );
			add_action( 'admin_head', array( $this, 'smart_offers_admin_head') );

			add_action( 'admin_init', array( $this, 'set_order_data' ) );

			add_action( 'admin_init', array( $this, 'so_check_hook_exists' ) );
			add_action( 'admin_notices', array( $this,'so_show_theme_notice' ) );

			add_filter( 'post_row_actions', array( $this, 'so_remove_add_custom_links' ), 1, 2 );
			add_filter( 'manage_edit-smart_offers_columns', array( $this, 'so_edit_columns' ) );
			add_filter( 'manage_edit-smart_offers_sortable_columns', array( $this, 'so_sortable_columns' ) );
			add_filter( 'views_edit-smart_offers', array( $this, 'admin_new_button' ) );

			add_action( 'admin_print_scripts-edit.php', array( $this, 'smart_offers_admin_scripts' ) );
			add_action( 'bulk_edit_custom_box', array( $this, 'display_smart_offers_quick_bulk_edit_option' ), 10, 2 );
			add_action( 'quick_edit_custom_box', array( $this, 'display_smart_offers_quick_bulk_edit_option' ), 10, 2 );
			add_action( 'save_post', array( $this, 'process_quick_edit_smart_offers' ), 10, 2 );
			add_action( 'wp_ajax_process_bulk_edit_smart_offers', array( $this, 'process_bulk_edit_smart_offers' ) );
			add_action( 'wp_ajax_generate_embed_offer', array( $this, 'smart_offers_generate_embed_offer' ) );

			// Filters to modify SO menu position
			add_filter( 'custom_menu_order', '__return_true' );
			add_filter( 'menu_order', array( $this, 'so_menu_order' ) );
		}

		public function so_admin_menus() {

			global $menu;

			// Add a separator for SO
			$menu[] = [ '', 'read', 'separator-smart-offers', '', 'wp-menu-separator smart-offers' ];

			add_submenu_page( 'edit.php?post_type=smart_offers', __( 'Setting & Styles', 'smart-offers' ),  __( 'Setting & Styles', 'smart-offers' ), 'manage_options', 'so-settings', array( 'SO_Admin_Offers', 'so_settings_page' ) );
			add_submenu_page( 'edit.php?post_type=smart_offers', __( 'Docs & Support', 'smart-offers' ),  __( 'Docs & Support', 'smart-offers' ), 'manage_options', 'so-about', array( $GLOBALS['sa_so_admin_welcome'], 'show_welcome_page' ) );
			add_submenu_page( 'edit.php?post_type=smart_offers', __( 'Shortcode', 'smart-offers' ),  __( 'Shortcode', 'smart-offers' ), 'manage_options', 'so-shortcode', array( $GLOBALS['sa_so_admin_welcome'], 'show_welcome_page' ) );
			add_submenu_page( 'edit.php?post_type=smart_offers', __( 'FAQ\'s', 'smart-offers' ),  __( 'FAQ\'s', 'smart-offers' ), 'manage_options', 'so-faqs', array( $GLOBALS['sa_so_admin_welcome'], 'show_welcome_page' ) );

		}

		/**
		 * Reorder the Smart Offers menu items in admin.
		 * Based on WC.
		 *
		 * @since 3.6.0
		 *
		 * @param array $menu_order Menu order.
		 * @return array
		 */
		public function so_menu_order( $menu_order ) {
			// Initialize our custom order array.
			$smart_offers_menu_order = array();

			// Get the index of our custom separator.
			$smart_offers_separator = array_search( 'separator-smart-offers', $menu_order, true );

			// Get index of library menu.
			$smart_offers_post_type = array_search( 'edit.php?post_type=smart_offers', $menu_order, true );

			// Loop through menu order and do some rearranging.
			foreach ( $menu_order as $index => $item ) {
				if ( 'edit.php?post_type=smart_offers' === $item ) {
					$smart_offers_menu_order[] = 'separator-smart-offers';
					$smart_offers_menu_order[] = $item;
					$smart_offers_menu_order[] = 'edit.php?post_type=smart_offers';

					unset( $menu_order[ $smart_offers_separator ] );
					unset( $menu_order[ $smart_offers_post_type ] );
				} elseif ( ! in_array( $item, [ 'separator-smart-offers' ], true ) ) {
					$smart_offers_menu_order[] = $item;
				}
			}

			// Return order.
			return $smart_offers_menu_order;
		}

		public function smart_offers_admin_head() {
			remove_submenu_page( 'edit.php?post_type=smart_offers', 'post-new.php?post_type=smart_offers' );
			remove_submenu_page( 'edit.php?post_type=smart_offers', 'so-shortcode' );
			remove_submenu_page( 'edit.php?post_type=smart_offers', 'so-faqs' );
		}

		public static function so_settings_page() {
			include ( 'class-so-admin-setting.php' );
		}

		/**
		 * Define SO custom columns shown in admin.
		 * @param  string $column
		 */
		function so_custom_columns( $columns ) {

			global $post, $sa_smart_offers;

			$order_data = $this->order_data;

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );

			$so_accept_skip_counter = get_post_meta($offer_id, 'so_accept_skip_counter', true);

			$offered_products = (get_post_meta($offer_id, 'target_product_ids', true)) ? explode(',', get_post_meta($offer_id, 'target_product_ids', true)) : array();

			$actions_on_accept_offer = get_post_meta( $offer_id, 'so_actions_on_accept', true );
			$upsell_offer_ids = '';
			if ( is_array( $actions_on_accept_offer ) && array_key_exists( 'accepted_offer_ids' , $actions_on_accept_offer ) ) {
				$upsell_offer_ids = $actions_on_accept_offer['accepted_offer_ids'];
			}

			$actions_on_skip_offer = get_post_meta( $offer_id, 'sa_smart_offer_if_denied', true );
			$downsell_offer_ids = '';
			if ( $actions_on_skip_offer == 'offer_page' ) {
				$downsell_offer_ids = get_post_meta( $offer_id, 'url', true );
			}

			$offer_seen = (isset($so_accept_skip_counter ['offer_shown'])) ? $so_accept_skip_counter ['offer_shown'] : 0;
			$accepted = (isset($so_accept_skip_counter ['accepted'])) ? $so_accept_skip_counter ['accepted'] : 0;
			$skipped = (isset($so_accept_skip_counter ['skipped'])) ? $so_accept_skip_counter ['skipped'] : 0;
			$count_of_orders = get_post_meta($offer_id, 'so_order_count', true);
			$count_of_orders_having_offers = is_array( $count_of_orders ) && array_key_exists( 'order_count', $count_of_orders ) ? $count_of_orders['order_count'] : $count_of_orders;

			$conversion_rate = '';
			if ( is_numeric($offer_seen) && is_numeric($count_of_orders_having_offers) ) {
				$conversion_rate = ($offer_seen != 0) ? ($count_of_orders_having_offers / $offer_seen) * 100 : 0;
			}

			update_post_meta( $offer_id, 'so_conversion_rate', wc_format_decimal($conversion_rate, get_option( 'woocommerce_price_num_decimals' ), $trim_zeros = false) );

			$offer_type = get_post_meta( $offer_id, 'so_offer_type', true );

			switch ($columns) {
				case "offered_products" :
					if ( sizeof($offered_products) > 0 ) {
						$product = wc_get_product(implode(', ', $offered_products));
						if( !( $product instanceof WC_Product ) ) {
							break;
						}

						$title = $product->get_formatted_name();
						$image = $product->get_image( array( 50, 50 ) );
						$product_id = $product->get_id();

						echo $image . '&nbsp;<p class="offered_products_name product_' . $product_id . '">' . $title . '</p>';
					} else {
						echo '&ndash;';
					}
					break;
				case "offer_type" :
					if ( !empty( $offer_type ) && 'order_bump' === $offer_type ) {
						echo __( 'Order Bump', 'smart-offers' );
					} else {
						echo '&ndash;';
					}
					break;
				case "upsell_offers" :
					if ( !empty( $upsell_offer_ids ) ) {
						if ( strpos( $upsell_offer_ids, ',' ) ) {
							$offer_ids = explode( ",", $upsell_offer_ids );
							foreach ($offer_ids as $key => $value) {
								$value = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $value );
								echo get_the_title( $value ) . '<br>';
							}
						} else {
							$upsell_offer_ids = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $upsell_offer_ids );
							echo get_the_title( $upsell_offer_ids );
						}
					} else {
						echo '&ndash;';
					}
					break;
				case "downsell_offers" :
					if ( !empty( $downsell_offer_ids ) ) {
						if ( strpos( $downsell_offer_ids, ',' ) ) {
							$offer_ids = explode( ",", $downsell_offer_ids );
							foreach ($offer_ids as $key => $value) {
								$value = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $value );
								echo get_the_title( $value ) . '<br>';
							}
						} else {
							$downsell_offer_ids = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $downsell_offer_ids );
							echo get_the_title( $downsell_offer_ids );
						}
					} else {
						echo '&ndash;';
					}
					break;
				case "quick_stats" :
					echo sprintf(__( '%s Seen: %d', 'smart-offers' ), '<span class="dashicons dashicons-visibility"></span>', $offer_seen ) . '<br/>'
							. sprintf(__( '%s Skipped: %d', 'smart-offers' ), '<span class="dashicons dashicons-thumbs-down"></span>', $skipped ) . '<br/>'
							. sprintf(__( '%s Accepted: %d', 'smart-offers' ), '<span class="dashicons dashicons-thumbs-up"></span>', $accepted ) . '<br/>'
							. sprintf(__( '%s Paid: %d', 'smart-offers' ), '<span class="dashicons dashicons-awards"></span>', $count_of_orders_having_offers );
					break;
				case "conversion_rate" :
					if ( !empty( $conversion_rate ) ) {
						echo wc_format_decimal($conversion_rate, get_option( 'woocommerce_price_num_decimals' ), $trim_zeros = false) . '%';
					} else {
						echo '&ndash;';
					}
					break;
				case "earnings" :
					if ( !empty( $order_data[ $offer_id ]['earnings'] ) ) {
						echo '<strong>' . wc_price( $order_data[ $offer_id ]['earnings'] ) . '</strong>';
					} else {
						echo '&ndash;';
					}
					break;
			}
		}

		/**
		 * Duplicate a offer action.
		 */
		function so_duplicate_offer() {

			if ( !( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && 'duplicate_post_save_as_new_page' == $_REQUEST['action'] ) ) ) {
				wp_die( __( 'No offer to duplicate has been supplied!', 'smart-offers' ) );
			}

			// Get the original page
			$id = ( isset($_GET['post']) ? $_GET['post'] : $_POST['post'] );

			$id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $id );

			check_admin_referer('woocommerce-duplicate-offer_' . $id);
			$post = $this->sa_smart_offers_get_offer_to_duplicate($id);

			if (isset($post) && $post != null) {
				$new_id = $this->sa_smart_offers_duplicate_from_offer($post);

				// If you have written a plugin which uses non-WP database tables to save
				// information about a page you can hook this action to dupe that data.
				do_action( 'woocommerce_duplicate_offer', $new_id, $post );

				// Redirect to the edit screen for the new draft page
				wp_safe_redirect(admin_url('post.php?action=edit&post=' . $new_id));
				exit;
			} else {
				wp_die( __( 'Offer creation failed, could not find original product:', 'smart-offers' ) . ' ' . $id );
			}
		}

		/**
		 * Get a offer from the database to duplicate
		 *
		 * @access public
		 * @param mixed $id
		 * @return WP_Post|bool
		 * @see duplicate_product
		 */
		function sa_smart_offers_get_offer_to_duplicate( $id ) {
			global $wpdb;

			$smart_offers_args = array(
										'p' => $id,
										'post_type' => 'smart_offers',
										'nopaging' => true,
										'post_status' => array( 'publish', 'draft', 'pending' )
									);
			$smart_offers_query_results = new WP_Query( $smart_offers_args );

			if ( $smart_offers_query_results->post_count > 0 ) {
				return $smart_offers_query_results->post;
			}

			return null;

		}

		/**
		 * Function to create the duplicate of the offer.
		 *
		 * @access public
		 * @param mixed $post
		 * @param int $parent (default: 0)
		 * @param string $post_status (default: '')
		 * @return int
		 */
		function sa_smart_offers_duplicate_from_offer( $post, $parent = 0, $post_status = '' ) {
			global $wpdb;

			$new_post_author = wp_get_current_user();
			$new_post_date = current_time('mysql');
			$new_post_date_gmt = get_gmt_from_date($new_post_date);

			if ($parent > 0) {
				$post_parent = $parent;
				$post_status = $post_status ? $post_status : 'publish';
				$suffix = '';
			} else {
				$post_parent = $post->post_parent;
				$post_status = $post_status ? $post_status : 'draft';
				$suffix = __("(Duplicate)", 'smart-offers');
			}

			$new_post_type = $post->post_type;
			$post_content = str_replace("'", "''", $post->post_content);
			$post_content_filtered = str_replace("'", "''", $post->post_content_filtered);
			$post_excerpt = str_replace("'", "''", $post->post_excerpt);
			$post_title = str_replace("'", "''", $post->post_title) . $suffix;
			$post_name = str_replace("'", "''", $post->post_name);
			$comment_status = str_replace("'", "''", $post->comment_status);
			$ping_status = str_replace("'", "''", $post->ping_status);

			// Insert the new template in the post table
			$wpdb->insert(
							$wpdb->posts,
							array(
									'post_author'               => $new_post_author->ID,
									'post_date'                 => $new_post_date,
									'post_date_gmt'             => $new_post_date_gmt,
									'post_content'              => $post_content,
									'post_content_filtered'     => $post_content_filtered,
									'post_title'                => $post_title,
									'post_excerpt'              => $post_excerpt,
									'post_status'               => $post_status,
									'post_type'                 => $new_post_type,
									'comment_status'            => $comment_status,
									'ping_status'               => $ping_status,
									'post_password'             => $post->post_password,
									'to_ping'                   => $post->to_ping,
									'pinged'                    => $post->pinged,
									'post_modified'             => $new_post_date,
									'post_modified_gmt'         => $new_post_date_gmt,
									'post_parent'               => $post_parent,
									'menu_order'                => $post->menu_order,
									'post_mime_type'            => $post->post_mime_type
								)
						);

			$new_post_id = $wpdb->insert_id;

			// Copy the meta information
			$this->sa_smart_offers_duplicate_offer_post_meta($post->ID, $new_post_id);	// Not running $post->ID through WPML offer_id

			return $new_post_id;
		}

		/**
		 * Copy the meta information of a post to another post
		 *
		 * @access public
		 * @param mixed $id
		 * @param mixed $new_id
		 * @return void
		 */
		function sa_smart_offers_duplicate_offer_post_meta($id, $new_id) {
			global $wpdb;

			$post_meta_infos = get_post_meta( $id );

			if ( count( $post_meta_infos ) > 0 ) {
				foreach ( $post_meta_infos as $meta_key => $meta_value ) {

					if ( $meta_key == "so_order_count" || $meta_key == "so_conversion_rate" || $meta_key == "so_accept_skip_counter" )
						continue;
					add_post_meta( $new_id, $meta_key, maybe_unserialize( $meta_value[0] ) );
				}
			}
		}

		/**
		 * Generate embed offer
		 */
		function smart_offers_generate_embed_offer() {
			global $sa_smart_offers;

			check_ajax_referer('so_generate_embed_offer', 'security');

			if(empty($_POST['post_id']) || $_POST['action'] != 'generate_embed_offer') return;

			$so_offer = new SO_Offer();

			$data = array();
			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $_POST['post_id'] );
			$where_url = get_option('siteurl');

			$offer_content = $so_offer->return_post_content($offer_id, $page = '', $where_url);
			$data['content'] = apply_filters('the_content', $offer_content);
			$button_style = get_option('so_accept_button_styles');

			if ( $button_style == 'smart_offers_custom_style_button' ) {
				$accept_css = get_option('so_css_for_accept');
				$style_for_accept_text = "";
			} else {
				$accept_css = get_option( $button_style );
				$style_for_accept_text = "div.so_accept a { text-decoration: none !important; color: white; }";
			}

			$skip_css = get_option('so_css_for_skip');
			$style_for_accept = "div.so_accept { $accept_css }";
			$style_for_skip = "div.so_skip { $skip_css }";

			$data['style'] = $style_for_accept . $style_for_skip . $style_for_accept_text;

			echo json_encode( $data );
			die();
		}

		/**
		 * Show reset quick stats button in admin
		 */
		function so_restrict_manage_smart_offers() {
			global $typenow, $pagenow;

			if ( $typenow != 'smart_offers' )     // show reset offers button only when post type is smart offers
				return;

			/* TO show Reset Quick Stats button on Smart Offers page */
			?>
			<script type="text/javascript">
				jQuery(document).on('click', 'input#reset_stats', function(e) {
					var answer = confirm("<?php _e('Are you sure you want reset Quick Stats of Smart Offers?? It will clear data from Quick Stats and Conversion rate column & also Smart Offers widget on WordPress dashboard.'); ?>");
					if (answer == false) {
						e.preventDefault();
					}
				});
			</script>
			<div class="alignright" style="margin-top: 1px;" >
				<input type="submit" name="reset_stats" id="reset_stats" class="button action" value="<?php _e('Reset All Quick Stats', 'smart-offers'); ?>" >
			</div>
			<?php
		}

		/**
		 * Action to reset the statistics
		 */
		function so_reset_stats() {
			global $wpdb, $typenow;

			if ( isset( $_GET['reset_stats'] ) ) {
				$all = true;
				$this->reset_quick_stats( array(), $all );
			}

			if ( isset($_GET['so-theme-notice']) ) {

				$dismiss_theme_notice = false;

				if ($_GET['so-theme-notice'] == 'add_shortcode') {

					$page_ids = array( 'Cart' => wc_get_page_id('cart'),
						'Checkout' => wc_get_page_id('checkout'),
						'Order Received' => wc_get_page_id('thanks'),		// might need to change
						'My Account' => wc_get_page_id('myaccount') );

					$add_shortcode = get_option('so_theme_compatibility');

					if (!empty($add_shortcode)) {

						foreach ($add_shortcode as $page_name => $page_value) {

							if ($page_value == true) {

								$page_id = $page_ids[$page_name];
								if ($page_id) {
									$page = get_post($page_id);
									$page_content = $page->post_content;
									$page_content = "[so_show_offers]" . $page_content;
//                                                      update
									$my_post = array();
									$my_post['ID'] = $page_id;
									$my_post['post_content'] = $page_content;
									wp_update_post($my_post);
								}
							}
						}
						$dismiss_theme_notice = true;
					}
				} elseif ( $_GET['so-theme-notice'] == 'dismiss_theme_notice' ) {
					$dismiss_theme_notice = true;
				}

				if ( $dismiss_theme_notice == true ) {
					update_option( 'so_theme_notice', 'no', 'no' );
				}
			}
		}

		/**
		 * Show admin messages
		 */
		function so_reset_success_message() {
			global $typenow, $pagenow, $post;

			if ( !isset( $_GET['so_reset_stats'] ) && !isset( $_GET['show_sc_msg'] ) )
				return;

			if ( isset( $_GET['so_reset_stats'] ) && $_GET['so_reset_stats'] == "success" ) {
				if ( 'edit.php' == $pagenow && 'smart_offers' == $typenow ) {

					echo '<div id="message" class="updated fade"><p>
												' . sprintf( __( 'Smart Offers Statistics have been reset successfully', 'smart-offers' ) ) . '
										</p></div>';
				}
			}

			if ( isset($_GET['show_sc_msg']) && $_GET['show_sc_msg'] == true ) {

				$offer_id = is_object( $post ) ? apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID ) : 0;
				$so_offer_type = '';

				if( !empty( $offer_id ) ) {
					$so_offer_type = SO_Admin_Offer::get_offer_type( $offer_id );
				}

				if ( 'post.php' == $pagenow && 'smart_offers' == $typenow && 'order_bump' !== $so_offer_type ) {

					echo '<div class="updated fade"><p>' . sprintf( __( 'Shortcode to show Product Variations is added in the Offer Description.', 'smart-offers' ) ) . '</p></div>' . "\n";
				}
			}
		}

		/**
		 * Change the columns shown in admin.
		 */
		function so_edit_columns($columns) {

			$columns = array();

			$columns ["cb"] = "<input type=\"checkbox\" />";
			$columns ["title"] = __( 'Offer Title', 'smart-offers' );
			$columns ["offered_products"] = __( 'Product', 'smart-offers' );
			$columns ["offer_type"] = __( 'Offer Type', 'smart-offers' );
			$columns ["upsell_offers"] = __( 'Upsell Offers', 'smart-offers' );
			$columns ["downsell_offers"] = __( 'Downsell Offers', 'smart-offers' );
			$columns ["quick_stats"] = __( 'Quick Stats', 'smart-offers' );
			$columns ["conversion_rate"] = __( 'Conversion Rate', 'smart-offers' );
			$columns ["earnings"] = __( 'Earnings', 'smart-offers' );

			return $columns;
		}

		/**
		 * Make SO columns sortable
		 */
		function so_sortable_columns($columns) {
			$columns ["conversion_rate"] = "conversion_rate";
			$columns ["offered_products"] = "offered_products";
			$columns ["earnings"] = "earnings";
			return $columns;
		}

		/**
		 * Sort Offers orderby
		 */
		function so_edit_load() {
			add_filter( 'request', array( $this, 'so_sort_converion_rate') );
			add_filter( 'request', array( $this, 'so_sort_offered_products') );
		}

		/**
		 * Sort offers based on product
		 */
		function so_sort_offered_products($vars) {
			global $wp, $wp_query;

			/* Check if we're viewing the 'smart_offers' post type. */
			if (isset($vars ['post_type']) && 'smart_offers' == $vars ['post_type']) {

				/* Check if 'orderby' is set to 'offered_products'. */
				if (isset($vars ['orderby']) && 'offered_products' == $vars ['orderby']) {

					/* Merge the query vars with our custom variables. */
					$vars = array_merge($vars, array('meta_key' => 'target_product_ids'));
				}
			}

			return $vars;
		}

		/**
		 * Sort offers based on conversion rate
		 */
		function so_sort_converion_rate($vars) {
			global $wp, $wp_query;

			/* Check if we're viewing the 'smart_offers' post type. */
			if (isset($vars ['post_type']) && 'smart_offers' == $vars ['post_type']) {

				/* Check if 'orderby' is set to 'conversion_rate'. */
				if (isset($vars ['orderby']) && 'conversion_rate' == $vars ['orderby']) {

					/* Merge the query vars with our custom variables. */
					$vars = array_merge($vars, array('meta_key' => 'so_conversion_rate', 'orderby' => 'meta_value_num'));
				}
			}

			return $vars;
		}

		/**
		 * Add additional admin buttons in SO
		 */
		function admin_new_button($views) {
			global $post_type;

			if ( isset($post_type) && $post_type == "smart_offers" ) {
				$views['smart_offers_welcome'] = '<a target="_blank" href=' . admin_url('edit.php?post_type=smart_offers&page=so-shortcode') . '>' . __( 'Available Shortcodes', 'smart-offers' ) . '</a>';
				$views['smart_offers_support'] = '<a target="_blank" href=' . admin_url('edit.php?post_type=smart_offers&page=so-faqs') . '>' . __( "FAQ's", 'smart-offers' ) . '</a>';
				$views['smart_offers_docs'] = '<a href="https://www.storeapps.org/knowledgebase_category/smart-offers/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs" title="' . __( 'Documentation', 'smart-offers' ) . '" target="_blank">' . __( 'Docs', 'smart-offers' ) . '</a>';
			}

			return apply_filters( 'smart_offers_views', $views );
		}

		/**
		 * set order data if not already set
		 */
		function set_order_data() {
			if ( empty( $this->order_data ) ) {
				$this->order_data = $this->get_order_data();
			}
		}

		/**
		 * Check whether current theme is compatible fully with WC
		 */
		function so_check_hook_exists() {

			$template_compatibility_option = get_option( 'so_theme_compatibility' );

			if( empty( $template_compatibility_option ) ) {

				$found_files = $add_shortcode_to_template = array();

				$files_path = array( 
									'Cart' 				=> 'cart/cart.php',
									'Checkout' 			=> 'checkout/form-checkout.php',
									'Order Received' 	=> 'checkout/thankyou.php',
									'My Account' 		=> 'myaccount/my-account.php'
									);

				foreach ( $files_path as $key => $file ) {

					if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
						$found_files[$key] = '/' . $file ;
					} elseif( file_exists( get_stylesheet_directory() . '/woocommerce/' . $file ) ) {
						$found_files[$key] = '/woocommerce/' . $file ;
					}
				}

				if( !empty( $found_files ) ) {

					foreach( $found_files as $page_nm => $file_path ){

						$handle = fopen(get_stylesheet_directory() . $file_path, 'r');

						$file_content = nl2br(htmlentities(file_get_contents( get_stylesheet_directory() . $file_path )));

						if( $page_nm == 'Cart' ) {
							$search_string = 'woocommerce_before_cart';
						} elseif( $page_nm == 'Checkout' ) {
							$search_string = 'woocommerce_before_checkout_form';
						} elseif( $page_nm == 'Order Received' ) {
							$search_string = 'woocommerce_thankyou';
						} elseif( $page_nm == 'My Account' ) {
							$search_string = 'woocommerce_before_my_account';
						}

						$add_shortcode = false;

						preg_match('/\\b'.$search_string.'\\b/', $file_content, $str_matches);

						if( empty( $str_matches ) ){
							$add_shortcode = true;
						} else {

							preg_match_all('/(\/\*).*?(\*\/)|(\/\/).*?(\n)/s', $file_content, $comment_matches);
							$prg = $comment_matches[0];
							$prg_str = implode( ',', $prg);

							if( preg_match('/\\b'.$search_string.'\\b/', $prg_str, $str_comment_match )){

								if( ! empty( $str_comment_match ) ) {
									$add_shortcode = true;
								}
							}
						}

						if( $add_shortcode == true ) {
							$add_shortcode_to_template[$page_nm]  = true;
						}
					}

					if( !empty( $add_shortcode_to_template ) ) {
						update_option( 'so_theme_compatibility', $add_shortcode_to_template );
						update_option( 'so_theme_notice', 'yes', 'no' );
					}
				}
			}
		}

		/**
		 * Show theme incompatibility message
		 */
		function so_show_theme_notice() {

			global $typenow, $pagenow, $post;

			$theme_compatibility = get_option( 'so_theme_compatibility' );

			if( ! empty( $theme_compatibility ) && get_option( 'so_theme_notice' ) == "yes" ) {

				if( 'smart_offers' == $typenow && ( 'post.php' == $pagenow || 'edit.php' == $pagenow ) ) {

					$pages = implode( ', ', array_keys( $theme_compatibility ) );
					?>
					<div id="message" class="updated">
						<div class="squeezer">
							<p style="font-weight: bold;"><?php _e( 'Your current theme is not compatible with Smart Offers.', 'smart-offers' ); ?></p>
							<p style="font-weight: bold;"><?php _e( 'We would need to add Smart Offers Shortcode to the following page/pages: ' . $pages . '.', 'smart-offers' ); ?></p>
							<p><a href="<?php echo esc_url( add_query_arg( 'so-theme-notice', 'add_shortcode' ) ) ;?>" class="wc-update-now button-primary"><?php _e( 'Fix this automatically', 'smart-offers' ); ?></a> <a href="<?php echo esc_url( add_query_arg( 'so-theme-notice', 'dismiss_theme_notice' ) ) ;?>" class="wc-update-now button-primary"><?php _e( 'Dismiss this notice', 'smart-offers' ); ?></a> <a href="https://www.storeapps.org/docs/so-how-to-check-if-smart-offers-is-compatible-with-my-theme/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_theme_compatible_page" target="_blank" class="wc-update-now button-primary"><?php _e( 'Take to me the Documentation', 'smart-offers' ); ?></a> </p>
						</div>
					</div>
					<?php

				}
			}

		}

		/**
		 * Remove View link & add additional links on Smart Offers Dashboard
		 */
		function so_remove_add_custom_links( $actions, $post ) {
			if ( $post->post_type != 'smart_offers' ) {
				return $actions;
			}

			if ( isset( $actions['view'] ) ) {
				unset( $actions['view'] );
			}

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );

			// Offer ID at the start
			$actions = array_merge( array( 'id' => sprintf( __( 'ID: %d', 'smart-offers' ), $offer_id ) ), $actions );

			// Preview link on SO dashboard
			$query_args = array();
			if ( $post->post_status == 'publish' ) {
				$query_args['preview_id'] = $offer_id;
			}
			$nonce = wp_create_nonce( 'post_preview_' . $offer_id );
			$query_args['preview_nonce'] = $nonce;
			$actions['so_preview'] = '<a href="' . get_preview_post_link( $offer_id, $query_args ) . '" title="' . __( 'Preview offer', 'smart-offers' ) . '" rel="permalink" target="_blank">' . __( 'Preview', 'smart-offers' ) . '</a>';

			$actions['duplicate'] = '<a href="' . wp_nonce_url( admin_url( 'admin.php?action=duplicate_offer&amp;post=' . $offer_id ), 'woocommerce-duplicate-offer_' . $offer_id ) . '" title="' . __( 'Duplicate offer', 'smart-offers' ) . '" rel="permalink">' .  __( 'Duplicate', 'smart-offers' ) . '</a>';

			$actions['so_embed'] = '<a id="so_embed_offer_'. $offer_id . '" href="#" title="' . __( "Get embed code", 'smart-offers' ) . '" rel="permalink">' .  __( "HTML", 'smart-offers' ) . '</a>';

			return $actions;
		}

		/**
		 * Enqueue smart offers admin scripts
		 * Javascript to generate HTML content of offer using ajax
		 */
		function smart_offers_admin_scripts() {
			if ( ( isset( $_GET['page'] ) && 'smart_offers' == $_GET['page'] ) || ( isset( $_GET['post_type'] ) && 'smart_offers' == $_GET['post_type'] ) ) {
				$plugin_data = SA_Smart_Offers::get_smart_offers_plugin_data();
				$version = $plugin_data['Version'];
				if ( !wp_script_is('jquery') ) {
					wp_enqueue_script('jquery');
					wp_enqueue_style('jquery');
				}

				if ( !wp_script_is( 'so_magnific_popup_js' ) ) {
					wp_enqueue_script( 'so_magnific_popup_js', plugins_url('smart-offers/assets/js/jquery.magnific-popup.js'), array(), $version );
				}

				if ( !wp_style_is( 'so_magnific_popup_css' ) ) {
					wp_enqueue_style( 'so_magnific_popup_css', plugins_url('smart-offers/assets/css/magnific-popup.css'), array(), $version );
				}

				// ========================= Code to generate HTML content of offer ==========================

				$js = "jQuery('[id^=so_embed_offer_]').click(function(){
							var offer_id = this.id.substr(15);

							jQuery.ajax({
								type: 'POST',
								url: '" . admin_url('admin-ajax.php') . "',
								dataType: 'json',
								data: {
									action: 'generate_embed_offer',
									post_id: offer_id,
									security: '" . wp_create_nonce('so_generate_embed_offer') . "'
								},
								success: function( response ) {

									var content = response.content.replace('display:none','display:block');

									jQuery.magnificPopup.open({
										items: {
												src: '<div class =\"embed_offer\" ><h3 class=\"embed_head\" >" . __( 'Embed code for this offer: ', 'smart-offers' ) . "</h3><label><small>" . __( 'copy following HTML code and paste it on email or your website or however as you like.', 'smart-offers' ) . "</small></label><hr><textarea class =\"embed_text\" name =\"content\" id =\"so_offer_content\" >' + '<style>' + response.style + '</style>'+ content + '</textarea></div></div>',
												type: 'inline'
											},
										closeBtnInside: true,
										closeOnBgClick: true,
										showCloseBtn: true,
										tError: '". __( 'The content could not be loaded.' ,  'smart-offers' ) . "'
									});
								}
							});
						});";

				wc_enqueue_js( $js );
			}
		}

		/**
		 * Function to display quick edit & bulk edit option for Smart Offers
		 */
		function display_smart_offers_quick_bulk_edit_option( $column_name, $post_type ) {

			if ( empty( $post_type ) || $post_type != 'smart_offers' || $column_name != 'quick_stats' ) return;

			wp_nonce_field( 'smart_offers_quick_bulk_edit', 'smart_offers_quick_bulk_edit_nonce' );

			?>
			<fieldset class="inline-edit-col-right inline-edit-<?php echo $post_type; ?>">
				<div class="inline-edit-col column-<?php echo $column_name; ?>">
					<div class="inline-edit-group">
						<label class="inline-edit-status alignleft" for="so_reset_quick_stats">
							<span class="title"></span>
							<input type="checkbox" name="so_reset_quick_stats" id="so_reset_quick_stats" value="yes" />
							<?php echo __( 'Reset Quick Stats?', 'smart-offers' ); ?>
						</label>
					</div>
				</div>
			</fieldset>
			<?php

		}

		/**
		 * Function to handle quick edit action for smart offers
		 */
		function process_quick_edit_smart_offers( $post_id = 0, $post = null ) {

			if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) return;
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
			if ( is_int( wp_is_post_revision( $post ) ) ) return;
			if ( is_int( wp_is_post_autosave( $post ) ) ) return;
			if ( !empty( $_POST['smart_offers_quick_bulk_edit_nonce'] ) && !wp_verify_nonce( $_POST['smart_offers_quick_bulk_edit_nonce'], 'smart_offers_quick_bulk_edit' ) ) return;
			if ( !current_user_can( 'edit_post', $post_id ) ) return;
			if ( $post->post_type != 'smart_offers' ) return;

			if ( isset( $_POST['so_reset_quick_stats'] ) && $_POST['so_reset_quick_stats'] == 'yes' ) {
				$post_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post_id );
				$this->reset_quick_stats( $post_id );
			}

		}

		/**
		 * Function to handle bulk edit action for smart offers
		 */
		function process_bulk_edit_smart_offers() {

			$post_ids = ( ! empty( $_POST['post_ids'] ) ) ? $_POST['post_ids'] : array();
			$is_reset_quick_stats = ( ! empty( $_POST['so_reset_quick_stats'] ) && $_POST['so_reset_quick_stats'] == 'yes' ) ? true : false;

			if ( $is_reset_quick_stats ) {
				$this->reset_quick_stats( $post_ids );
			}
			die();

		}

		/**
		 * Function to reset stats for ALL or Given offer ids
		 */
		function reset_quick_stats( $offer_ids = array(), $all = false ) {
			global $wpdb;

			if ( empty( $offer_ids ) && ! $all ) return;

			if ( ! is_array( $offer_ids ) ) {
				$offer_ids = array( $offer_ids );
			}

			$wpdb->query("SET SESSION group_concat_max_len=999999");

			$smart_offers_args = array(
										'post_type' => 'smart_offers',
										'fields' => 'ids',
										'nopaging' => true,
										'post_status' => 'any',
										'meta_query' => array(
																'relation' => 'OR',
																array(
																		'key' => 'so_accept_skip_counter'
																	),
																array(
																		'key' => 'so_order_count'
																	)
															)
									);

			if ( ! $all ) {
				$smart_offers_args += array( 'post__in' => $offer_ids );
			}

			$smart_offers_results = new WP_Query( $smart_offers_args );

			if ( $smart_offers_results->post_count > 0 ) {

				$wpdb->query("DELETE FROM {$wpdb->prefix}postmeta
									 WHERE meta_key IN ('so_accept_skip_counter', 'so_order_count')
									 AND post_id IN ( ".implode( ',', $smart_offers_results->posts )." )");

			}

		}

		function get_order_data() {
			global $sa_smart_offers, $typenow;

			$order_data = array();

			if ( empty( $typenow ) || $typenow != 'smart_offers' || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return $order_data;
			}

			$valid_order_status = array( 'wc-completed', 'wc-processing', 'wc-on-hold' );
			$get_valid_order_statuses = get_option( 'so_valid_order_statuses_for_earning', $valid_order_status );

			$offers_sale_args = array(
										'post_type' => 'shop_order',
										'fields' => 'ids',
										'nopaging' => true,
										'post_status' => $get_valid_order_statuses,
										'meta_query' => array(
															array(
																	'key' => 'smart_offers_meta_data'
																)
														)
									);
			$offers_sale_order_ids = new WP_Query( $offers_sale_args );

			$store_currency = get_option( 'woocommerce_currency' );

			if ( $offers_sale_order_ids->post_count > 0 ) {
				foreach ( $offers_sale_order_ids->posts as $post_id ) {
					$result = get_post_meta( $post_id, 'smart_offers_meta_data', true );
					foreach ( $result as $key => $value ) {
						if ( empty( $order_data[ $key ] ) || ! is_array( $order_data[ $key ] ) ) {
							$order_data[ $key ] = array();
						}
						if ( empty( $order_data[ $key ]['earnings'] ) ) {
							$order_data[ $key ]['earnings'] = 0;
						}

						// TODO: move currency dependency class checking in one class and then route through individual plugin files
						$order_currency = get_post_meta( $post_id, '_order_currency', true );
						if ( $store_currency !== $order_currency && class_exists( 'SO_Aelia_CS_Compatibility' ) ) {
							$so_aelia_cs = new SO_Aelia_CS_Compatibility();
							$so_converted_total = $so_aelia_cs->modify_so_contri_amount( $value['offered_price'], $order_currency );
							$order_data[ $key ]['earnings'] += $so_converted_total;
						} else {
							$order_data[ $key ]['earnings'] += $value['offered_price'];
						}
					}
				}
			}

			$this->order_data = $order_data;

			return $this->order_data;
		}

	}

	return new SO_Admin_Offers();
}
