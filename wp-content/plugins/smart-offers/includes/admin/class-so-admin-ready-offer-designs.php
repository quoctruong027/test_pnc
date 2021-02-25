<?php
/**
 * Ready offer designs and import logic
 *
 * @author      StoreApps
 * @since       3.4.1
 * @version     1.1.0
 * @package     Smart Offers
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Admin_Ready_Offer_Designs' ) ) {

	class SO_Admin_Ready_Offer_Designs {

		function __construct() {
			add_action( 'init', array( $this, 'init' ) );
		}

		public static function get_sample_offers() {

			$offer_content = array(
				// [Ready Offer Design 1] Surprise Gift
				array(
					'post'     => array(
						'ID'           => '',
						'post_content' => '<div class="so-free-gift">
<h1 class="so-fg-1"><strong>FREE</strong> GIFT</h1>
<div class="so-fg-2">Claim before it runs out of stock</div>
<div class="so-fg-3"><a href="[so_acceptlink]">GET IT NOW ➞</a></div>
<div class="so_skip"><a href="[so_skiplink]">No thanks</a></div>
</div>',
						'post_title'   => '[Ready Offer Design 1] Surprise Gift',
						'post_name'    => 'ready-offer-design-1-surprise-gift',
						'post_status'  => 'publish',
						'post_type'    => 'smart_offers',
						'post_excerpt' => '',
					),
					'postmeta' => array(
						'offer_title'      => '[Ready Offer Design 1] Surprise Gift',
						'discount_type'    => 'percent_discount',
						'offer_price'      => '50',
						'so_show_offer_as' => 'offer_as_popup',
						'so_custom_css'    => ".so-popup #so_this_offer {
	background: none !important;
}
#so_this_offer .so-free-gift {
	text-align: center;
	display: block;
	background-image: url('" . SO_PRE_URL . "assets/images/giftbox.jpg');
	background-position: center bottom;
	background-size: cover;
	background-repeat: no-repeat;
	max-width: 500px;
	margin: 0px auto;
	padding: 1.3em;
}
#so_this_offer .so-fg-1 {
	color: #673111;
	font-size: 3em;
	line-height: 1.2em;
}
#so_this_offer .so-fg-2 {
	font-size: 1.5em;
}
#so_this_offer .so-fg-3 {
	width: 60%;
	background-color: transparent;
	border: 2px solid #673111;
	border-radius: 4px;
	font-size: 1.1em;
	padding: .65em 0;
	margin: 0 auto;
	margin-top: 17.5em;
}",
					),
				),
				// [Ready Offer Design 2] General offer
				array(
					'post'     => array(
						'ID'           => '',
						'post_content' => '<div class="so_go">
<h1 class="so_headline">WHAT\'RE YOU WAITING FOR?</h1>
<h3 class="so_product"><img class="wp-image-288 alignleft" src="' . SO_PRE_URL . 'assets/images/charming-woman-upsell.jpg" alt="" width="226" height="226" /><strong>You\'ve got a $5 discount!
</strong></h3>
<div class="so_model"></div>
<div class="so_upsell_text">Upgrade now and get $5 off.<br>Valid till you reject offer.</div>
<div class="so_price">[so_price]</div>
<div class="so_accept_button"><a href="[so_acceptlink]">UPGRADE NOW!</a></div>
<div class="so_skip_button"><a href="[so_skiplink]">No Thanks!</a></div>
</div>',
						'post_title'   => '[Ready Offer Design 2] General offer',
						'post_name'    => 'ready-offer-design-2-general-offer',
						'post_status'  => 'publish',
						'post_type'    => 'smart_offers',
						'post_excerpt' => '',
					),
					'postmeta' => array(
						'offer_title'      => '[Ready Offer Design 2] General offer',
						'discount_type'    => 'price_discount',
						'offer_price'      => '5',
						'so_show_offer_as' => 'offer_as_popup',
						'so_custom_css'    => '.so-popup #so_this_offer {
	max-width: 700px;
	margin: 0 auto;
}
#so_this_offer .so_headline {
	font-weight: bold;
	color: #77B703;
	text-align: center !important;
	margin-bottom: 1em !important;
}
#so_this_offer .so_upsell_text {
	margin-top: 1em;
	margin-bottom: 1.5em;
}
#so_this_offer .so_product {
	margin-bottom: 0em !important;
}
#so_this_offer .so_model {
	margin-left: 15.8em;
	border-bottom: 1px dashed #cecbcb;
}
#so_this_offer .so_price {
	margin-top: -0.5em;
	margin-bottom: 0.25em;
	font-weight: bold;
	font-size: 1.5em;
	color: #77B703;
}
#so_this_offer .so_accept_button {
	background:hsl(0,0%,26%);
	color:hsl(0,100%,100%);
	text-decoration:none;
	font-weight:400;
	width:25%;
	font-size: 1em;
	border:none;
	border-radius:.6em;
	border-bottom:.3em solid hsl(0,0%,20%);
	text-align:center;
	margin:.2em auto .5em 15.8em;
	padding:0.4em;
	cursor: pointer;
}
#so_this_offer .so_accept_button a {
	color: #FFFFFF;
}
#so_this_offer .so_skip_button a {
	color: #222;
	font-size: 0.8em;
	margin-left: 0.5em;
}
#so_this_offer .so_skip_button {
	margin:.2em auto .5em 15.8em;
	text-decoration: underline;
}
@media only screen and (max-width: 768px) {
	#so_this_offer .so_accept_button {
		width: 60% !important;
		margin: unset !important;
	}
	#so_this_offer .so_skip_button {
		margin: unset !important;
	}
}
@media only screen and (max-width: 600px) {
	#so_this_offer .so_accept_button {
		width: 75% !important;
		margin: unset !important;
	}
	#so_this_offer .so_skip_button {
		margin: unset !important;
	}
}
@media only screen and (max-width: 425px) {
	#so_this_offer .so_accept_button {
		width: 90% !important;
		margin: unset !important;
	}
	#so_this_offer .so_skip_button {
		margin: unset !important;
	}
}
@media only screen and (max-width: 320px) {
	#so_this_offer .so_accept_button {
		width: 100% !important;
		margin: unset !important;
	}
	#so_this_offer .so_skip_button {
		margin: unset !important;
	}
}',
					),
				),
				// [Ready Offer Design 3] Free Gift Card
				array(
					'post'     => array(
						'ID'           => '',
						'post_content' => '<div class="so-free-gift-card">
<h4 style="color: #232323;">CONGRATULATIONS! You Have Won a</h4>
<div class="so-fgc-2"><span class="so-fgc-3">$500</span> Gift Card</div>
<div class="so_accept"><a href="[so_acceptlink]">Yess, I want it</a></div>
<div class="so_skip"><a href="[so_skiplink]">No, I don’t want it</a></div>
</div>',
						'post_title'   => '[Ready Offer Design 3] Free Gift Card',
						'post_name'    => 'ready-offer-design-3-free-gift-card',
						'post_status'  => 'publish',
						'post_type'    => 'smart_offers',
						'post_excerpt' => '',
					),
					'postmeta' => array(
						'offer_title'      => '[Ready Offer Design 3] Free Gift Card',
						'discount_type'    => 'percent_discount',
						'offer_price'      => '100',
						'so_show_offer_as' => 'offer_as_inline',
						'so_custom_css'    => '#so_this_offer {
	max-width: 570px;
	margin: 0 auto;
}
#so_this_offer .so-free-gift-card {
	text-align: center;
	border: #bfbf0f 1em solid;
	padding: 1.1em;
	background-color: #f5f5ee;
}
#so_this_offer .so-fgc-2 {
	font-weight: bold;
	font-size: 4em;
	font-style: oblique;
}
#so_this_offer .so-fgc-3 {
	color: #bfbf0f;
}',
					),
				),
				// [Ready Offer Design 4] Free Shipping
				array(
					'post'     => array(
						'ID'           => '',
						'post_content' => '<table id="so-free-shipping" border="0" width="750" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr>
<td class="so-fs-1">
<table class="so-fs-2">
<tbody>
<tr>
<td class="so-fs-3">
<p class="so-fs-4">Limited Time Offer!</p>
<hr class="so-separator">
</td>
</tr>
<tr>
<td class="so-fs-5">
<p class="so-fs-6">ENJOY FREE SHIPPING</p>
<p class="so-fs-6">ON ANY ORDER</p>
</td>
</tr>
<tr>
<td class="so-fs-7">
<div class="so_accept"><a href="[so_acceptlink]">Apply free shipping</a></div>
<div class="so_skip"><a href="[so_skiplink]">I\'ll pay for shipping</a></div></td>
</tr>
<tr>
<td class="so-fs-8">
<p class="so-fs-9">Expires Today Midnight.</p>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>',
						'post_title'   => '[Ready Offer Design 4] Free Shipping',
						'post_name'    => 'ready-offer-design-4-free-shipping',
						'post_status'  => 'publish',
						'post_type'    => 'smart_offers',
						'post_excerpt' => '',
					),
					'postmeta' => array(
						'offer_title'      => '[Ready Offer Design 4] Free Shipping',
						'discount_type'    => 'fixed_price',
						'so_show_offer_as' => 'offer_as_inline',
						'so_custom_css'    => "@import url('https://fonts.googleapis.com/css?family=Special+Elite');
#so_this_offer {
	background: none !important;
}
#so_this_offer #so-free-shipping {
	width: 85%;
	font-size: 12px;
	line-height: normal;
	margin: 0px auto;
}
#so_this_offer .so-fs-1 {
	padding: 5em;
	background-image: url('" . SO_PRE_URL . "assets/images/seamless.jpg');
}
#so_this_offer .so-fs-2 {
	margin-bottom: 0;
}
#so_this_offer .so-fs-3 {
	text-align: center;
	padding-top: 2em;
	font-size: 1.5em;
	color: #313030;
	font-family: 'Special Elite', cursive;
	letter-spacing: 0.1em;
}
#so_this_offer .so-fs-4 {
	margin-bottom: 0.3em;
	color: #cc2603;
}
#so_this_offer .so-separator {
	width: 21%;
	margin-left: 39%;
	margin-top: 1em;
}
#so_this_offer .so-fs-5 {
	text-align: center;
	font-family: 'Special Elite',
	cursive; background: #FFF;
}
#so_this_offer .so-fs-6 {
	font-size: 2.5em;
	margin-bottom: 0.5em;
}
#so_this_offer .so-fs-7 {
	padding-bottom: 2em;
	padding-top: 2em;
}
#so_this_offer div.so_accept { 
	display: block;
	border: 1px solid white;
	border-style: none;
	width: 30%;
	background: #313338;
	color: #333;
	line-height: 3;
	text-align: center;
	font-size: 1em;
	margin: auto;
	text-decoration: none;
	font-weight: inherit;
	border-radius: 0;
	text-shadow: none;
	box-shadow: 3px 3px 8px #4e4040 !important;
}
#so_this_offer .so_accept a {
	font-size: 1.1em;
}
#so_this_offer #so-free-shipping .so_skip {
	margin: 0.8em auto;
}
#so_this_offer .so_skip a {
	color: #333133;
}
#so_this_offer .so-fs-8 {
	text-align: center;
	padding-bottom: 2em;
	font-size: 1.5em;
	font-family: 'special Elite', cursive;
	letter-spacing: 0.1em;
}
#so_this_offer .so-fs-9 {
	margin-bottom: 0.3em !important;
}
@media only screen and (max-width: 768px) {
	#so_this_offer .so_accept {
		width: 60% !important;
	}
}
@media only screen and (max-width: 600px) {
	#so_this_offer .so_accept {
		width: 75% !important;
	}
}
@media only screen and (max-width: 425px) {
	#so_this_offer .so_accept {
		width: 90% !important;
	}
}
@media only screen and (max-width: 320px) {
	#so_this_offer .so_accept {
		width: 100% !important;
	}
}",
					),
				),
				// [Ready Offer Design 5] Halloween with BOGO
				array(
					'post'     => array(
						'ID'           => '',
						'post_content' => '<div class="so-halloween">
<div class="so-headline">
<div class="so-hn-headline"><span style="font-family: raleway; color: #f29500; font-size: 30px;">HALLOWEEN SALE!</span></div>
</div>
<div class="so-hn-2">
<div class="so-hn-3">Buy ONE more at</div>
<div class="so-hn-4">25% OFF</div>
</div>
<div class="so-hn-5">Limited stock. Hurry NOW.</div>
<div class="so_accept"><a href="[so_acceptlink]">Yes, Add to Cart</a></div>
<div class="so_skip"><a href="[so_skiplink]">No, Skip this offer</a></div>
</div>',
						'post_title'   => '[Ready Offer Design 5] Halloween with BOGO',
						'post_name'    => 'ready-offer-design-5-halloween-with-bogo',
						'post_status'  => 'publish',
						'post_type'    => 'smart_offers',
						'post_excerpt' => '',
					),
					'postmeta' => array(
						'offer_title'      => '[Ready Offer Design 5] Halloween with BOGO',
						'discount_type'    => 'percent_discount',
						'offer_price'      => '25',
						'so_show_offer_as' => 'offer_as_popup',
						'so_custom_css'    => "@import url('https://fonts.googleapis.com/css?family=Amatic+SC');
.so-popup #so_this_offer {
	background: none !important;
}
#so_this_offer .so-halloween {
	background-image:url('" . SO_PRE_URL . "assets/images/halloween-background.jpg');
	background-repeat: no-repeat;
	background-position: center;
	background-size: cover;
	max-width: 900px;
	margin: 0px auto;
	padding: 1em;
}
#so_this_offer .so-headline {
	margin-top: 1em;
	margin-bottom: 1em;
	text-align: center;
	-ms-transform: translate3d(0,0,0) rotate(-2deg);
	-moz-transform: translate3d(0,0,0) rotate(-2deg);
	-webkit-transform: translate3d(0,0,0) rotate(-2deg);
	-o-transform: translate3d(0,0,0) rotate(-2deg);
	transform: translate3d(0,0,0) rotate(-2deg);
}
#so_this_offer .so-hn-headline {
	background-color: rgb(1, 1, 10);
	border-top-color: rgb(1, 1, 10);
	line-height: 20px;
	font-size: 20px;
	font-weight: 700;
	padding: 15px;
	display: inline-block !important;
	margin: 0 auto 15px;
}
#so_this_offer .so-hn-2 {
	font-family: impact;
	color: white;
	text-align: left;
	margin-left: 4em;
}
#so_this_offer .so-hn-3 {
	font-size: 32px;
}
#so_this_offer .so-hn-4 {
	font-size: 68px;
	font-weight: 600;
	margin-top: -20px;
}
#so_this_offer .so-hn-5 {
	font-family: raleway;
	font-size: 15px;
	color: white;
	text-align: left;
	padding: 0.3em 0.8em 1.3em 0.8em;
	margin-left: 3.6em;
	margin-top: -1em;
}
#so_this_offer .so_accept {
	background: #e58800 !important;
	border: 2px solid #e58800 !important;
	font-size: 1.2em !important;
	width: 20% !important;
	padding: 0.6em !important;
	margin-left: 3.4em !important;
	border-radius: unset !important;
}
#so_this_offer div.so_accept a {
	color: #000000 !important;
}
#so_this_offer .so_skip {
	margin-bottom: 1em !important;
	margin-left: 6.4em !important;
	margin-top: 0.3em !important;
	text-align: left !important;
}
#so_this_offer div.so_skip a {
	color: #FFFFFF;
	font-size: 12px;
}
@media only screen and (max-width: 768px) {
	#so_this_offer .so_accept {
		width: 60% !important;
		margin-left: unset !important;
	}
	#so_this_offer .so-hn-2, .so-hn-5, .so_skip {
		margin-left: unset !important;
	}
}
@media only screen and (max-width: 600px) {
	#so_this_offer .so_accept {
		width: 75% !important;
		margin-left: unset !important;
	}
	#so_this_offer .so-hn-2, .so-hn-5, .so_skip {
		margin-left: unset !important;
	}
}
@media only screen and (max-width: 425px) {
	#so_this_offer .so_accept {
		width: 90% !important;
		margin-left: unset !important;
	}
	#so_this_offer .so-hn-2, .so-hn-5, .so_skip {
		margin-left: unset !important;
	}
}
@media only screen and (max-width: 320px) {
	#so_this_offer .so_accept {
		width: 100% !important;
		margin-left: unset !important;
	}
	#so_this_offer .so-hn-2, .so-hn-5, .so_skip {
		margin-left: unset !important;
	}
}",
					),
				),
			);

			return $offer_content;

		}

		public static function import_smart_offers( $args = array() ) {
			if ( empty( $args ) ) {
				return;
			}

			foreach ( $args as $arg ) {
				$post_id = wp_insert_post( $arg['post'] );
				foreach ( $arg['postmeta'] as $meta_key => $meta_value ) {
					update_post_meta( $post_id, $meta_key, $meta_value );
				}
			}
		}

		function init() {

			if ( ( ! empty( $_GET['page'] ) ) && ( $_GET['page'] == 'so-about' || $_GET['page'] == 'so-shortcode' || $_GET['page'] == 'so-faqs' ) && ( ! empty( $_GET['action'] ) ) && ( $_GET['action'] == 'so-import' ) ) {
				$args = $this->get_sample_offers();
				$this->import_smart_offers( $args );
				// update_option( 'smart_offers_sample_data_imported', 'yes', 'no' );	// Older option for older sample offers.
				update_option( 'smart_offers_ready_designs_imported', 'yes', 'no' );
				wp_redirect( admin_url( 'edit.php?post_type=smart_offers' ) );
				exit;
			}

		}
	}

	return new SO_Admin_Ready_Offer_Designs();

}
