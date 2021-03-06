<?php

class BWFAN_Unsubscribe_Link extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'unsubscribe_link';
		$this->tag_description = __( 'Unsubscribe url', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_unsubscribe_link', array( $this, 'parse_shortcode' ) );
		$this->support_fallback = false;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview();
		}

		$global_settings = BWFAN_Common::get_global_settings();
		if ( ! isset( $global_settings['bwfan_unsubscribe_page'] ) || empty( $global_settings['bwfan_unsubscribe_page'] ) ) {
			return '';
		}

		$page      = absint( $global_settings['bwfan_unsubscribe_page'] );
		$page_link = get_permalink( $page );

		$unsubscribe_link = add_query_arg(
			array(
				'bwfan-action' => 'unsubscribe',
			), $page_link
		);

		$skip_name_email = apply_filters( 'bwfan_skip_name_email_from_unsubscribe_link', false );
		if ( false === $skip_name_email ) {
			$name  = BWFAN_Common::decode_merge_tags( '{{customer_first_name}}' );
			$email = BWFAN_Common::decode_merge_tags( '{{customer_email}}' );

			if ( ! empty( $email ) ) {
				$unsubscribe_link = add_query_arg(
					array(
						'subscriber_recipient' => $email,
					), $unsubscribe_link
				);
			}
			if ( ! empty( $name ) ) {
				$unsubscribe_link = add_query_arg(
					array(
						'subscriber_name' => $name,
					), $unsubscribe_link
				);
			}
		}

		$unsubscribe_link = apply_filters( 'bwfan_unsubscribe_link', $unsubscribe_link, $attr );

		return $this->parse_shortcode_output( $unsubscribe_link, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return add_query_arg(
			array(
				'bwfan-action' => 'unsubscribe',
			), home_url()
		);
	}


}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'bwfan_default', 'BWFAN_Unsubscribe_Link' );
