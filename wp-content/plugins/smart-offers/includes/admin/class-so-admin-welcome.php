<?php
/**
 * Welcome (Docs & Support) Page
 *
 * @author      StoreApps
 * @since       2.7
 * @version     1.4.4
 * @package     Smart Offers
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SO_Admin_Welcome class
 */
class SO_Admin_Welcome {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'so_welcome' ) );
		add_action( 'admin_footer', array( $this, 'smart_offers_support_ticket_content' ) );
	}

	/**
	 * Add admin menus/screens.
	 */
	public function show_welcome_page() {

		if ( empty( $_GET['page'] ) ) {
			return;
		}

		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#menu-posts-smart_offers').find('a[href$=so-shortcode]').addClass('current');
				jQuery('#menu-posts-smart_offers').find('a[href$=so-about]').addClass('current');
				jQuery('#menu-posts-smart_offers').find('a[href$=so-shortcode]').parent().addClass('current');
				jQuery('#menu-posts-smart_offers').find('a[href$=so-about]').parent().addClass('current');
			});
		</script>
		<?php

		switch ( $_GET['page'] ) {
			case 'so-about' :
				$this->about_screen();
				break;
			case 'so-shortcode' :
				$this->shortcode_screen();
				break;
			case 'so-faqs' :
				$this->faqs_screen();
				break;
		}
	}

	/**
	 * Add styles just for this page, and remove dashboard page links.
	 */
	public function admin_head() {
		$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : '';

		if ( ! empty( $get_page ) && ( 'so-faqs' === $get_page || 'so-shortcode' === $get_page || 'so-about' === $get_page ) ) {
			?>
			<style type="text/css">
				.about-wrap h3 {
					margin-top: 1em;
					margin-right: 0em;
					margin-bottom: 0.1em;
					font-size: 1.25em;
					line-height: 1.3em;
				}
				.about-wrap .button-primary {
					margin-top: 18px;
				}
				.about-wrap .button-hero {
					color: #FFF!important;
					border-color: #03a025!important;
					background: #03a025 !important;
					box-shadow: 0 1px 0 #03a025;
					font-size: 1em;
					font-weight: bold;
				}
				.about-wrap .button-hero:hover {
					color: #FFF!important;
					background: #0AAB2E!important;
					border-color: #0AAB2E!important;
				}
				.about-wrap p {
					margin-top: 0.6em;
					margin-bottom: 0.8em;
					line-height: 1.6em;
					font-size: 14px;
				}
				.about-wrap .feature-section {
					padding-bottom: 5px;
				}
				.so-features {
					max-width: none !important;
					margin-left: unset !important;
				}
				.so-getting-started {
					font-size: 1.5em;
					margin-bottom: 1em;
				}
				div#TB_window {
					background: lightgrey;
				}
				.so-support-ticket-1 {
					background-color: #ecddef;
					padding: 0.5em;
					margin: 1.2em 0;
					border: 1px solid #4e4e8a;
				}
				.so-support-ticket-2 {
					font-size: 1.8em;
					color: #43438e;
					margin-left: -0.1em;
					margin-right: 0.2rem;
					margin-bottom: 0.45em;
					line-height: inherit;
				}
				.so-support-ticket-3 {
					color: #43438e !important;
				}
				.about-wrap .has-2-columns,
				.about-wrap .has-3-columns {
					max-width: unset !important;
				}
				ul.so_list {
					list-style: disc !important;
				}
				h4.brand_color {
					color: #5850EC;
				}
			</style>
			<?php
		}
	}

	/**
	 * Intro text/links shown on all about pages.
	 */
	private function intro() {

		if ( is_callable( 'SA_Smart_Offers::get_smart_offers_plugin_data' ) ) {
			$plugin_data = SA_Smart_Offers::get_smart_offers_plugin_data();
			$version = $plugin_data['Version'];
		} else {
			$version = '';
		}

		$current_user = wp_get_current_user();
		if ( ! $current_user->exists() ) {
			return;
		}

		?>
		<h1><?php printf( __( 'Thank you for installing Smart Offers %s!', 'smart-offers' ), $version ); ?></h1>

		<div style="margin-top:0.3em;"><?php echo __( 'Glad to have you onboard. We hope Smart Offers adds to your desired success 🏆', 'smart-offers' ); ?></div>

		<div class="has-2-columns feature-section col two-col" style="margin-bottom:30px !important;">
			<div class="column col">
				<?php
				$current_user_display_name = $current_user->display_name;
				$so_ready_designs_imported = get_option ( 'smart_offers_ready_designs_imported' );
				if ( empty( $so_ready_designs_imported ) ) {
					$count = (array) wp_count_posts( 'smart_offers' );
					$sum = array_sum( $count );
					if ( $sum == 0 ) {
						?>
						<div class="so-getting-started">
							<?php echo sprintf(__( 'Hey %s 😊', 'smart-offers' ), $current_user_display_name ); ?>
						</div>
						<ul>
							<li><?php echo __( 'To get you started quickly, we have created <b>some amazing offer designs</b> for you.', 'smart-offers' ); ?></li>
							<li><?php echo __( 'They are ready to use. All you need to do is fill in the product and offer rules and then you are good to go.', 'smart-offers' ); ?></li>
						</ul>
						<a class="button button-hero" href="<?php echo admin_url('edit.php?post_type=smart_offers'); ?>"><?php _e( 'Click to view these offers', 'smart-offers' ); ?></a>
						<?php
						$args = SO_Admin_Ready_Offer_Designs::get_sample_offers();
						SO_Admin_Ready_Offer_Designs::import_smart_offers( $args );
						update_option( 'smart_offers_ready_designs_imported', 'yes', 'no' );
					} elseif ( $sum > 0 ) {
						?>
						<div class="so-getting-started">
							<?php echo __( 'New Offer Designs 😊', 'smart-offers' ); ?>
						</div>
						<ul>
							<li><?php echo sprintf(__( 'Hey %s,', 'smart-offers' ), $current_user_display_name ); ?></li>
							<li><?php echo __( 'We have created some amazing offer designs for you.', 'smart-offers' ); ?></li>
							<li><?php echo __( 'They are ready to use. So all you need to do is fill in the product & rules and you are good to go.', 'smart-offers' ); ?></li>
						</ul>
						<a class="button button-hero" id="so-ready-offer-designs" href="<?php echo admin_url('edit.php?post_type=smart_offers&page=so-about&action=so-import'); ?>">
							<?php echo __( 'Click to view these offers', 'smart-offers' ); ?>
						</a>
						<?php
					}
				} else {
					?>
					<a class="button button-hero" href="<?php echo admin_url('edit.php?post_type=smart_offers'); ?>"><?php echo __( 'Go to All Offers', 'smart-offers' ); ?></a>
					<?php
				}
				?>
			</div>
			<div class="column col last-feature">
				<p align="right">
					<a class="button-primary" href="<?php echo admin_url( 'edit.php?post_type=smart_offers&page=so-settings' ); ?>" target="_blank"><?php echo __( 'Settings', 'smart-offers' ); ?></a>
					<a class="button-primary" href="<?php echo esc_url( apply_filters( 'smart_offers_docs_url', 'https://www.storeapps.org/knowledgebase_category/smart-offers/?utm_source=so&utm_medium=in_app&utm_campaign=view_bundle', 'smart-offers' ) ); ?>" target="_blank"><?php echo __( 'Docs', 'smart-offers' ); ?></a>
				</p>
			</div>
		</div>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php if ( $_GET['page'] == 'so-about' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'so-about' ), 'edit.php?post_type=smart_offers' ) ); ?>">
				<?php echo __( 'Get to Know', 'smart-offers' ); ?>
			</a>
			<a class="nav-tab <?php if ( $_GET['page'] == 'so-shortcode' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'so-shortcode' ), 'edit.php?post_type=smart_offers' ) ); ?>">
				<?php echo __( 'Available Shortcodes', 'smart-offers' ); ?>
			</a>
			<a class="nav-tab <?php if ( $_GET['page'] == 'so-faqs' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'so-faqs' ), 'edit.php?post_type=smart_offers' ) ); ?>">
				<?php echo __( 'FAQ\'s', 'smart-offers' ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Output the about screen.
	 */
	public function about_screen() {
		?>
		<div class="wrap about-wrap" style="max-width: none !important;">

			<?php $this->intro(); ?>

			<div class="changelog">

				<div class="feature-section">
					<h3 style="text-align: center;"><?php echo __( 'What is Smart Offers?', 'smart-offers' ); ?></h3>
					<p class="so-features">
						<?php echo __( 'Smart Offers is a WooCommerce extension that lets you create powerful, profit boosting sales funnels. Sell more to customers while they are making another purchase. Use <code>upsells, cross-sells, downsells, one time offers and backend promotions</code> for targeted customers.', 'smart-offers' ); ?>
					</p>
					<p class="so-features">
						<?php echo __( 'If the customer does not accept your upsell offer, you can give them another offer – either a lower priced downsell or another offer that better suits your customers needs. You can link as many offers as you want like this..', 'smart-offers' ); ?>
					</p>
				</div>
				<div class="has-3-columns feature-section col three-col">
					<div class="column col">
						<h4><?php echo __( 'Getting Started', 'smart-offers' ); ?></h4>
						<p>
							<?php echo __( 'How to create your first offer?', 'smart-offers' ); ?>
							<a target="_blank" href="https://www.storeapps.org/docs/so-whats-the-offer/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page">
								<?php echo __( 'Click here to know', 'smart-offers' ); ?>
							</a>
						</p>
						<p>
							<?php echo __( 'See Smart Offers in Action', 'smart-offers' ); ?>
							<a target="_blank" href="https://www.storeapps.org/support/documentation/smart-offers/smart-offers-action/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page">
								<?php echo __( 'Click here to know', 'smart-offers' ); ?>
							</a>
						</p>
					</div>
					<div class="column col">
						<h4><?php echo __( 'Designing offers', 'smart-offers' ); ?></h4>
						<p><?php echo __( 'Want to design your offers like professionals? Check out following links:', 'smart-offers' ); ?>
							<ul class="so_list">
								<li>
									<a target="_blank" href="https://www.storeapps.org/woocommerce-offer-design/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page">
										<?php echo __( 'Smart Offers took away your biggest obstacle – Designing Offers', 'smart-offers' ); ?>
									</a>
								</li>
								<li>
									<a target="_blank" href="https://www.storeapps.org/create-awesome-looking-offers-quickly-in-woocommerce/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page">
										<?php echo __( 'Create Awesome Looking Offers In Few Minutes', 'smart-offers' ); ?>
									</a>
								</li>
							</ul>
						</p>
					</div>
					<div class="column col last-feature">
						<h4 class="brand_color"><?php echo __( 'Types of offers you can create using Smart Offers', 'smart-offers' ); ?></h4>
							<ul class="so_list">
								<li>
									<a target="_blank" href="https://www.storeapps.org/woocommerce-giveaway/?utm_source=so&utm_medium=in_app&utm_campaign=view_giveaway_blog">
										<?php echo __( 'Giveaway offer', 'smart-offers' ); ?>
									</a>
								</li>
								<li>
									<a target="_blank" href="https://www.storeapps.org/woocommerce-upsells/?utm_source=so&utm_medium=in_app&utm_campaign=view_upsells_blog">
										<?php echo __( 'Upsells & Downsell offers', 'smart-offers' ); ?>
									</a>
								</li>
								<li>
									<a target="_blank" href="https://www.storeapps.org/woocommerce-bogo/?utm_source=so&utm_medium=in_app&utm_campaign=view_bogo_blog">
										<?php echo __( 'BOGO offer', 'smart-offers' ); ?>
									</a>
								</li>
								<li>
									<a target="_blank" href="https://www.storeapps.org/woocommerce-upsell-offer-bulk-purchase-user-roles/?utm_source=so&utm_medium=in_app&utm_campaign=view_bulk_user_blog">
										<?php echo __( 'Offer to target customers based on bulk purchase / user roles', 'smart-offers' ); ?>
									</a>
								</li>
							</ul>
							<?php echo __( 'and many more...', 'smart-offers' ); ?>
						</p>
					</div>
				</div>
				<div class="has-3-columns feature-section col three-col">
					<div class="column col">
						<h4 class="brand_color"><?php echo __( 'Must have plugins to use with Smart Offers:', 'smart-offers' ); ?></h4>
						<ul class="so_list">
							<li>
								<a target="_blank" href="https://www.storeapps.org/product/woocommerce-buy-now/?utm_source=so&utm_medium=in_app&utm_campaign=view_product_bn">
									<?php echo __( 'WooCommerce Buy Now', 'smart-offers' ); ?>
								</a>
							</li>
							<li>
								<a target="_blank" href="https://www.storeapps.org/product/custom-thank-you-pages-per-product-for-woocommerce/?utm_source=so&utm_medium=in_app&utm_campaign=view_product_ctp">
									<?php echo __( 'WooCommerce Custom Thank You Pages', 'smart-offers' ); ?>
								</a>
							</li>
							<li>
								<a target="_blank" href="https://www.storeapps.org/product/woocommerce-marketing-bundle/?utm_source=so&utm_medium=in_app&utm_campaign=view_bundle">
									<?php echo __( 'WooCommerce Marketing Bundle', 'smart-offers' ); ?>
								</a>
							</li>
							<li>
								<a target="_blank" href="https://woocommerce.com/products/chained-products/?aff=5475">
									<?php echo __( 'WooCommerce Chained Products', 'smart-offers' ); ?>
								</a>
							</li>
						</ul>
					</div>
					<div class="column col">
						<h4><?php echo __( 'Use Smart Offers with Popular Page Builders', 'smart-offers' ); ?></h4>
						<ul class="so_list">
							<li>
								<a target="_blank" href="https://www.storeapps.org/smart-offers-beaver-builder-integration/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page">
									<?php echo __( 'Smart Offers now Integrates with Beaver Builder', 'smart-offers' ); ?>
								</a>
							</li>
							<li>
								<a target="_blank" href="https://www.storeapps.org/smart-offers-elementor-integration/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page">
									<?php echo __( 'Smart Offers now Integrates with Elementor', 'smart-offers' ); ?>
								</a>
							</li>
						</ul>
					</div>
					<div class="column col last-feature">
						<h4 class="brand_color"><?php echo __( 'How to do <code>one-click purchase/upsell</code>?', 'smart-offers' ); ?></h4>
						<p>
							<?php echo __( 'Use Smart Offers with our ', 'smart-offers' ); ?>
							<a target="_blank" href="https://www.storeapps.org/product/woocommerce-buy-now/?utm_source=so&utm_medium=in_app&utm_campaign=view_product_bn"><?php echo __( 'Buy Now', 'smart-offers' ); ?></a>
							<?php echo __( ' plugin. Know more:', 'smart-offers' ); ?>
						</p>
						<p>
							<ul class="so_list">
								<li>
									<a target="_blank" href="https://www.storeapps.org/how-to-create-1-click-upsells-in-woocommerce/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page">
										<?php echo __( 'Create one-click Upsells in WooCommerce', 'smart-offers' ); ?>
									</a>
								</li>
								<li>
									<a target="_blank" href="https://www.storeapps.org/docs/bn-what-are-the-buy-now-1-click-requirements/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page">
										<?php echo __( 'One click requirements', 'smart-offers' ); ?>
									</a>
								</li>
							</ul>
						</p>
					</div>
				</div>
			</div>
			<div class="feature-section">
				<h3 style="text-align: center;"><?php echo __( 'Explore all 20+ plugins from StoreApps', 'smart-offers' ); ?></h3>
				<p style="text-align: center;">
					<a class="button button-hero" href="https://www.storeapps.org/shop/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page" target="_blank">
						<?php echo __( 'Yes, show me', 'smart-offers' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Output the shortcode reference screen.
	 */
	public function shortcode_screen() {
		?>
		<style>
			.sa-so-shortcodes {
				margin-left: unset !important;
			}
		</style>
		<div class="wrap about-wrap" style="max-width: none !important;">

			<?php $this->intro(); ?>

			<h3><?php echo __( 'Smart Offer has a few custom shortcodes that you can use anywhere in the offer content while designing an offer.', 'smart-offers' ); ?></h3>

			<div class="has-2-columns feature-section col two-col">
				<div class="column col">
					<h4>
						<?php echo __( '1) ', 'smart-offers' ) ?>
						<code><?php echo __( '[so_show_offers]', 'smart-offers' ); ?></code>
					</h4>
					<p class="sa-so-shortcodes">
						<?php echo __( 'In addition to default page option i.e Home, Cart, Checkout, Order Received, My Account, you can show offers on any other page by using the shortcode [so_show_offers]', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<b><?php echo __( 'Shortcode Attributes', 'smart-offers' ); ?></b>
					</p>
					<table class="wp-list-table widefat striped">
						<thead>
							<tr>
								<th><?php echo __( 'Attributes', 'smart-offers' ); ?></th>
								<th><?php echo __( 'Values', 'smart-offers' ); ?></th>
								<th><?php echo __( 'Description', 'smart-offers' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code><?php echo __( 'display_as', 'smart-offers' ); ?></code></td>
								<td><code><?php echo __( 'inline', 'smart-offers' ); ?></code> / <code><?php echo __( 'popup', 'smart-offers'); ?></code></td>
								<td><?php echo __( 'It determines how to display the offer. If no value is passed then value would be taken from the option saved in “Which page/pages to show offer on -> Show offer as” of the offer that will be shown.', 'smart-offers' ); ?></td>
							</tr>
							<tr>
								<td><code><?php echo __( 'offer_ids', 'smart-offers' ); ?></code></td>
								<td><?php echo __( 'Comma separated offer_ids', 'smart-offers' ); ?></td>
								<td><?php echo __( 'It will show one of the offer from the ids mentioned in this argument. If no value is passed, then Smart Offers will fetch all offers having option “Any other page where shortcode is added” ticked under “Which page/pages to show offer on -> Show offer on” and show one of the offer satisfying Offer rules and Smart Offers Settings.', 'smart-offers' ); ?></td>
							</tr>
						</tbody>
					</table>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Example Usage,', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<code><?php echo __( '[so_show_offers]', 'smart-offers' ); ?></code><br>
						<code><?php echo __( '[so_show_offers offer_ids="10"]', 'smart-offers' ); ?></code><br>
						<code><?php echo __( '[so_show_offers display_as="popup" offer_ids="1,2,3"]', 'smart-offers' ); ?></code>
					</p>
				</div>
				<div class="column col last-feature">
					<h4>
						<?php echo __( '2) ', 'smart-offers' ) ?>
						<code><?php echo __( '[so_quantity]', 'smart-offers' ); ?></code>
					</h4>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Shortcode [so_quantity] will show quantity box in the offer, allowing your customer to select the quantity of the offered product.', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<b><?php echo __( 'Shortcode Attributes', 'smart-offers' ); ?></b>
					</p>
					<table class="wp-list-table widefat striped">
						<thead>
							<tr>
								<th><?php echo __( 'Attributes', 'smart-offers' ); ?></th>
								<th><?php echo __( 'Values', 'smart-offers' ); ?></th>
								<th><?php echo __( 'Description', 'smart-offers' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code><?php echo __( 'value', 'smart-offers' ); ?></code></td>
								<td><?php echo __( '-', 'smart-offers' ); ?></td>
								<td><?php echo __( 'It defines what should be the quantity value. Default is 1.', 'smart-offers' ); ?></td>
							</tr>
							<tr>
								<td><code><?php echo __( 'min', 'smart-offers' ); ?></code></td>
								<td><?php echo __( '-', 'smart-offers' ); ?></td>
								<td><?php echo __( 'It defines what should be the minimum quantity that your customer can select. Default is 1.', 'smart-offers' ); ?></td>
							</tr>
							<tr>
								<td><code><?php echo __( 'max', 'smart-offers' ); ?></code></td>
								<td><?php echo __( '-', 'smart-offers' ); ?></td>
								<td><?php echo __( 'It defines what should be the maximum quantity that your customer can select.', 'smart-offers' ); ?></td>
							</tr>
							<tr>
								<td><code><?php echo __( 'allow_change', 'smart-offers' ); ?></code></td>
								<td><code><?php echo __( 'true', 'smart-offers' ); ?></code> / <code><?php echo __( 'false', 'smart-offers'); ?></code></td>
								<td><?php echo __( 'It defines whether you want to allow your customer to change the quantity or not and indirectly determining whether to show quantity in the offer or not. Default is false.', 'smart-offers' ); ?></td>
							</tr>
						</tbody>
					</table>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Example Usage,', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<code><?php echo __( '[so_quantity allow_change=true]', 'smart-offers' ); ?></code><br>
						<code><?php echo __( '[so_quantity value=2 max=10 allow_change=true]', 'smart-offers' ); ?></code><br>
						<code><?php echo __( '[so_quantity min=2 allow_change=true max=6]', 'smart-offers' ); ?></code>
					</p>
				</div>
			</div>

			<div class="has-2-columns feature-section col two-col">
				<div class="column col">
					<h4>
						<?php echo __( '3) ', 'smart-offers' ) ?>
						<code><?php echo __( '[so_price]', 'smart-offers' ); ?></code>
					</h4>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Shortcode [so_price] will show the original price & the new price of the offered product in the offer.', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Example Usage,', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<code>
							<?php echo __( '[so_price]', 'smart-offers' ); ?>
						</code>
					</p>
					<p class="sa-so-shortcodes">
						<?php echo __( '<b>Note</b>: This shortcode is to be used only if offered product is a <b>Simple</b> product. If offered product is a Variable product, the price will be included and shown by default i.e. you do not need to write this shortcode if offered product is a Variable product.', 'smart-offers' ); ?>
					</p>
				</div>
				<div class="column col last-feature">
					<h4>
						<?php echo __( '4) ', 'smart-offers' ) ?>
						<code><?php echo __( '[so_product_image]', 'smart-offers' ); ?></code>
					</h4>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Shortcode [so_product_image] will show default image of the product in the offer.', 'smart-offers' ); ?>
					</p>
					<table class="wp-list-table widefat striped">
						<thead>
							<tr>
								<th><?php echo __( 'Attribute', 'smart-offers' ); ?></th>
								<th><?php echo __( 'Default Value', 'smart-offers' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code><?php echo __( 'image', 'smart-offers' ); ?></code></td>
								<td><code><?php echo __( 'yes', 'smart-offers' ); ?> </code></td>
							</tr>
						</tbody>
					</table>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Example Usage,', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<code>
							<?php echo __( '[so_product_image image="yes"]', 'smart-offers' ); ?>
						</code>
					</p>
				</div>
			</div>

			<div class="has-2-columns feature-section col two-col">
				<div class="column col">
					<h4>
						<?php echo __( '5) ', 'smart-offers' ) ?>
						<code><?php echo __( '[so_product_name]', 'smart-offers' ); ?></code>
					</h4>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Shortcode [so_product_name] will show offered product name in the offer.', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Example Usage,', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<code>
							<?php echo __( '[so_product_name]', 'smart-offers' ); ?>
						</code>
					</p>
				</div>
				<div class="column col last-feature">
					<h4>
						<?php echo __( '6) ', 'smart-offers' ) ?>
						<code><?php echo __( '[so_product_short_description]', 'smart-offers' ); ?></code>
					</h4>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Shortcode [so_product_short_description] will show short description of the offered product in the offer.', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Example Usage,', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<code>
							<?php echo __( '[so_product_short_description]', 'smart-offers' ); ?>
						</code>
					</p>
				</div>
			</div>

			<div class="has-2-columns feature-section col two-col">
				<div class="column col">
					<h4>
						<?php echo __( '7) ', 'smart-offers' ) ?>
						<code><?php echo __( '[so_acceptlink]', 'smart-offers' ); ?></code>
					</h4>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Shortcode [so_acceptlink] will generate an accept url for the offer. You need to pass offer id in the shortcode to generate offer specific link.', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Example Usage,', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<code>
							<?php echo __( '&lta href="[so_acceptlink offer_id=23]"&gtYes, Add to Cart&lt/a&gt', 'smart-offers' ); ?>
						</code>
					</p>
				</div>
				<div class="column col last-feature">
					<h4>
						<?php echo __( '8) ', 'smart-offers' ) ?>
						<code><?php echo __( '[so_skiplink]', 'smart-offers' ); ?></code>
					</h4>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Shortcode [so_skiplink] will generate an skip url for the offer. You need to pass offer id in the shortcode to generate offer specific link.', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Example Usage,', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<code>
							<?php echo __( '&lta href="[so_skiplink offer_id=23]"&gtNo, Skip this&lt/a&gt', 'smart-offers' ); ?>
						</code>
					</p>
				</div>
			</div>

			<div class="has-2-columns feature-section col two-col">
				<div class="column col">
					<h4>
						<?php echo __( '9) ', 'smart-offers' ) ?>
						<code><?php echo __( '[so_product_variants]', 'smart-offers' ); ?></code>
					</h4>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Shortcode [so_product_variants] will show the variation option for the parent variable product.', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<?php echo __( 'If you want to let your customer select which variation product they would want as an offer, then add parent variable product in Offered product and shortcode [so_product_variants] in the offer description.', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<?php echo __( 'Example Usage,', 'smart-offers' ); ?>
					</p>
					<p class="sa-so-shortcodes">
						<code>
							<?php echo __( '[so_product_variants]', 'smart-offers' ); ?>
						</code>
					</p>
				</div>
				<div class="column col last-feature">
				</div>
			</div>

			<h3 style="text-align: center;">
				<?php echo sprintf(__( 'It is explained in detail here: %s.', 'smart-offers' ), '<a target="_blank" href="https://www.storeapps.org/docs/so-shortcode-reference/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page">' . __( "Shortcode Reference", "smart-offers" ) . '</a>' ); ?>
			</h3>

		</div>
		<?php
	}

	/**
	 * Output the FAQ's screen.
	 */
	public function faqs_screen() {
		?>
		<div class="wrap about-wrap" style="max-width: none !important;">

		<?php $this->intro(); ?>

		<h3><?php echo __("FAQ / Common Problems", 'smart-offers'); ?></h3>

		<?php
			$faqs = array(
						array(
								'que' => __( '1. When I click on accept or skip link in the offer, I get a 404 page not found error and nothing is added to the cart.', 'smart-offers' ),
								'ans' => __( 'Edit that offer and go to offer content. Switch to Text tab and check if the accept and skip shortcodes are in the following format:<br>
												<code>For accept: &lta href=”[so_acceptlink offer_id=23]”&gt</code><br>
												<code>For skip: &lta href=”[so_skiplink offer_id=23]”&gt</code><br><br>
												If not in above format, convert if and then save the offer (Here offer_id is the id of the current offer). Then check the offer. Accept & Skip link will work correctly now.', 'smart-offers' )
							),
						array(
								'que' => __( '2. I can’t see offer preview when I click on ‘Preview Changes’ button.', 'smart-offers' ),
								'ans' => __( 'De-activate and re-activate Smart Offers once and then have a check if it is working. If it is still not working, save your permalinks once i.e. go to WordPress Dashboard -> Settings > Permalinks and click on ‘Save Changes‘. It will work after that.', 'smart-offers' )
							),
						array(
								'que' => __( '3. Offer is not showing up / I can\'t see the offer.', 'smart-offers' ),
								'ans' => __( 'Please check:<br>
												1. if you are not using the <a href="https://www.storeapps.org/docs/so-changelog/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page" target="_blank">latest version</a> of Smart Offers<br>
												2. if the Offered Product field while creating an offer is empty<br>
												3. if the Offered Product is Hidden and option Show Offer for hidden products in Woocommerce -> Settings -> Smart Offers is set to No<br>
												4. if the Offered Product is out of stock<br>
												5. if none of the option is ticked in Which page/s to show this offer on<br>
												6. if the Offer does not satisfy the offer rules<br>
												7. try switching from Inline to Popup and vice-versa<br>
												8. if you have multiple rules in the offer, then make sure all offer rules are getting satisfied as all rules use AND operation (check more from <a href="https://www.storeapps.org/docs/so-when-to-show-this-offer/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page" target="_blank">here</a>)<br>
												9. if a customer has already accepted or skipped that offer because an offer is visible only once to a customer in that particular session. The customer can again see the same offer when they log in again (registered users). For guest users, the same offer will be visible again when they close the browser and open it again i.e. upon visiting your site next next time<br>
												10. if there is any error in browser’s console (open browser console in Chrome using Ctrl+Shift+j). Check from where those errors are coming from (most of the time it is your theme or any other plugin activated) and resolve them
												11. if you are using any caching plugin, clear cache from that plugin. Also clear cache from your browser and then have a check<br>
												12. conflict with any other plugin or your theme. Check our <a href="https://www.storeapps.org/docs/self-service-guide/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page" target="_blank">Self Service Guide</a> to find and resolve conflict with other plugins/theme<br>', 'smart-offers' )
							),
						array(
								'que' => __( '4. For Variable Product, I’m not able to select Variations (or not visible / or broken) from the drop down in the Offer.', 'smart-offers' ),
								'ans' => __( 'Please make sure:<br>
												1. you have saved Variations correctly in the Variable Product<br>
												2. if you can select the Variations on the Variable Product Page<br>
												3. you have shortcode [so_product_variants] written inside the offer content<br>
												4. <b>you have not added any custom attributes for that variable product, as currently Smart Offers doesn\'t support custom attributes</b><br><br>
												If you still can’t see Variations even after performing above steps, then it might be possible that Smart Offer is either conflicting with your theme or any other plugin. To verify, de-activate all your plugins except WooCommerce & Smart Offers and switch your current theme to default WordPress theme ( Storefront / 2017 ) and then have a check with the functionality (Refer <a href="https://www.storeapps.org/docs/self-service-guide/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page" target="_blank">Self Service Guide</a> for detailed steps).<br><br>
												If you can then select Variations from the drop down, then keep on activating other plugins one-by-one to find the conflicting plugin and then theme at the end and then check if it is working.<br>', 'smart-offers' )
							),
						array(
								'que' => __( '5. Even after accepting / skipping an offer, the same offer is showing? / Offer keeps on showing up?', 'smart-offers' ),
								'ans' => __( 'Are you using Smart Offers shortcode with offer_ids i.e. example, [so_show_offers offer_ids="109"]?<br>
											If yes, then that is the reason for re-execution of the same offer. On accepting / skipping the offer, page is getting refreshed. WordPress then re-executes the content written in the editor and since shortcode is written inside editor, show offer shortcode also gets executed along with it and hence you are seeing same offer again.<br><br>
											To overcome this, in your offer you can configure any of the following setting,<br>
											– set a unique rule in the offer which will prevent the showing up of the offer.<br>
											– under Actions to take when offer is Accepted , select option Redirect to a URL and redirect your customers to any other page on your site after accepting the offer.<br>
											– under Actions to take when offer is Skipped , select option Skip & Redirect to and redirect your customers to any other page on your site after skipping the offer.<br>', 'smart-offers' ),
							),
						array(
								'que' => __( '6. Once I accept / skip an offer, why I\'m not able to see the same offer again?', 'smart-offers' ),
								'ans' => __( 'By default in Smart Offers if an offer is accepted / skipped, then the same offer won’t be visible until the customer logs in again (registered users) i.e. an offer will be visible only once in that particular session. For guest users, the same offer will be visible again when they close the browser and open it again i.e. upon visiting your site next next time.', 'smart-offers' )
							),
						array(
								'que' => __( '7. I have setup offer to display as Popup but it shows as an Inline Offer.', 'smart-offers' ),
								'ans' => __( 'Have you selected multiple offers to show on that same page? If yes, then it is behaving correctly i.e. when you show multiple offers on one page, all offers will be shown as “Inline”.', 'smart-offers' )
							),
						array(
								'que' => __( '8. I have added shortcode <code>[so_quantity]</code> but quantity box doesn\'t appear in offer.', 'smart-offers' ),
								'ans' => __( 'That is because you haven\'t added any parameter to the shortcode. To show quantity in the offer, add <code>[so_quantity allow_change=true]</code>, this will show quantity box in the offer.', 'smart-offers' )
							),
						array(
								'que' => __( '9. How can I check Conversion rate of my offers?', 'smart-offers' ),
								'ans' => sprintf(__( 'Refer this: %s', 'smart-offers' ), '<a href="https://www.storeapps.org/docs/so-how-to-check-your-offers-statistics/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page" target="_blank">' . __( 'How to check Earnings from Smart Offers', 'smart-offers' ) . '</a>' )
							),
						array(
								'que' => __( '10. I still have questions?', 'smart-offers' ),
								'ans' => sprintf(__( '<span style="font-size: 1.5em;">Check more FAQ\'s from %1s or %2s </span>', 'smart-offers' ), '<a href="https://www.storeapps.org/docs/so-after-purchase-faqs/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page" target="_blank">' . __( 'here', 'smart-offers' ) . '</a>', '<a href="https://www.storeapps.org/support/contact-us/?utm_source=so&utm_medium=in_app&utm_campaign=view_docs_welcome_page" target="_blank">' . __( 'Contact Us', 'smart-offers' ) . '</a>' )
							)
					);

				$faqs = array_chunk( $faqs, 2 );

				echo '<div>';
				foreach ( $faqs as $fqs ) {
					echo '<div class="has-2-columns two-col">';
					foreach ( $fqs as $index => $faq ) {
						echo '<div' . ( ( $index == 1 ) ? ' class="column col last-feature"' : ' class="column col"' ) . '>';
						echo '<h4>' . $faq['que'] . '</h4>';
						echo '<p>' . $faq['ans'] . '</p>';
						echo '</div>';
					}
					echo '</div>';
				}
				echo '</div>';
			?>

			</div>

		<?php
	}

	/**
	 * Sends user to the welcome page on activation.
	 */
	public function so_welcome() {

		if ( ! get_transient( '_so_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_so_activation_redirect' );

		wp_redirect( admin_url( 'edit.php?post_type=smart_offers&page=so-about' ) );
		exit;

	}

	/**
	 * Smart Offer's Support Form
	 */
	function smart_offers_support_ticket_content() {
		global $sa_smart_offers_upgrade;

		if ( !wp_script_is('thickbox') ) {
			if ( !function_exists('add_thickbox') ) {
				require_once ABSPATH . 'wp-includes/general-template.php';
			}
			add_thickbox();
		}

		if ( ! method_exists( 'StoreApps_Upgrade_3_6', 'support_ticket_content' ) ) return;

		$prefix = 'smart_offers';
		$sku = 'so';
		$plugin_data = get_plugin_data( SO_PLUGIN_FILE );
		$license_key = get_option( $prefix.'_license_key' );

		StoreApps_Upgrade_3_6::support_ticket_content( $prefix, $sku, $plugin_data, $license_key, 'smart-offers' );
	}
}

$GLOBALS['sa_so_admin_welcome'] = new SO_Admin_Welcome();
