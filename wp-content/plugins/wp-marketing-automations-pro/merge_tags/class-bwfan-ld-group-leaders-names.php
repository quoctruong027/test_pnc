<?php

class BWFAN_LD_Group_Leaders extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'ld_group_leaders';
		$this->tag_description = __( 'Group Leader(s)\'s Names', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_ld_group_leaders', array( $this, 'parse_shortcode' ) );
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

		$group_id = absint( BWFAN_Merge_Tag_Loader::get_data( 'group_id' ) );

		$leaders = BWFAN_Learndash_Common::get_group_leaders_by_group_id( $group_id );

		$leader_names = array();
		if ( ! empty( $leaders ) && is_array( $leaders ) ) {
			foreach ( $leaders as $leader ) {
				$leader_obj = get_user_by( 'ID', absint( $leader['user_id'] ) );

				if ( $leader_obj instanceof WP_User ) {
					$leader_names[] = $leader_obj->display_name;
				}
			}
		}

		$leaders_emails_to = implode( ', ', $leader_names );

		return $this->parse_shortcode_output( $leaders_emails_to, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 *
	 * @todo:Hard values shouldn't be passed
	 */
	public function get_dummy_preview() {
		return 'Leader Name 1, Leader Name 2';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_learndash_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'learndash_group', 'BWFAN_LD_Group_Leaders' );
}
