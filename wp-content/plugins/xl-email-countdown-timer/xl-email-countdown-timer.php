<?php
/**
 * Plugin Name: Finale Email Countdown Timer
 * Plugin URI: https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/
 * Description: A Finale Addon which lets you generate images of countdown timers so that you can embed them in your email campaigns
 * Version: 1.2.0
 * Author: XLPlugins
 * Author URI: https://www.xlplugins.com
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path:/languages
 * XL: True
 * XLTOOLS: True
 * Requires at least: 4.2.1
 * Tested up to: 5.1.1
 * WC requires at least: 2.6.0
 * WC tested up to: 3.5.7
 * XL: True
 *
 * XL Email Countdown Timer is free software.
 * You can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * XL Email Countdown Timer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with XL Email Countdown Timer. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! function_exists( 'wcct_finale_dependency' ) ) {

	/**
	 * Function to check if wcct_finale pro version is loaded and activated or not?
	 * @return bool True|False
	 */
	function wcct_finale_dependency() {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
		if ( false === file_exists( plugin_dir_path( __DIR__ ) . 'finale-woocommerce-sales-countdown-timer-discount-plugin/finale-woocommerce-sales-countdown-timer-discount-plugin.php' ) ) {
			return false;
		}

		return in_array( 'finale-woocommerce-sales-countdown-timer-discount-plugin/finale-woocommerce-sales-countdown-timer-discount-plugin.php', $active_plugins ) || array_key_exists( 'finale-woocommerce-sales-countdown-timer-discount-plugin/finale-woocommerce-sales-countdown-timer-discount-plugin.php', $active_plugins );
	}
}

class XL_WCET {

	static $template_path;
	private static $instance = null;
	public $preview;
	public $background;
	public $font;
	public $end_timestamp;
	public $timezone = "America/New_York";
	public $path;
	public $xml;
	public $expired;
	public $pl_url;
	private $allowed_timerGif = [ "AnotnioBold", "AnotnioLight", "AvenirWhite", "Francois One", "Old Stamper", "android" ];
	private $allowed_expiredGif = [ "image_2", "image_3", "image_4", "image_5" ];

	public function __construct() {


		/**
		 * Handling for the GD Extension, If found missing then terminate
		 */
		if ( ! function_exists( 'imagecreatefrompng' ) ) {

			add_action( 'admin_notices', array( $this, 'wcet_dependecy_missing' ) );

			return false;

		}
		add_action( 'add_meta_boxes', array( $this, 'add_timer_gif_meta_boxes' ), 10, 2 );


		if ( isset( $_GET["post"] ) && $_GET["post"] > 0 ) {
			add_action( "admin_footer", array( $this, "previewCode" ) );
		}
		add_action( "wp_ajax_counter_timer_gif_preview", array( $this, "counter_timer_gif_preview" ) );

		add_action( "wp", array( $this, "init" ), 20 );

		$this->define_plugin_properties();

		include_once plugin_dir_path( WCET_PLUGIN_FILE ) . "WCET_EDD_License_Handler.php";
		/**
		 * Initiates and loads XL start file
		 */
		$this->include_commons();

		add_filter( "wcct_listing_modify_appearance_column", array( $this, "wcct_listing_modify_appearance_column" ), 10, 3 );
	}

	public function define_plugin_properties() {
		/******** DEFINING CONSTANTS **********/

		define( "WCET_VERSION", "1.2.0" );
		define( "WCET_SLUG", "xl-email-countdown-timer" );
		define( "WCET_FULL_NAME", "XL Email Countdown Timer" );
		define( "WCET_PLUGIN_FILE", __FILE__ );
		define( "WCET_PLUGIN_DIR", __DIR__ );
		define( "WCET_PLUGIN_BASENAME", plugin_basename( __FILE__ ) );
		define( "WCET_PURCHASE", 'xlplugin' );
		define( "WCET_SHORT_SLUG", 'wcct' );
	}

	public function include_commons() {
		require 'wcet-xl-support.php';
	}

	public static function get_instance() {
		if ( self::$instance != null ) {
			return self::$instance;
		}
		self::$instance = new counter_timer_genrator_gif();

		return self::$instance;
	}

	public function init() {
		if ( isset( $_GET["campaign_hash_id"] ) && $_GET["campaign_hash_id"] != "" ) {
			$campaign_hash_id = $_GET["campaign_hash_id"];
			$this->get_email_timer_image( $campaign_hash_id );
			do_action( 'email_timer_image_loaded', $campaign_hash_id );
			exit();
		}
	}

	public function get_email_timer_image( $campaign_hash_id ) {
		$this->timezone = WCCT_Common::wc_timezone_string();

		$mainArra = array( "time_image" => "select_timers", "expired_images" => "expired_banner", "end_date" => "end_date", "end_time" => "" );
		global $wpdb;
		$found_campaign = $wpdb->get_results( "select * from {$wpdb->prefix}postmeta where meta_key='campaign_hash_id' &&  meta_value='{$campaign_hash_id}' ", ARRAY_A );

		if ( count( $found_campaign ) > 0 ) {
			$post_id   = $found_campaign[0]["post_id"];
			$cache_key = "wcct_countdown_meta_" . $post_id;
			wp_cache_delete( $cache_key, "wcct_countdown_data" );
			$campaign_data = WCCT_Common::get_item_data( $post_id );
			$timings       = WCCT_Common::start_end_timestamp( $campaign_data );
			$timings       = apply_filters( 'xl_email_countdown_timer_campaign_timings', $timings, $post_id, $campaign_data );
			extract( $timings );
			$available = array( "time_image", "expired_images", "end_date", "end_time", "counter_timer_gif_width", "width" );

			if ( $todayDate >= $start_date_timestamp && $todayDate < $end_date_timestamp ) {
				if ( $post_id > 0 ) {
					if ( ! is_admin() ) {
						if ( $end_date_timestamp > 0 ) {
							$config["end_timestamp"] = $end_date_timestamp;
							$config["todayDate"]     = $todayDate;
						}
					}
					foreach ( $mainArra as $key => $val ) {
						$meta_val = get_post_meta( $post_id, $key, true );
						if ( $meta_val != "" ) {
							$config[ $val ] = get_post_meta( $post_id, $key, true );
						} else {
							unset( $config[ $val ] );
						}
					}

					if ( $_SERVER["SERVER_NAME"] != "localhost" && isset( $_SERVER["HTTP_REFERER"] ) ) {
						$remote_addr = $_SERVER["REMOTE_ADDR"];
						$track_data  = array( "ip" => $remote_addr, "refrer" => $_SERVER["HTTP_REFERER"] );
						$ipHash      = md5( $remote_addr );
						update_post_meta( $post_id, "email_tracker_{$ipHash}", $track_data );
					}
				}
			} else {

				$time_image     = get_post_meta( $post_id, "time_image", true );
				$expired_images = get_post_meta( $post_id, "expired_images", true );
				$config         = array(
					"end_timestamp"  => "0098156200",
					"todayDate"      => $todayDate,
					"select_timers"  => $time_image,
					"expired_banner" => $expired_images
				);
			}

			$this->prepareData( $config )->render();
		}
	}

	function render() {
		include __DIR__ . '/includes/AnimatedGif.php';
		$color  = $this->hex2rgb();
		$frames = array();
		$delays = array();
		$image  = imagecreatefrompng( $this->background );
		$delay  = 100; // milliseconds
		$font   = array(
			'size'     => (int) $this->xml->font->size,
			'angle'    => 0,
			'x-offset' => (int) $this->xml->font->x,
			'y-offset' => (int) $this->xml->font->y,
			'file'     => $this->font,
			'color'    => imagecolorallocate( $image, $color["r"], $color["g"], $color["b"] ),
		);

		$ft_date = new DateTime();
		$ft_date->setTimestamp( $this->end_timestamp );
		$now_date = new DateTime();
		$now_date->setTimestamp( $this->todayDate );

		for ( $i = 0; $i <= 60; $i ++ ) {
			$interval = date_diff( $ft_date, $now_date );
			if ( $this->end_timestamp < $this->todayDate ) {
				$image = imagecreatefrompng( $this->expired_banner );
				$text  = $interval->format( (string) $this->xml->font->end_format );
				if ( $this->expired && $this->expired->image() != null ) {
					imagecopyresampled( $image, $this->expired->image(), 0, 0, 0, 0, imagesx( $this->expired->image() ), imagesy( $this->expired->image() ), imagesx( $this->expired->image() ), imagesy( $this->expired->image() ) );
				}
				ob_start();
				imagegif( $image );
				$frames[] = ob_get_contents();
				$delays[] = $delay;
				$loops    = 1;
				ob_end_clean();
				break;
			} else {
				$image = imagecreatefrompng( $this->background );
				$text  = str_pad( $interval->format( (string) $this->xml->font->format ), strlen( (string) $this->xml->font->format ), '0', STR_PAD_LEFT );
				imagettftext( $image, $font['size'], $font['angle'], $font['x-offset'], $font['y-offset'], $font['color'], $font['file'], $text );
				ob_start();
				imagegif( $image );
				$frames[] = ob_get_contents();
				$delays[] = $delay;
				$loops    = 1;
				ob_end_clean();
			}
			$now_date->modify( '+1 second' );
		}

		header( 'Expires: Fri, 01 Jan 1999 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );

		$gif = new AnimatedGif( $frames, $delays, $loops );
		$gif->display();
	}

	function hex2rgb() {
		$hex = str_replace( "#", "", (string) $this->xml->font->color );

		if ( strlen( $hex ) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}
		$rgb = array( "r" => $r, "g" => $g, "b" => $b );

		return $rgb;
	}

	public function prepareData( $inputData ) {
		if ( is_array( $inputData ) && count( $inputData ) > 0 ) {
			extract( $inputData );
			$this->end_timestamp  = $end_timestamp;
			$this->todayDate      = $todayDate;
			$this->expired_banner = __DIR__ . "/expired_images/" . $expired_banner . "/expired.png";
			$this->background     = __DIR__ . "/timers/" . $select_timers . "/background.png";
			$this->path           = __DIR__ . "/timers/" . $select_timers;
			if ( $this->path && file_exists( $this->path ) ) {
				$this->xml  = simplexml_load_file( $this->path . '/data.xml' );
				$this->font = __DIR__ . "/timers/" . $select_timers . "/font.ttf";
			}
		}

		return $this;
	}

	public function add_timer_gif_meta_boxes() {
		if ( isset( $_GET["post"] ) && $_GET["post"] > 0 ) {
			add_meta_box( 'counter_timer_genrator_gif', __( 'Timer Embed Code' ), array( $this, 'render_email_timer_meta_box' ), 'wcct_countdown', 'side', 'default' );
		}
	}

	function render_email_timer_meta_box() {
		$post_id           = (int) $_GET["post"];
		$campaign_hash_id  = get_post_meta( $post_id, "campaign_hash_id", true );
		$email_timer_width = get_post_meta( $post_id, "email_timer_width", true );
		$email_timer_width = ( isset( $email_timer_width ) && $email_timer_width > 0 ) ? $email_timer_width : 300;
		$image_url         = add_query_arg( array( "campaign_hash_id" => $campaign_hash_id ), site_url() );
		$image             = "<img src='{$image_url}' style='width:{$email_timer_width}px'>";
		$image             = htmlentities( $image );

		?>
        <div class="email_timer_meta_box">
			<?php
			do_action( "render_email_timer_meta_box", $post_id );
			?>

			<?php if ( ! $campaign_hash_id ) { ?>
                <span class="init_text">Click below button to generate email timer embed code.</span>
			<?php } ?>

            <div class="success_timer" style="<?php echo ( isset( $campaign_hash_id ) && $campaign_hash_id != "" ) ? '' : 'display:none;'; ?> padding-bottom: 10px;"> Here is the embed code, you can
                use it in your emails.
            </div>
            <textarea rows="4" class='email_timer_gif_url' readonly="" onclick='this.select()'
                      style="<?php echo ( isset( $campaign_hash_id ) && $campaign_hash_id != "" ) ? '' : 'display:none;'; ?>width: 100%;"><?php echo $image; ?></textarea>
            <br/>

            <span class="email_timer_error"></span>
            <br/>
            <input type="button" style="float: right" class="email_timer_meta_box_button button button-primary button-large"
                   value="Generate Timer" onclick="wcct_show_tb('Generate Timer', 'email_openModal');">
            <div style="clear: both"></div>
        </div>
		<?php
	}

	public function counter_timer_gif_preview() {
		extract( $_POST );
		$data = $_POST;
		$this->timer_save_setings( $campaign_id, $data );
		$campaign_id_hash  = md5( $campaign_id );
		$image_url         = add_query_arg( array( "campaign_hash_id" => $campaign_id_hash ), site_url() );
		$image_url         = apply_filters( 'xl_email_countdown_timer_image_url', $image_url );
		$email_timer_width = get_post_meta( $campaign_id, "email_timer_width", true );
		$email_timer_width = ( isset( $email_timer_width ) && $email_timer_width > 0 ) ? $email_timer_width : 300;
		ob_start();

		$image        = "<img src='{$image_url}' style='width:{$email_timer_width}px'>";
		$imageWithout = $image;
		$image        = htmlentities( $image );
		?>
        <span class="spanCopier"><span><?php echo $image; ?></span></span>
		<?php
		$image_html = ob_get_clean();
		exit( wp_json_encode( array( "image_url" => $image_url, "image_html" => $image_html, "image_without" => $imageWithout ) ) );
	}

	public function timer_save_setings( $post_id, $data ) {
		if ( $post_id > 0 ) {
			$campaign_data = WCCT_Common::get_item_data( $post_id );
			$timings       = WCCT_Common::start_end_timestamp( $campaign_data );
			extract( $timings );
			$available = array( "time_image", "expired_images", "end_date", "end_time", "counter_timer_gif_width", "email_timer_width" );
			if ( $todayDate >= $start_date_timestamp && $todayDate < $end_date_timestamp ) {
				if ( count( $timings ) > 0 && $timings["end_date_timestamp"] > 0 ) {
					$data["end_timestamp"] = isset( $end_timestamp ) ? $end_timestamp : 0;
				}
				$output      = __( 'Running', WCCT_SLUG );
				$slug_timing = "running";
			} else {
				$data = array(
					"end_timestamp"  => "0098156200",
					"time_image"     => $data["time_image"],
					"expired_images" => $data["expired_images"]
				);
			}
			foreach ( $available as $val ) {
				if ( isset( $data[ $val ] ) && $data[ $val ] != "" ) {
					update_post_meta( $post_id, $val, $_POST[ $val ] );
				} else {
					update_post_meta( $post_id, $val, "" );
				}
			}
			update_post_meta( $post_id, "campaign_hash_id", md5( $post_id ) );
		}
	}

	public function previewCode() {
		?>
        <style>.email_timer_close {
                background: #606061;
                color: #FFF;
                line-height: 25px;
                position: absolute;
                right: -12px;
                text-align: center;
                top: -10px;
                width: 24px;
                text-decoration: none;
                font-weight: 700;
                -webkit-border-radius: 12px;
                -moz-border-radius: 12px;
                border-radius: 12px;
                -moz-box-shadow: 1px 1px 3px #000;
                -webkit-box-shadow: 1px 1px 3px #000;
                box-shadow: 1px 1px 3px #000
            }

            .email_timer_close:hover {
                background: #00d9ff
            }

            .email_timer_modalDialog {
                position: fixed;
                font-family: Arial, Helvetica, sans-serif;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                background: rgba(0, 0, 0, .8);
                z-index: 99999;
                opacity: 0;
                -webkit-transition: opacity .4s ease-in;
                -moz-transition: opacity .4s ease-in;
                transition: opacity .4s ease-in;
                pointer-events: none
            }

            .email_timer_modalDialog_show {
                opacity: 1;
                pointer-events: auto
            }

            .email_timer_modalDialog > div {
                width: 60%;
                position: relative;
                margin: 4% auto;
                padding: 5px 20px 13px;
                border-radius: 10px;
                background: #fff;
                min-height: 300px;
                text-align: centre
            }

            .email_timer_prealoder, .email_timer_prealoder .spinner {
                position: absolute;
                top: 0;
                right: 0;
                left: 0;
                bottom: 0;
                margin: auto
            }

            .email_timer_prealoder {
                text-align: center;
                background: #fcfcfc
            }

            .counter_timer_gif_response {
                display: none;
                text-align: center;
                padding-top: 50px
            }

            .counter_timer_gif_response .spanCopier {
                border: 5px dotted red;
                padding: 20px 10px;
                font-size: 18px;
                display: inline-block;
                background: #efefef;
                width: 600px
            }

            .counter_timer_gif_response .highlightCopier span {
                background: #00bfff;
                color: #fff
            }

            .email_timer_gif_url {
                font-size: 12px
            } </style>
        <div id="email_openModal" class="email_timer_modalDialog wcct_tb_content" style="display:none;">
			<?php
			$post_id     = (int) $_GET["post"];
			$timer_image = get_post_meta( $post_id, "time_image", true );
			if ( $timer_image == false ) {
				$timer_image = "AnotnioBold";
			}
			$expired_images = get_post_meta( $post_id, "expired_images", true );
			if ( $expired_images == false ) {
				$expired_images = "image_2";
			}
			$email_timer_width = get_post_meta( $post_id, "email_timer_width", true );
			?>
            <div class="counter_timer_genrator_gif_setting">
                <div class="email_timer_prealoder" style="display:none">
                    <span class="spinner"></span>
                </div>
                <div class="email_timer_form_field">
                    <h4>Choose Timer Image</h4>
                    <table border="0" cellpadding="1" cellspacing="1" class="table borderless">
                        <tbody>
                        <tr>
							<?php
							$i = 0;
							foreach ( $this->allowed_timerGif as $val ) {
								?>
                                <td valign="center">
                                    <input type="radio" name="time_image_x" class="time_image"
                                           id='<?php echo "time_image_" . $i ?>'
                                           value="<?php echo $val; ?>" <?php echo ( isset( $timer_image ) && $timer_image == $val ) ? "checked" : '' ?>>
                                </td>
                                <td>
                                    <img
                                            src="<?php echo plugin_dir_url( WCET_PLUGIN_FILE ) ?>/timers/<?php echo $val ?>/preview.png"
                                            style="max-width: 100%" for='<?php echo "time_image_" . $i; ?>'>
                                </td>
								<?php
								$i ++;
							}
							?>
                        </tr>
                        </tbody>
                    </table>
                    <h4>Choose Expired Image</h4>
                    <table border="0" cellpadding="1" cellspacing="1" class="table borderless">
                        <tbody>
                        <tr>
							<?php
							foreach ( $this->allowed_expiredGif as $val ) {
								?>
                                <td valign="center">
                                    <input type="radio" name="expired_images_x" class="expired_images"
                                           value="<?php echo $val; ?>" <?php echo ( isset( $expired_images ) && $expired_images == $val ) ? "checked" : '' ?>>
                                </td>
                                <td>
                                    <img src="<?php echo plugin_dir_url( WCET_PLUGIN_FILE ); ?>/expired_images/<?php echo $val ?>/preview.png" style="max-width: 100%">
                                </td>
								<?php
							}
							?>
                        </tr>
                        </tbody>
                    </table>
                    <input type="hidden" class="email_timer_end_date" value="2017-06-03">
                    <input type="hidden" class="email_timer_end_time" value="22:00">
                    <h4>Enter Width</h4>
                    <input type="text" class="email_timer_width"
                           value="<?php echo ( isset( $email_timer_width ) && $email_timer_width != "" ) ? $email_timer_width : '300' ?>"> in px
                    <br> <br>

					<?php do_action( 'xl_email_countdown_timer_preview_code_fields' ); ?>

                    <centre><input type="button" class="button button-primary button-large"
                                   id="counter_timer_gif_preview" value="Generate Image"></centre>
                </div>
                <div class="counter_timer_gif_response">
                </div>
            </div>
        </div>
        <script>
            jQuery(window).load(function () {
                setTimeout(function () {
                    jQuery("html body").on("change", "#post", function () {
                        jQuery(".email_timer_meta_box_button").attr("disabled", "");
                        jQuery(".email_timer_error").html("<b>kindly save the campaign first then generate the image</b>");
                    });
                }, 3000);

                jQuery("#counter_timer_gif_preview").on("click", function () {
                    var $this = jQuery(this);
                    $this.attr("disabled", "disabled");
                    jQuery(".email_timer_prealoder").show();
                    jQuery(".email_timer_prealoder .spinner").css({"visibility": "visible"});
                    var data = {
                        end_date: jQuery(".email_timer_end_date").val(),
                        end_time: jQuery(".email_timer_end_time").val(),
                        email_timer_width: jQuery(".email_timer_width").val(),
                        time_image: jQuery(".time_image:checked").val(),
                        expired_images: jQuery(".expired_images:checked").val(),
                        campaign_id: <?php echo $_GET["post"]; ?>,
						<?php do_action( 'xlect_add_post_data_in_counter_timer_gif_preview' ); ?>
                        action: "counter_timer_gif_preview"
                    }
                    jQuery(".email_timer_form_field").hide();
                    jQuery(".counter_timer_gif_response").hide();
                    jQuery.ajax({
                        method: "post",
                        url: "admin-ajax.php",
                        data: data,
                        success: function (resp) {
                            var nrsp = JSON.parse(resp);
                            if (nrsp.hasOwnProperty("image_url") == true) {
                                var image = new Image();
                                image.src = nrsp.image_url;
                                image.onload = function () {
                                    $this.removeAttr("disabled", "disabled");
                                    jQuery(".counter_timer_gif_response").show();
                                    jQuery(".email_timer_prealoder").hide();
                                    jQuery(".email_timer_prealoder .spinner").css({"visibility": "hidden"});
                                    jQuery(".counter_timer_gif_response").html("");
                                    jQuery(".counter_timer_gif_response").append("<h4>Preview Timer</h4>");
                                    jQuery(".counter_timer_gif_response").append(image);
                                    jQuery(".counter_timer_gif_response").append("<h4>Copy and paste below code in your email to generate Timer</h4>");
                                    jQuery(".counter_timer_gif_response").append(nrsp.image_html);
                                    jQuery(".email_timer_gif_url").val(nrsp.image_without);
                                    jQuery(".email_timer_gif_url").removeClass("highlightCopier");
                                    jQuery(".email_timer_gif_url").show();
                                    jQuery(".success_timer").show();
                                    jQuery(".init_text").hide();
                                    jQuery(".email_timer_meta_box_button").val("Rebuilt Timer");
                                }
                            }
                        }
                    });
                });
                jQuery(".email_timer_close").on("click", function (e) {
                    e.preventDefault();
                    jQuery(".email_timer_modalDialog").removeClass("email_timer_modalDialog_show");
                });


                jQuery(".email_timer_meta_box_button").on("click", function (e) {
                    e.preventDefault();
                    jQuery(".email_timer_form_field").show();
                    jQuery(".counter_timer_gif_response").hide();
                });
                jQuery("html body").on("click", ".spanCopier", function () {
                    var elem = jQuery(this)[0];
                    jQuery(this).addClass("highlightCopier");
                    copyToClipboard(elem);

                });
                jQuery("html body").on("click", ".email_timer_gif_url", function () {
                    var elem = jQuery(this)[0];
                    jQuery(this).addClass("highlightCopier");
                    copyToClipboard(elem);

                });

                function copyToClipboard(elem) {
                    var targetId = "_hiddenCopyText_";
                    var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
                    var origSelectionStart, origSelectionEnd;
                    if (isInput) {
                        target = elem;
                        origSelectionStart = elem.selectionStart;
                        origSelectionEnd = elem.selectionEnd;
                    } else {
                        target = document.getElementById(targetId);
                        if (!target) {
                            var target = document.createElement("textarea");
                            target.style.position = "absolute";
                            target.style.left = "-9999px";
                            target.style.top = "0";
                            target.id = targetId;
                            document.body.appendChild(target);
                        }
                        target.textContent = elem.textContent;
                    }
                    var currentFocus = document.activeElement;
                    target.focus();
                    target.setSelectionRange(0, target.value.length);
                    var succeed;
                    try {
                        succeed = document.execCommand("copy");
                    } catch (e) {
                        succeed = false;
                    }
                    if (currentFocus && typeof currentFocus.focus === "function") {
                        currentFocus.focus();
                    }

                    if (isInput) {
                        elem.setSelectionRange(origSelectionStart, origSelectionEnd);
                    } else {
                        target.textContent = "";
                    }
                    return succeed;
                }

            });
        </script>
		<?php
	}


	public function wcet_dependecy_missing() {
		?>
        <div class="error">
            <p><?php _e( "XL Email Countdown Timer: PHP:GD Extension is missing.", WCET_SLUG ); ?></p>
        </div>
		<?php
	}

	public function wcct_listing_modify_appearance_column( $output, $item, $data ) {
		if ( ! in_array( 'Email Timer', $output ) ) {
			$campaign_hash_id = get_post_meta( (int) $item["id"], 'campaign_hash_id', true );
			if ( ! empty( $campaign_hash_id ) ) {
				$output[] = __( 'Email Timer', WCCT_SLUG );
			}
		}

		return $output;
	}

}

if ( class_exists( 'woocommerce' ) && wcct_finale_dependency() ) {
	new XL_WCET();
}