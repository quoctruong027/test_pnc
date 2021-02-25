<?php

class BWFAN_Pro_Admin {

	private static $ins = null;
	public $admin_path;
	public $admin_url;

	private function __construct() {
		$this->admin_path = BWFAN_PRO_PLUGIN_DIR . '/admin';
		$this->admin_url  = BWFAN_PRO_PLUGIN_URL . '/admin';

		add_filter( 'bwfan_load_external_autonami_page_template', [ $this, 'load_batch_processing' ] );
		add_action( 'wp_ajax_bwf_sync_automation', [ $this, 'bwfan_sync_automation' ] );

		add_action( 'bwfan_wp_sendemail_setting_html', [ $this, 'utm_fields' ] );

		add_filter( 'bwfan_automation_global_js_data', [ $this, 'language_settings' ] );

		add_action( 'admin_head', [ $this, 'spinner_gif' ] );
	}

	public static function get_instance() {
		if ( is_null( self::$ins ) ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function load_batch_processing( $template ) {
		$template = [];
		//phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['tab'] ) && 'batch_process' === sanitize_text_field( $_GET['tab'] ) ) {

			if ( isset( $_GET['sub_section'] ) && 'history_sync' === sanitize_text_field( $_GET['sub_section'] ) && isset( $_GET['action'] ) && 'add_new' === sanitize_text_field( $_GET['action'] ) ) {
				$template[] = $this->admin_path . '/views/batch-process-add-new.php';
			} else {
				$template[] = $this->admin_path . '/includes/class-bwfan-sync-table.php';
				$template[] = $this->admin_path . '/views/batch-process.php';
			}
		}

		//phpcs:enable WordPress.Security.NonceVerification
		return $template;
	}

	/**
	 * Runs when sync button is clicked from in batch process
	 */
	public function bwfan_sync_automation() {
		BWFAN_Common::check_nonce();
		$event = isset( $_POST['event'] ) ? $_POST['event'] : ''; //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		if ( empty( $event ) ) {
			return;
		}
		$instance = BWFAN_Core()->sources->get_event( $event );

		if ( ! $instance instanceof BWFAN_Event ) {
			return;
		}

		$instance->load_hooks();
		$instance->process_sync();
	}

	public function utm_fields( $event ) {
		include __DIR__ . '/views/utm_fields.php';
	}

	/**
	 *  adding language options in case any of the language plugin like wpml, polylang,            translatepress activated
	 */

	public function language_settings( $settings ) {

		$automation_global_events_js_data['enable_lang'] = 0;
		$language_options                                = [];

		/** in case of wpml */
		if ( function_exists( 'icl_get_languages' ) ) {
			$languages = icl_get_languages();
			if ( ! empty( $languages ) ) {
				foreach ( $languages as $language ) {
					$language_options[ $language['language_code'] ] = ! empty( $language['translated_name'] ) ? $language['translated_name'] : $language['native_name'];
				}
			}
		}

		/** in case of polylang */
		if ( function_exists( 'pll_the_languages' ) ) {
			$languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
			if ( ! empty( $languages ) ) {
				foreach ( $languages as $language ) {
					$language_options[ $language['slug'] ] = $language['name'];
				}
			}
		}


		/** in case of translatepress **/
		if ( bwfan_is_translatepress_active() ) {

			$trp                 = TRP_Translate_Press::get_trp_instance();
			$trp_languages       = $trp->get_component( 'languages' );
			$trp_languages_array = $trp_languages->get_languages( 'english_name' );

			$languages = ! empty( get_option( 'trp_settings' ) ) ? get_option( 'trp_settings' ) : array();

			$languages = isset( $languages['translation-languages'] ) ? $languages['translation-languages'] : array();
			if ( ! empty( $languages ) ) {
				foreach ( $languages as $language ) {
					$language_options[ $language ] = $language;
				}
			}

			$language_options = array_intersect_key( $trp_languages_array, $language_options );
		}

		if ( count( $language_options ) > 1 ) {
			$settings['enable_lang']  = 1;
			$settings['lang_options'] = $language_options;
		}

		return $settings;
	}

	public function spinner_gif() {
		ob_start();
		?>
        <style>
            .bwfan_btn_spin_blue {
                opacity: 1!important;
                position: relative;
                color: rgba(0, 163, 161, .05) !important;
                pointer-events: none!important;
            }
            .bwfan_btn_spin_blue::-moz-selection {
                color: rgba(0, 163, 161, .05) !important;
            }

            .bwfan_btn_spin_blue::selection {
                color: rgba(0, 163, 161, .05) !important;
            }
            .bwfan_btn_spin_blue:after {
                animation: bwfan_spin .5s infinite linear;
                border: 2px solid #0071a1;
                border-radius: 50%;
                border-right-color: transparent !important;
                border-top-color: transparent !important;
                content: "";
                display: block;
                width: 12px;
                height: 12px;
                top: 50%;
                left: 50%;
                margin-top: -8px;
                margin-left: -8px;
                position: absolute;
            }
            a.bwfan-add-repeater-data i {
                font-size: 16px;
                width: 16px;
                height: 16px;
                color: #444;
            }
        </style>
		<?php
		echo ob_get_clean();
	}

}

BWFAN_Pro_Admin::get_instance();
