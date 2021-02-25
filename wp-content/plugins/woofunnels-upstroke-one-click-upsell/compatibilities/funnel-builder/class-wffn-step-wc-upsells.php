<?php
defined( 'ABSPATH' ) || exit; //Exit if accessed directly

/**
 * Class contains all the upstroke related funnel functionality
 * Class WFFN_Step_WC_Upsells
 */
if ( !class_exists( 'WFFN_Step_WC_Upsells' ) ) {
	class WFFN_Step_WC_Upsells extends WFFN_Step {

		private static $ins = null;
		public $slug = 'wc_upsells';
		public $list_priority = 30;

		/**
		 * WFFN_Step_WC_Upsells constructor.
		 */
		public function __construct() {
			parent::__construct();
			add_action( 'wfocu_funnels_from_external_base', array( $this, 'maybe_filter_upsells' ) );
			add_filter( 'wfocu_add_control_meta_query', [ $this, 'exclude_from_query' ] );
			add_filter( 'wfocu_session_db_insert_data', array( $this, 'funnel_id_recorded' ) );
			add_filter( 'maybe_setup_funnel_for_breadcrumb', [ $this, 'maybe_funnel_breadcrumb' ] );
			add_filter( 'wfocu_fb_pixel_ids', array( $this, 'override_pixel_key' ) );
			add_filter( 'wfocu_get_ga_key', array( $this, 'override_ga_key' ) );
			add_filter( 'wfocu_get_gad_key', array( $this, 'override_gad_key' ) );
			add_filter( 'wfocu_get_pint_key', array( $this, 'override_pint_key' ) );
			add_filter( 'wfocu_get_conversion_label', array( $this, 'override_conversion_key' ) );

		}

		/**
		 * @return WFFN_Step_WC_Upsells|null
		 */
		public static function get_instance() {
			if ( null === self::$ins ) {
				self::$ins = new self;
			}

			return self::$ins;
		}

		/**
		 * @param $steps
		 *
		 * @return array
		 */
		public function get_step_data() {
			return array(
				'type'        => $this->slug,
				'title'       => $this->get_title(),
				'popup_title' => sprintf( __( 'Add %s', 'funnel-builder' ), $this->get_title() ),
				'dashicons'   => 'dashicons-tag',
				'label_class' => 'bwf-st-c-badge-green',
				'substeps'    => array(),
			);
		}

		/**
		 * Return title of upstroke step
		 */
		public function get_title() {
			return __( 'One Click Upsells', 'funnel-builder' );
		}

		/**
		 * @param $type
		 *
		 * @return array
		 */
		public function get_step_designs( $term ) {
			remove_all_filters( 'wfocu_add_control_meta_query' );
			add_filter( 'wfocu_add_control_meta_query', [ $this, 'search_any_post_status' ], 9 );
			$get_upstroke_posts = WFOCU_Core()->funnels->setup_funnels();
			$get_all_ids        = wp_list_pluck( $get_upstroke_posts, 'id' );

			$get_upstroke_posts = array_map( 'get_post', $get_all_ids );

			$designs = [];
			$data    = [];

			if ( is_array( $get_upstroke_posts ) && count( $get_upstroke_posts ) > 0 ) {

				foreach ( $get_upstroke_posts as $post ) {
					if ( false === strpos( strtolower( $post->post_title ), strtolower( $term ) ) && ! is_numeric( $term ) ) {
						continue;
					}
					$post_type = get_post_type( $post->ID );
					if ( 'cartflows_step' === $post_type ) {
						$meta = get_post_meta( $post->ID, 'wcf-step-type', true );
						if ( 'upsell' === $meta ) {
							$data = array(
								'id'   => $post->ID,
								'name' => $post->post_title . ' (#' . $post->ID . ')',
							);
						}
					} else {
						$data = array(
							'id'   => $post->ID,
							'name' => $post->post_title . ' (#' . $post->ID . ')',
						);

					}

					if ( is_numeric( $term ) && intval( $term ) === $post->ID ) {
						$designs   = [];
						$designs[] = $data;
						break;
					} else {
						$designs[] = $data;
					}
				}
			}

			return $designs;
		}

		public function exclude_ab_variant_from_query( $existing_args ) {
			$existing_args                 = is_array( $existing_args ) ? $existing_args : [];
			$existing_args['get_existing'] = true;

			return $existing_args;
		}

		public function search_any_post_status( $existing_args ) {
			$existing_args                = is_array( $existing_args ) ? $existing_args : [];
			$existing_args['post_type']   = array( WFOCU_Common::get_funnel_post_type_slug(), 'cartflows_step', 'page' );
			$existing_args['post_status'] = 'any';

			return $existing_args;
		}


		/**
		 * @param $funnel_id
		 * @param $type
		 * @param $posted_data
		 *
		 * @return stdClass
		 */
		public function add_step( $funnel_id, $posted_data ) {
			$title = isset( $posted_data['title'] ) ? $posted_data['title'] : '';

			$funnel_to_create = array(
				'title'           => $title,
				'offers'          => array(),
				'status'          => 'publish',
				'offers_override' => array(
					0 => array( 'meta_override' => array( '_wfocu_setting_override' => array( 'template' => 'sp-classic' ) ) ),
				),
			);

			$step_id           = WFOCU_Core()->funnels->generate_preset_funnel_data( $funnel_to_create );
			$posted_data['id'] = ( $step_id > 0 ) ? $step_id : 0;

			return parent::add_step( $funnel_id, $posted_data );
		}

		/**
		 * @param $funnel_id
		 * @param $upsell_step_id
		 * @param $type
		 * @param $posted_data
		 *
		 * @return stdClass
		 */
		public function duplicate_step( $funnel_id, $upsell_step_id, $posted_data ) {
			global $wpdb;
			$duplicate_upsell_id = 0;
			if ( $upsell_step_id > 0 && $funnel_id > 0 ) {
				$offers    = array();
				$post_type = get_post_type( $upsell_step_id );

				$post_status = ( isset( $posted_data['original_id'] ) && $posted_data['original_id'] > 0 ) ? get_post_status( $posted_data['original_id'] ) : 'publish';


				if ( 'cartflows_step' === $post_type ) {
					$exclude_metas = array(
						'cartflows_imported_step',
						'enable-to-import',
						'site-sidebar-layout',
						'site-content-layout',
						'theme-transparent-header-meta',
						'_uabb_lite_converted',
						'_astra_content_layout_flag',
						'site-post-title',
						'ast-title-bar-display',
						'ast-featured-img',
						'_thumbnail_id',
					);

					$meta_selects = array();

					$post_meta_all = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$upsell_step_id" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

					if ( is_array( $post_meta_all ) && count( $post_meta_all ) > 0 ) {
						$meta_selects[] = (object) [ 'meta_key' => '_offer_type', 'meta_value' => 'upsell' ];
						foreach ( $post_meta_all as $meta_info ) {
							$meta_key   = $meta_info->meta_key;
							$meta_value = $meta_info->meta_value;
							if ( ! in_array( $meta_key, $exclude_metas, true ) ) {
								if ( ( strpos( $meta_key, 'wcf-' ) === false ) ) {
									if ( $meta_key === '_wp_page_template' ) {
										$meta_value = ( strpos( $meta_value, 'cartflows' ) !== false ) ? str_replace( 'cartflows', "wfocu", $meta_value ) : $meta_value;
									}
									$meta_selects[] = (object) [ 'meta_key' => $meta_key, 'meta_value' => $meta_value ];

								}
							}
						}


					}

					$offers[] = array(
						'id'               => $upsell_step_id,
						'name'             => get_the_title( $upsell_step_id ) . __( ' Copy', 'funnel-builder' ),
						'type'             => 'upsell',
						'state'            => 0,
						'meta'             => array(
							'_wfocu_is_rules_saved'   => 'yes',
							'_wfocu_rules'            => array(),
							'_funnel_steps'           => array(),
							'_funnel_upsell_downsell' => array(),
						),
						'parent_meta'      => $meta_selects,
						'_customizer_data' => get_option( 'wfocu_c_' . $upsell_step_id, '' ),
						'post_content'     => get_post_field( 'post_content', $upsell_step_id ),
					);

					$funnel_to_create = array(
						'title'       => get_the_title( $upsell_step_id ) . ' Copy',
						'description' => get_post_field( 'post_content', $upsell_step_id ),
						'status'      => $post_status,
						'priority'    => WFOCU_Common::get_next_funnel_priority(),
						'offers'      => $offers,
						'meta'        => array(
							'_wfocu_is_rules_saved'   => get_post_meta( $upsell_step_id, '_wfocu_is_rules_saved', true ),
							'_wfocu_rules'            => get_post_meta( $upsell_step_id, '_wfocu_rules', true ),
							'_funnel_steps'           => array(),
							'_funnel_upsell_downsell' => array(),
						),
					);

					$duplicate_upsell_id = WFOCU_Core()->funnels->generate_preset_funnel_data( $funnel_to_create );

					if ( $duplicate_upsell_id > 0 ) {
						$posted_data['id'] = $duplicate_upsell_id;
						$funnel_settings   = get_post_meta( $upsell_step_id, '_wfocu_settings', true );
						$funnel_settings   = is_array( $funnel_settings ) ? $funnel_settings : array();

						$funnel_steps = get_post_meta( $duplicate_upsell_id, '_funnel_steps', true );

						if ( is_array( $funnel_steps ) && count( $funnel_steps ) > 0 ) {
							foreach ( $funnel_steps as $step ) {
								$post_template = get_post_meta( $step['id'], '_funnel_steps', true );
								if ( strpos( $post_template, 'canvas' ) !== false || strpos( $post_template, 'boxed' ) !== false ) {
									update_post_meta( $step['id'], '_wp_page_template', $post_template . '.php' );
								}
							}
						}

						$funnel_priority_new                = WFOCU_Common::get_next_funnel_priority();
						$funnel_settings['funnel_priority'] = $funnel_priority_new;

						update_post_meta( $duplicate_upsell_id, '_wfocu_settings', $funnel_settings );
					}
				} else {


					$resp                = WFOCU_AJAX_Controller::duplicating_funnel( $upsell_step_id, array(
						'msg'    => '',
						'status' => true,
					) );
					$duplicate_upsell_id = $resp['duplicate_id'];
					$posted_data['id']   = $duplicate_upsell_id;
				}


			}

			if( isset ( $posted_data['id'] ) && $posted_data['id'] > 0 ){
				$new_title = isset( $posted_data['existing'] ) && isset( $posted_data['title'] ) ? $posted_data['title'] : '';
				if ( ! empty( $new_title ) ) {
					$arr = [ 'ID' => $posted_data['id'], 'post_title' => $new_title ];
					wp_update_post( $arr );
				}
			}

			return parent::duplicate_step( $funnel_id, $upsell_step_id, $posted_data );
		}

		/**
		 * @param $step_id
		 *
		 * @return mixed
		 */
		public function get_entity_edit_link( $step_id ) {
			$link = parent::get_entity_edit_link( $step_id );
			if ( $step_id > 0 && get_post( $step_id ) instanceof WP_Post ) {
				$link = esc_url( BWF_Admin_Breadcrumbs::maybe_add_refs( add_query_arg( array(
					'page'    => 'upstroke',
					'section' => 'offers',
					'edit'    => $step_id,
				), admin_url( 'admin.php' ) ) ) );
			}

			return $link;
		}

		/**
		 * @param $step_id
		 *
		 * @return mixed
		 */
		public function get_entity_view_link( $step_id ) {
			$link = parent::get_entity_view_link( $step_id );
			if ( $step_id > 0 && get_post( $step_id ) instanceof WP_Post ) {
				$steps = WFOCU_Core()->funnels->get_funnel_steps( $step_id );
				$link  = ( is_array( $steps ) && count( $steps ) > 0 ) ? get_permalink( $steps[0]['id'] ) : "";
			}

			return $link;
		}

		public function get_entity_tags( $step_id, $funnel_id ) { //phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
			$funnel_rules = get_post_meta( $step_id, '_wfocu_rules', true );
			$has_rules    = $no_product = $no_offers = false;
			foreach ( is_array( $funnel_rules ) ? $funnel_rules : array() as $rule_groups ) {
				foreach ( is_array( $rule_groups ) ? $rule_groups : array() as $rules_data ) {
					foreach ( is_array( $rules_data ) ? $rules_data : array() as $rules_arr ) {
						if ( isset( $rules_arr['rule_type'] ) && ( 'general_always' !== $rules_arr['rule_type'] && 'general_always_2' !== $rules_arr['rule_type'] ) ) {
							$has_rules = true;
							break 3;
						}
					}
				}
			}
			$funnel_steps = WFOCU_Core()->funnels->get_funnel_steps( $step_id );
			if ( ! is_array( $funnel_steps ) || count( $funnel_steps ) < 1 ) {
				$no_offers = true;
			} else {
				$funnel_statuses = wp_list_pluck( $funnel_steps, 'state' );
				if ( ! in_array( '1', $funnel_statuses, true ) ) {
					$no_product = true;
				}
				if ( ! $no_product ) {
					$offer_id   = wp_list_pluck( $funnel_steps, 'id', true );
					$offer_id   = is_array( $offer_id ) && count( $offer_id ) > 0 ? $offer_id[0] : 0;
					$offer_meta = get_post_meta( $offer_id, '_wfocu_setting', true );
					if ( ! empty( $offer_meta ) ) {
						$products = (array) $offer_meta->products;
						if ( is_array( $products ) && count( $products ) === 0 ) {
							$no_product = true;
						}
					}
				}
			}
			$flags = array();
			if ( $has_rules ) {
				$flags['has_rules'] = array(
					'label'       => __( 'Has Rules', 'funnel-builder' ),
					'label_class' => 'bwf-st-c-badge-green',
				);
			}
			if ( $no_offers ) {
				$flags['no_offers'] = array(
					'label'       => __( 'No offers', 'funnel-builder' ),
					'label_class' => 'bwf-st-c-badge-red',
				);
			}
			if ( $no_product ) {
				$flags['no_product'] = array(
					'label'       => __( 'No Products', 'funnel-builder' ),
					'label_class' => 'bwf-st-c-badge-red',
				);
			}

			return $flags;
		}

		/**
		 * @return array|void
		 */
		public function get_supports() {
			return array_unique( array_merge( parent::get_supports(), [ 'expand' ] ) );
		}


		/**
		 * @param $funnels
		 *
		 * @return array
		 */
		public function maybe_filter_upsells( $funnels ) {
			$funnel          = WFFN_Core()->data->get_session_funnel();
			$current_step    = WFFN_Core()->data->get_current_step();
			$current_step_id = isset( $current_step['id'] ) ? $current_step['id'] : 0;
			if ( $current_step_id > 0 ) {
				$current_step['id'] = apply_filters( 'wffn_maybe_get_ab_control', $current_step_id );
			}
			if ( WFFN_Core()->data->has_valid_session() && ! empty( $current_step ) && wffn_is_valid_funnel( $funnel ) && $this->validate_environment( $current_step ) ) {


				return $this->maybe_get_upsells( $current_step, $funnel );
			}

			return $funnels;
		}

		/**
		 * @param $current_step
		 *
		 * @return bool
		 */
		public function validate_environment( $current_step ) {
			$wfacp_id = WFOCU_Core()->data->get_posted( 'wfacp_embed_form_page_id', 0 );

			if ( empty( $wfacp_id ) ) {
				// For Dedicated and Global checkout
				$wfacp_id = WFOCU_Core()->data->get_posted( '_wfacp_post_id', 0 );
			}
			if ( empty( $wfacp_id ) ) {
				// For Dedicated and Global checkout
				$wfacp_id = WFOCU_Core()->data->get_posted( 'wfacp_post_id', 0 );
			}

			if ( empty( $wfacp_id ) ) {
				$orderID = WFFN_Core()->data->get( 'wc_order' );
				$order   = wc_get_order( $orderID );
				if ( ! $order instanceof WC_Order ) {
					WFFN_Core()->logger->log( 'No Order found.' );

					return false;
				}

				$get_checkout_id = $order->get_meta( '_wfacp_post_id', true );
				$wfacp_id        = $get_checkout_id;

			}
			$current_step = WFFN_Core()->data->get_current_step();

			if ( absint( $current_step['id'] ) === absint( $wfacp_id ) ) {
				return true;
			}

			return false;
		}

		/**
		 * @param $current_step
		 * @param $funnel
		 *
		 * @return array
		 */
		public function maybe_get_upsells( $current_step, $funnel ) {
			$found_step          = false;
			$all_upsells_funnels = [];
			$targets_step_found  = false;
			foreach ( $funnel->steps as $key => $step ) {

				/**
				 * continue till we found the current step
				 */
				if ( absint( $current_step['id'] ) === absint( $step['id'] ) ) {
					$found_step = $key;
					continue;
				}
				/**
				 * Continue if we have not found the current step yet
				 */
				if ( false === $found_step ) {
					continue;
				}

				/**
				 * if step is not the type after the current step then break the loop
				 */
				if ( false !== $found_step && $this->slug !== $step['type'] && true === $targets_step_found ) {
					break;
				}
				if ( false !== $found_step && $this->slug !== $step['type'] ) {
					continue;
				}
				/**
				 * if we have found the curent step and type is upsell then connect
				 */
				if ( false !== $found_step && $this->slug === $step['type'] ) {
					$properties = $this->populate_data_properties( $step, $funnel->get_id() );

					if ( $this->is_disabled( $this->get_enitity_data( $properties['_data'], 'status' ) ) ) {
						continue;
					}
					array_push( $all_upsells_funnels, [ 'id' => $step['id'] ] );
					$targets_step_found = true;
					continue;
				}
			}

			return $all_upsells_funnels;
		}

		/**
		 * @param $type
		 * @param $step_id
		 * @param $new_status
		 *
		 * @return bool
		 */
		public function switch_status( $step_id, $new_status ) {
			$switched = false;
			if ( $step_id > 0 && get_post( $step_id ) instanceof WP_Post ) {
				$updated_id  = 0;
				$post_status = get_post_status( $step_id );
				$newStatus   = ( 1 === absint( $new_status ) ) ? 'publish' : WFOCU_SLUG . '-disabled';
				if ( $newStatus !== $post_status ) {
					$updated_id = wp_update_post( array(
						'ID'          => $step_id,
						'post_status' => $newStatus,
					) );
				}
				if ( intval( $step_id ) === intval( $updated_id ) ) {
					$switched = true;
				}
			}

			return $switched;
		}


		public function _get_export_metadata( $step ) {
			return WFOCU_Core()->export->export_a_funnel( $step['id'] );
		}

		public function _process_import( $funnel_id, $step_data ) {
			$ids         = WFOCU_Core()->import->import_from_json_data( array(
				array_merge( $step_data['meta'], array(
					'title'  => $step_data['title'],
					'status' => ( isset( $step_data['status'] ) ? $step_data['status'] : 0 )
				) )
			) );
			$posted_data = [ 'title' => $step_data['title'], 'id' => $ids[0] ];
			parent::add_step( $funnel_id, $posted_data );
		}

		public function has_import_scheduled( $id ) {

			$get_steps = WFOCU_Core()->funnels->get_funnel_steps( $id );
			foreach ( $get_steps as $step ) {
				$template = get_post_meta( $step['id'], '_tobe_import_template', true );
				if ( ! empty( $template ) ) {
					return array(
						'template'      => $template,
						'template_type' => get_post_meta( $step['id'], '_tobe_import_template_type', true )

					);
				}
			}

			return false;
		}

		public function do_import( $id ) { //phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
			return true;
		}

		public function update_template_data( $id ) {
			$get_steps = WFOCU_Core()->funnels->get_funnel_steps( $id );
			foreach ( $get_steps as $step ) {

				$template      = get_post_meta( $step['id'], '_tobe_import_template', true );
				$template_type = get_post_meta( $step['id'], '_tobe_import_template_type', true );

				if ( empty( $template ) || empty( $template_type ) ) {
					continue;
				}
				$meta = get_post_meta( $step['id'], '_wfocu_setting', true );

				if ( is_object( $meta ) ) {
					$meta->template       = $template;
					$meta->template_group = $template_type;
					update_post_meta( $step['id'], '_wfocu_setting', $meta );
					WFOCU_Core()->importer->maybe_import_data( $meta->template_group, $meta->template, $step['id'], $meta );
				}
				if ( '' !== $id ) {
					WFOCU_Common::update_funnel_time( $id );
				}
				delete_post_meta( $step['id'], '_tobe_import_template' );
				delete_post_meta( $step['id'], '_tobe_import_template_type' );
			}
		}

		public static function funnel_id_recorded( $args ) {
			$funnel = WFFN_Core()->data->get_session_funnel();

			if ( wffn_is_valid_funnel( $funnel ) ) {
				$args['fid'] = $funnel->get_id();
			}

			return $args;

		}

		/**
		 * @param $get_ref
		 *
		 * @return mixed
		 */
		public function maybe_funnel_breadcrumb( $get_ref ) {
			$step_id = filter_input( INPUT_GET, 'edit', FILTER_SANITIZE_STRING );
			if ( empty( $get_ref ) && ! empty( $step_id ) ) {
				$funnel_id = get_post_meta( $step_id, '_bwf_in_funnel', true );
				if ( ! empty( $funnel_id ) && abs( $funnel_id ) > 0 ) {
					return $funnel_id;
				}
			}

			return $get_ref;
		}

		public function override_pixel_key( $key ) {
			$step_id = WFOCU_Core()->funnels->get_funnel_id();

			if ( $step_id > 0 && get_post( $step_id ) instanceof WP_Post ) {
				$setting = WFFN_Common::maybe_override_tracking( $step_id );
				if ( is_array( $setting ) ) {
					$key = ( isset( $setting['fb_pixel_key'] ) && ! empty( $setting['fb_pixel_key'] ) ) ? $setting['fb_pixel_key'] : $key;
				}
			}

			return $key;
		}

		/**
		 * @param $key
		 *
		 * @return mixed
		 */
		public function override_ga_key( $key ) {
			$step_id = WFOCU_Core()->funnels->get_funnel_id();

			if ( $step_id > 0 && get_post( $step_id ) instanceof WP_Post ) {
				$setting = WFFN_Common::maybe_override_tracking( $step_id );
				if ( is_array( $setting ) ) {
					$key = ( isset( $setting['ga_key'] ) && ! empty( $setting['ga_key'] ) ) ? $setting['ga_key'] : $key;
				}
			}

			return $key;
		}

		/**
		 * @param $key
		 *
		 * @return mixed
		 */
		public function override_gad_key( $key ) {
			$step_id = WFOCU_Core()->funnels->get_funnel_id();

			if ( $step_id > 0 && get_post( $step_id ) instanceof WP_Post ) {
				$setting = WFFN_Common::maybe_override_tracking( $step_id );
				if ( is_array( $setting ) ) {
					$key = ( isset( $setting['gad_key'] ) && ! empty( $setting['gad_key'] ) ) ? $setting['gad_key'] : $key;
				}
			}

			return $key;
		}

		/**
		 * @param $key
		 *
		 * @return mixed
		 */
		public function override_pint_key( $key ) {
			$step_id = WFOCU_Core()->funnels->get_funnel_id();

			if ( $step_id > 0 && get_post( $step_id ) instanceof WP_Post ) {
				$setting = WFFN_Common::maybe_override_tracking( $step_id );
				if ( is_array( $setting ) ) {
					$key = ( isset( $setting['pint_key'] ) && ! empty( $setting['pint_key'] ) ) ? $setting['pint_key'] : $key;
				}
			}

			return $key;
		}

		/**
		 * @param $key
		 *
		 * @return mixed
		 */
		public function override_conversion_key( $key ) {
			$step_id = WFOCU_Core()->funnels->get_funnel_id();

			if ( $step_id > 0 && get_post( $step_id ) instanceof WP_Post ) {
				$setting = WFFN_Common::maybe_override_tracking( $step_id );
				if ( is_array( $setting ) ) {
					$key = ( isset( $setting['gad_conversion_label'] ) && ! empty( $setting['gad_conversion_label'] ) ) ? $setting['gad_conversion_label'] : $key;
				}
			}

			return $key;
		}
	}

	WFFN_Core()->steps->register( WFFN_Step_WC_Upsells::get_instance() );
}

