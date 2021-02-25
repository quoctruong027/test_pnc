<?php

class BWFAN_LD_Topic_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'ld_topic_name';
		$this->tag_description = __( 'Topic Name', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_ld_topic_name', array( $this, 'parse_shortcode' ) );
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

		$topic_id = BWFAN_Merge_Tag_Loader::get_data( 'topic_id' );

		if ( empty( $topic_id ) ) {
			$quiz_id   = BWFAN_Merge_Tag_Loader::get_data( 'quiz_id' );
			$topic_id = ! empty( $quiz_id ) ? get_post_meta( $quiz_id, 'topic_id', true ) : 0;
		}

		$topic_name = ! empty( $topic_id ) ? esc_html__( get_the_title( $topic_id ), 'autonami-automations-pro' ) : '';

		return $this->parse_shortcode_output( $topic_name, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 *
	 * @todo:Hard values shouldn't be passed
	 */
	public function get_dummy_preview() {
		return 'Dummy LearnDash Topic Name';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_learndash_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'learndash_topic', 'BWFAN_LD_Topic_Name' );
	BWFAN_Merge_Tag_Loader::register( 'learndash_quiz', 'BWFAN_LD_Topic_Name' );
}
