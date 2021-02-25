<?php

class BWFAN_LD_Quiz_Selected_Answer extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'ld_quiz_selected_answer';
		$this->tag_description = __( 'Quiz\'s selected answer', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_ld_quiz_selected_answer', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Show the html in popup for the merge tag.
	 */
	public function get_view() {
		$this->get_back_button();
		$this->get_questions();

		$this->get_preview();
		$this->get_copy_button();
	}

	public function get_questions() {
		?>
		<div class="bwfan-input-form clearfix bwfan_quiz_questions_dropdown">
			<label for="" class="bwfan-label-title"><?php echo esc_html__( 'Select Question', 'wp-marketing-automations' ); ?></label>
			<?php /** Needed this class 'bwfan_ld_quiz_questions' (bwfan_'event_slug') to get the questions loaded into this select by WP BWFAN Filter 'bwfan_all_event_js_data'  */ ?>
			<select id="" class="bwfan-input-wrapper bwfan-mb-15 bwfan_tag_select bwfan_ld_quiz_questions" name="question"></select>
		</div>
		<?php
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

		$quiz_answers = BWFAN_Merge_Tag_Loader::get_data( 'quiz_answers' );

		$answer = '';
		if ( isset( $attr['question'] ) && ! empty( $quiz_answers ) && is_array( $quiz_answers ) ) {
			$answer = $quiz_answers[ absint( $attr['question'] ) ];
		}

		return $this->parse_shortcode_output( $answer, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 *
	 * @todo:Hard values shouldn't be passed
	 */
	public function get_dummy_preview() {
		return 'Dummy Answer to the Question';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_learndash_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'learndash_quiz', 'BWFAN_LD_Quiz_Selected_Answer' );
}
