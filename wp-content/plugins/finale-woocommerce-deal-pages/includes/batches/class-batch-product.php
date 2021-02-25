<?php

/**
 * Posts batch class.
 *
 * @package Xl_Locomotive/Batch
 */

/**
 * Batch Posts class.
 */
class Finale_products_batch_handler extends FinaleBatch {

	/**
	 * The individual batch's parameter for specifying the amount of results to return.
	 *
	 * @var string
	 */
	public $per_batch_param = 'posts_per_page';

	/**
	 * Default args for the query.
	 *
	 * @var array
	 */
	public $default_args = array(
		'post_type'      => 'product',
		'posts_per_page' => 10,
		'offset'         => 0,
	);

	/**
	 * Get results function for the registered batch process.
	 *
	 * @return array \WP_Query->get_posts() result.
	 */
	public function batch_get_results() {

		if ( isset( $_POST['cp_id'] ) ) {
			$cdata                        = array(
				'date'         => time(),
				'action'       => $_POST['cp_action_type'],
				'current_step' => $_POST['step'],
			);
			$options_data                 = get_option( 'wcct-deal-process-action-' . $_POST['cp_id'], $cdata );
			$options_data                 = $cdata;
			$options_data['date']         = time();
			$options_data['current_step'] = $_POST['step'];
			$options_data['action']       = $_POST['cp_action_type'];

		}
		$query       = new WP_Query( $this->args );
		$total_posts = $query->found_posts;
		$this->set_total_num_results( $total_posts );

		return $query->get_posts();
	}

	/**
	 * Clear the result status for a batch.
	 *
	 * @return bool
	 */
	public function batch_clear_result_status() {
		return delete_post_meta_by_key( $this->slug . '_status' );
	}

	/**
	 * Get the status of a result.
	 *
	 * @param \WP_Post $result The result we want to get status of.
	 */
	public function get_result_item_status( $result ) {
		return get_post_meta( $result->ID, $this->slug . '_status', true );
	}

	/**
	 * Update the meta info on a result.
	 *
	 * @param \WP_Post $result The result we want to track meta data on.
	 * @param string $status Status of this result in the batch.
	 */
	public function update_result_item_status( $result, $status ) {
		return update_post_meta( $result->ID, $this->slug . '_status', $status );
	}

}
