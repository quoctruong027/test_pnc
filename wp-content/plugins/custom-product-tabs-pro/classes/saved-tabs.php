<?php

/**
 * CRUD-type functions for saved tabs
 */
class YIKES_Custom_Product_Tabs_Pro_Saved_Tabs {

	/**
	 * Define hooks.
	 */
	public function __construct() {

		// Handle a saved tab on save (i.e. global || applying new saved tabs to taxonomies ).
		add_action( 'yikes-woo-handle-tab-save', array( $this, 'handle_adding_removing_saved_tab_to_products' ), 10, 2 );

		// When a term is deleted, remove applicable saved tabs from products.
		add_action( 'delete_term', array( $this, 'remove_saved_tab_from_products_on_term_delete' ), 10, 5 );

		// Add the saved tabs to the bulk edit UI.
		add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'display_saved_tabs_on_bulk_edit' ), 10 );

		// Add the saved tabs to products via bulk edit.
		add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'apply_saved_tabs_on_bulk_edit' ), 10 );

		// When a post gets assigned a term, check if we need to add a saved tab to it.
		add_action( 'yikes-woo-handle-tabs-on-product-save', array( $this, 'add_global_taxonomy_tabs_to_new_products' ), 20, 1 );

		// Add global tabs to new products.
		add_action( 'yikes-woo-handle-tabs-on-product-save', array( $this, 'add_global_tabs_to_new_products' ), 10, 1 );
	}

	/**
	 * When a new product is created lets check to see if we have global tabs to apply.
	 */
	public function add_global_tabs_to_new_products( $product_id ) {
		$check_flag = get_post_meta( $product_id, 'yikes_flagged_global_added' );
	
		if ( ! $check_flag  ) {
			// Adding a flag to our tab to make sure this only happens on product creation.
			add_post_meta( $product_id, 'yikes_flagged_global_added', 'added' );
			return $this->add_global_tabs( $product_id );
		}
		return;
	}

	/**
	 * Add the global tabs for a product.
	 */
	private function add_global_tabs( $product_id ) {
		$saved_tabs           = get_option( 'yikes_woo_reusable_products_tabs' );
		$saved_tabs_applied   = get_option( 'yikes_woo_reusable_products_tabs_applied' );
		$saved_tabs_applied   = empty( $saved_tabs_applied ) ? array() : $saved_tabs_applied;
		$excluded_product_ids = apply_filters( 'cptpro_global_tab_excluded_product_ids', array() );

		if ( isset( $excluded_product_ids[ $product_id ] ) ) {
			return;
		}

		if ( ! empty( $saved_tabs ) ) {
	
			foreach ( $saved_tabs as $saved_tab_id => $tab ) {
				if ( isset( $tab['global_tab'] ) && $tab['global_tab'] === true ) {
					$saved_tabs_applied = $this->add_saved_tab( $product_id, $tab, $saved_tabs_applied );
				}
			}

			update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
		}
	}

	/**
	 * When a new product is created lets check to see if we have global taxonomy tabs to apply.
	 */
	public function add_global_taxonomy_tabs_to_new_products( $product_id ) {
		$check_flag = get_post_meta( $product_id, 'yikes_flagged_global_taxonomy_added' );

		if ( ! $check_flag ) {
			// Adding a flag to our tab to make sure this only happens on product creation.
			add_post_meta( $product_id, 'yikes_flagged_global_taxonomy_added', 'added' );

			return $this->add_taxonomy_tabs( $product_id );
		}
		return;
	}

	/**
	 * Sync with global taxonomy tabs.
	 *
	 * @param number $product_id Product id.
	 */
	public function add_taxonomy_tabs( $post_id ) {

		$product = get_post( $post_id, 'OBJECT' );
		if ( empty( $product ) ) {
			return;
		}

		$pro_helper_class = new YIKES_Custom_Product_Tabs_Pro_Admin();
		$taxonomies         = $pro_helper_class->cptpro_get_object_taxonomies( $product, 'names' );
		$saved_tabs_applied = get_option( 'yikes_woo_reusable_products_tabs_applied' );
		$saved_tabs         = get_option( 'yikes_woo_reusable_products_tabs' );
		foreach( $taxonomies as $taxonomy_slug ) {
			$products_terms = get_the_terms( $post_id, $taxonomy_slug );
			/**
			* Filter the terms that a product is assigned.
			*
			* If for some reason you need to directly apply saved tabs to a product that does not have the term, use this filter.
			* Some taxonomies, like product visibility, assign their terms after our hook so they will not be available by default. 
			*
			* @param array  | $products_terms | The product's terms
			* @param int    | $post_id
			* @param string | $taxonomy_slug  | The taxonomy slug
			*
			* @return array | $products_terms | Array of WP_Term objects
			*/
			$products_terms = apply_filters( 'cptpro-assigned-product-taxonomy-terms', $products_terms, $post_id, $taxonomy_slug );
			if ( ! is_wp_error( $products_terms ) && $products_terms !== false ) {
				// Add tabs
				$term_ids = array_map( function( WP_Term $term ) { return $term->term_id; }, $products_terms );
				$tabs     = $this->get_saved_tabs_with_terms( $taxonomy_slug, $term_ids );
				if ( ! empty( $tabs ) ) {
					foreach( $tabs as $tab ) {
						$saved_tabs_applied = $this->add_saved_tab( $post_id, $tab, $saved_tabs_applied );
					}
				}
				// Remove tabs
				// Right here we have access to all of the saved tabs for this product.
				// However, we have no way of knowing if this saved tab was added manually and should now be deleted, i.e. was this saved tab applied via the taxonomy or was it added directly to a product without that taxonomy.
				// Do we need a flag saying "this tab was added manually?"
				// For now, let's just pretend users are not adding tabs manually. So we will delete any tab attached to a taxonomy. If this causes issues for the user, they can use this filter to disable it.
				if ( apply_filters( 'cptpro-remove-tabs-from-product-by-taxonomy', true ) === true ) {
					if ( isset( $saved_tabs_applied[ $post_id ] ) && ! empty( $saved_tabs_applied[ $post_id ] ) ) {
						foreach( $saved_tabs_applied[ $post_id ] as $saved_tab_id => $saved_tab ) {
							if ( isset( $saved_tabs[ $saved_tab_id ] ) ) {
								$tab = $saved_tabs[ $saved_tab_id ];
								if ( ! empty( $tab['taxonomies'] ) && isset( $tab['taxonomies'][$taxonomy_slug] ) ) {
									$tabs_taxonomies = array_keys( $tab['taxonomies'][$taxonomy_slug] );
									// Check if this product has any of this tab's taxonomies...
									$tabs_taxonomies_intersect = array_intersect( $tabs_taxonomies, $term_ids );
									if ( empty( $tabs_taxonomies_intersect ) ) {
										$saved_tabs_applied = $this->delete_saved_tab( $post_id, $tab, $saved_tabs_applied );
									}
								}
							}
						}
					}
				}
			}
		}
		update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
	}

	public function get_product_term_slugs( $product_id ) {
		$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );
		// Check if this is a taxonomy we even care about
		$pro_helper_class = new YIKES_Custom_Product_Tabs_Pro_Admin();
		$taxonomy_slugs   = $pro_helper_class->cptpro_get_object_taxonomies( 'product' );

		$terms_on_product = array();

		foreach( $taxonomy_slugs as $id => $taxonomy_slug ) {
			$terms = get_the_terms( $product_id, $taxonomy_slug );
			if ( ! empty( $terms ) ){
				foreach( $terms as $t ) {
					$terms_on_product[] = $t->slug;
				}
			}
		}

		return $terms_on_product;
	}

	/**
	 * Pass tab data to global tab handler or saved tab handler
	 *
	 * @param array  | $tab | The current tab
	 * @param string | $new | If the tab is a new or existing product
	 */
	public function handle_adding_removing_saved_tab_to_products( $tab, $new ) {

		if ( isset( $tab['global_tab'] ) && $tab['global_tab'] === true ) {

			// If we're dealing with a tab that already has global status, don't add it again.
			if ( $this->global_status_added( $tab ) === false ) {
				return;
			}

			$this->add_global_tab_to_products( $tab );
		} else {
			$this->handle_saved_tab( $tab, $new );
		}
	}

	/**
	 * Call `add_saved_tab_to_products` and, if it's not a new tab, call `remove_saved_tab_from_products_by_taxonomy` or `remove_saved_tab_from_products_by_global`
	 *
	 * @param array  | $tab | The current tab
	 * @param string | $new | If the tab is a new or existing product
	 */
	private function handle_saved_tab( $tab, $new ) {

		// Get our saved tabs option
		$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );

		// Get the original tab slug (it may have changed) so we can properly check our post meta field
		$original_tab_slug = isset( $saved_tabs[ $tab['tab_id'] ] ) && isset( $saved_tabs[ $tab['tab_id'] ]['tab_slug'] ) ? $saved_tabs[ $tab['tab_id'] ]['tab_slug'] : '';

		// Delete tabs first
		if ( $new !== 'new' ) {

			// Check if this was a global tab
			if ( $this->global_status_removed( $tab ) === true ) {
				
				// If this is no longer a global tab, remove it from every product
				$this->remove_saved_tab_from_products_by_global( $tab, $original_tab_slug );
			} else {

				// Otherwise just remove the saved tab from the taxonomies that are no longer applicable
				$this->remove_saved_tab_from_products_by_taxonomy( $tab, $original_tab_slug );
			}
		}

		// Then add new ones
		$this->add_saved_tab_to_products( $tab, $original_tab_slug );
	}

	    /*****************************/
	   /** **/                 /** **/
	  /***** Apply a Saved tab *****/
	 /** **/                 /** **/
	/*****************************/

	/**
	 * Add a global tab to all products
	 *
	 * @param array | $tab | A saved tab
	 */
	private function add_global_tab_to_products( $tab ) {

		$product_ids          = YIKES_Custom_Product_Tabs_Pro_Admin::fetch_all_product_ids();
		$product_ids          = apply_filters( 'cptpro-filter-product-ids-for-global-tab', $product_ids, $tab );
		$saved_tabs_applied   = get_option( 'yikes_woo_reusable_products_tabs_applied' );
		$saved_tabs_applied   = empty( $saved_tabs_applied ) ? array() : $saved_tabs_applied;
		$excluded_product_ids = apply_filters( 'cptpro_global_tab_excluded_product_ids', array() );

		if ( ! empty( $product_ids ) ) {

			foreach ( $product_ids as $product_id ) {

				if ( isset( $excluded_product_ids[ $product_id ] ) ) {
					continue;
				}

				$saved_tabs_applied = $this->add_saved_tab( $product_id, $tab, $saved_tabs_applied );
			}
		}

		update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
	}

	/**
	 * Get the products assigned to taxonomies and add a saved tab to each
	 *
	 * @param array  | $tab | A saved tab
	 * @param string | $original_tab_slug  | The tab slug before any potential name change. 
	 *                                       The slug is created from the title and is changed when the title is changed.
	 */
	private function add_saved_tab_to_products( $tab, $original_tab_slug ) {

		if ( ! isset( $tab['taxonomies'] ) || isset( $tab['taxonomies'] ) && empty( $tab['taxonomies'] ) ) {
			return;
		}

		$products_query = $this->get_products_by_taxonomy( $tab['taxonomies'] );

		if ( $products_query->have_posts() ) {

			$saved_tabs_applied = get_option( 'yikes_woo_reusable_products_tabs_applied' );
			$saved_tabs_applied = empty( $saved_tabs_applied ) ? array() : $saved_tabs_applied;

			foreach( $products_query->posts as $product_id ) {

				$saved_tabs_applied = $this->add_saved_tab( $product_id, $tab, $saved_tabs_applied, $original_tab_slug );
			}

			update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
		}
	}

	/**
	 * Fetch all of the products using taxonomies
	 *
	 * @param array | $taxonomies | Array of a tab's taxonomies (taxonomy_slugs => terms)
	 *
	 * @return obj  | WP_Query result
	 */
	private function get_products_by_taxonomy( $taxonomies ) {

		$wp_query_args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => '-1',
			'fields'         => 'ids',
			'tax_query'      => $this->format_tax_query( $taxonomies ),
		);

		$wp_query_args = apply_filters( 'cptpro-filter-fetch-product-by-taxonomies', $wp_query_args );

		$products_query = new WP_Query( $wp_query_args );

		return $products_query;
	}

	/**
	 * Format a tab's taxonomy data for a `tax_query` for WP_Query
	 *
	 * @param array  | $taxonomies | e.g. array( 'taxonomy_slug' => array( 'term_slug1', 'term_slug2' ), ... );
	 *
	 * @return array | Formatted `tax_query` array
	 */
	private function format_tax_query( $taxonomies ) {

		$tax_query = array();
		$tax_query['relation'] = 'OR';

		foreach( $taxonomies as $taxonomy_slug => $terms ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy_slug,
				'field'    => 'slug',
				'terms'    => $terms,
			);
		}

		return $tax_query;
	}

	/**
	 * Fetch the product's tabs and call our `add_saved_tab` functions
	 *
	 * @param int    | $product_id         | Need I say more?
	 * @param array  | $tab                | The current tab to check
	 * @param array  | $saved_tabs_applied | Array of saved tabs applied from our option
	 * @param string | $original_tab_slug  | The tab slug before any potential name change. 
	 *                                       The slug is created from the title and is changed when the title is changed.
	 *
	 * @return array | $saved_tabs_applied | Array of saved tabs applied w/ our new tab added
	 */
	private function add_saved_tab( $product_id, $tab, $saved_tabs_applied, $original_tab_slug = '' ) {

		$tabs = maybe_unserialize( get_post_meta( $product_id, 'yikes_woo_products_tabs', true ) );
		$tabs = empty( $tabs ) ? array() : $tabs;

		if ( $this->product_has_tab( $product_id, $tab, $tabs, $saved_tabs_applied, $original_tab_slug ) !== true ) {
			$this->add_saved_tab_as_post_meta( $product_id, $tab, $tabs );
			$saved_tabs_applied = $this->add_saved_tab_to_option( $product_id, $tab, $saved_tabs_applied );
		}

		return $saved_tabs_applied;
	}

	/**
	 * Add a tab to a product's `yikes_woo_products_tabs` post meta
	 *
	 * @param int   | $product_id
	 * @param array | $tab        | The current tab to check
	 * @param array | $tabs       | Array of all the tabs assigned to this product
	 */
	private function add_saved_tab_as_post_meta( $product_id, $tab, $tabs ) {
		if ( is_array( $tabs ) ) {
			$tabs[] = $this->convert_saved_tab_to_post_meta_tab( $tab );
			update_post_meta( $product_id, 'yikes_woo_products_tabs', $tabs );
		} else {
			$tab = $this->convert_saved_tab_to_post_meta_tab( $tab );
			update_post_meta( $product_id, 'yikes_woo_products_tabs', array( $tab ) );
		}
	}

	/**
	 * Format a saved tab to a post meta tab.
	 *
	 * For legacy reasons, the format of a "saved tab" is different than a tab from a product's post meta.
	 *
	 * @param array  | $tab | A saved-tab-formatted tab
	 *
	 * @return array | A post-meta-tab-formatted tab
	 */
	private function convert_saved_tab_to_post_meta_tab( $tab ) {
		return array(
			'title'   => $tab['tab_title'],
			'id'      => $tab['tab_slug'],
			'content' => $tab['tab_content'],
		);
	}

	/**
	 * Add a saved tab to our `yikes_woo_reusable_products_tabs_applied` option.
	 *
	 * @param int    | $product_id         | The ID of the product of course!
	 * @param array  | $tab                | The current tab to check
	 * @param array  | $saved_tabs_applied | Array of saved tabs applied from our option
	 *
	 * @return array | $saved_tabs_applied | Array of saved tabs applied w/ our new tab added
	 */
	private function add_saved_tab_to_option( $product_id, $tab, $saved_tabs_applied ) {
		
		$saved_tabs_applied[ $product_id ][ $tab['tab_id'] ] = $this->convert_saved_tab_to_options_tab( $product_id, $tab );
		return $saved_tabs_applied;
	}

	/**
	 * Format a saved tab to an applied-saved-tabs tab
	 *
	 * For legacy reasons, the format of a "saved tab" is different than a tab from our `yikes_woo_reusable_products_tabs_applied` option.
	 *
	 * @param int    | $product_id | THE PRODUCT ID!
	 * @param array  | $tab        | A saved-tab-formatted tab
	 *
	 * @return array | An applied-saved-tabs tab
	 */
	private function convert_saved_tab_to_options_tab( $product_id, $tab ) {
		return array(
			'post_id'         => $product_id,
			'reusable_tab_id' => $tab['tab_id'],
			'tab_id'          => $tab['tab_slug'],
		);
	}

	/**
	 * Check if a product has a tab in it's `yikes_woo_products_tabs` post meta and `yikes_woo_reusable_tabs_applied` option
	 *
	 * @param int    $product_id
	 * @param array  $tab               The current tab to check
	 * @param array  $tabs              Array of all the tabs assigned to this product (postmeta)
	 * @param array  $saved_tab         Array of all the saved tabs
	 * @param string $original_tab_slug The tab slug before any potential name change. 
	 *                                  The slug is created from the title and is changed when the title is changed.
	 *
	 * @return bool | True if the product has the tab, False if it doesn't
	 */
	private function product_has_tab( $product_id, $tab, $tabs, $saved_tabs_applied, $original_tab_slug = '' ) {

		// Check the saved tabs option...
		if ( ! isset( $saved_tabs_applied[ $product_id ] ) || isset( $saved_tabs_applied[ $product_id ] ) && ! isset( $saved_tabs_applied[ $product_id ][ $tab['tab_id'] ] ) ) {
			return false;
		}

		// Check the post meta field
		if ( ! empty( $tabs ) ) {

			$tab_slug_to_check = empty( $original_tab_slug ) ? $tab['tab_slug'] : $original_tab_slug;

			foreach( $tabs as $current_tab ) {

				if ( $current_tab['id'] === $tab_slug_to_check ) {

					return true;
				}
			}
		}

		return false;
	}

		/*****************************/
	   /** **/                 /** **/
	  /*****   Delete a Tab    *****/
	 /** **/                 /** **/
	/*****************************/

	/**
	 * When a term is deleted, remove any applicable saved tabs.
	 *
	 * @param int | $term_id          | The term's ID
	 * @param int | $tt_id            | The term's term taxonomy ID
	 * @param string | $taxonomy_slug | The term's taxonomy slug
	 *  @param object | $deleted_term  | The term object
	 * @param array  | $object_ids    | List of term object IDs
	 */
	public function remove_saved_tab_from_products_on_term_delete( $term_id, $tt_id, $taxonomy_slug, $deleted_term, $object_ids ) {


		// Check if this is a taxonomy we even care about
		$pro_helper_class = new YIKES_Custom_Product_Tabs_Pro_Admin();
		$taxonomy_slugs   = $pro_helper_class->cptpro_get_object_taxonomies( 'product' );
		$taxonomy_slugs   = array_flip( $taxonomy_slugs );
		if ( ! isset( $taxonomy_slugs[ $taxonomy_slug ] ) ) {
			return;
		}

		$tabs_using_term = array();
		$saved_tabs      = get_option( 'yikes_woo_reusable_products_tabs' );

		foreach( $saved_tabs as $saved_tab_id => $saved_tab ) {
			if ( isset( $saved_tab['taxonomies'] ) && isset( $saved_tab['taxonomies'][$taxonomy_slug] ) && ! empty( $saved_tab['taxonomies'][$taxonomy_slug] ) && isset( $saved_tab['taxonomies'][ $taxonomy_slug ][ $tt_id ] ) ) {

					$tabs_using_term[ $saved_tab_id ] = $saved_tab;

					// Remove this term from the saved tab's taxonomies
					unset( $saved_tabs[ $saved_tab_id ]['taxonomies'][ $taxonomy_slug ][ $tt_id ] );
			}
		}

		if ( empty( $tabs_using_term ) ) {
			return;
		}

		// Save our updated saved tabs array
		update_option( 'yikes_woo_reusable_products_tabs', $saved_tabs );

		// Need to find out if this product has an applicable taxonomy that means we SHOULDN'T delete it (that's the hard part...)

		$saved_tabs_applied = get_option( 'yikes_woo_reusable_products_tabs_applied' );

		foreach( $saved_tabs_applied as $product_id => $saved_tabs_array ) {

			foreach( $saved_tabs_array as $key => $saved_tab ) {

				if ( isset( $tabs_using_term[ $key ] ) ) {

					if ( $this->maybe_remove_tab( $product_id, $tabs_using_term[ $key ], $taxonomy_slug ) === true ) {

						$saved_tabs_applied = $this->delete_saved_tab( $product_id, $tabs_using_term[ $key ], $saved_tabs_applied );
					}
				}
			}
		}

		update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
	}

	/**
	 * Check whether a product is using a saved tab's term 
	 *
	 * @param int    | $product_id
	 * @param array  | $saved_tab   
	 * @param string | $taxonomy_slug
	 *
	 * @return bool  | True if the product isn't using any other of the saved tab's terms (and the tab should be unapplied)
	 *               | False if the product is using one of the saved tab's terms (and the tab should be kept)
	 */
	private function maybe_remove_tab( $product_id, $saved_tab, $taxonomy_slug ) {

		// If we deleted the only term attached to this tab, then remove the tab
		if ( count( $saved_tab['taxonomies'] ) === 1 && count( $saved_tab['taxonomies'][ $taxonomy_slug ] ) === 1 ) {
			return true;
		}

		// Loop through the $saved_tab's taxonomies
		foreach( $saved_tab['taxonomies'] as $taxonomy_slug => $term ) {

			// If our product has any of our tab's terms then we shouldn't remove the tab.
			if ( count( get_the_terms( $product_id, $taxonomy_slug ) ) > 0 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Remove the tab from all products
	 *
	 * @param array  | $tab | The current tab
	 * @param string | $original_tab_slug | The tab slug before any potential name change. 
	 *                                      The slug is created from the title and is changed when the title is changed.
	 */
	private function remove_saved_tab_from_products_by_global( $tab, $original_tab_slug ) {

		$product_ids        = YIKES_Custom_Product_Tabs_Pro_Admin::fetch_all_product_ids();
		$saved_tabs_applied = get_option( 'yikes_woo_reusable_products_tabs_applied' );
		$saved_tabs_applied = empty( $saved_tabs_applied ) ? array() : $saved_tabs_applied;

		if ( ! empty( $product_ids ) ) {

			foreach( $product_ids as $product_id ) {

				$saved_tabs_applied = $this->delete_saved_tab( $product_id, $tab, $saved_tabs_applied, $original_tab_slug );
			}

			update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
		}
	}

	/**
	 * Check if a taxonomy was removed from a tab and, if it was, remove the tabs from all products using that taxonomy
	 *
	 * @param array  | $tab | The current tab
	 * @param string | $original_tab_slug  | The tab slug before any potential name change. 
	 *                                       The slug is created from the title and is changed when the title is changed.
	 */
	private function remove_saved_tab_from_products_by_taxonomy( $tab, $original_tab_slug ) {
		$previous_tab        = $this->get_saved_tab_by_tab_id( $tab['tab_id'] );
		$prev_taxonomies     = $previous_tab['taxonomies'];
		$new_taxonomies      = $tab['taxonomies'];
		$deleted_taxonomies  = $this->compare_tab_taxonomies( $prev_taxonomies, $new_taxonomies );
		$saved_tabs_applied  = get_option( 'yikes_woo_reusable_products_tabs_applied' );
		$saved_tabs_applied  = empty( $saved_tabs_applied ) ? array() : $saved_tabs_applied;

		if ( empty( $deleted_taxonomies ) ) {
			return;
		}

		$products_query = $this->get_products_by_taxonomy( $deleted_taxonomies );

		if ( $products_query->have_posts() ) {

			foreach( $products_query->posts as $product_id ) {

				$saved_tabs_applied = $this->delete_saved_tab( $product_id, $tab, $saved_tabs_applied, $original_tab_slug );
			}

			update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
		}
	}

	/**
	 * Compare two arrays of a tab's taxonomies to find out if a taxonomy was removed from the current tab
	 *
	 * @param array  | $previous_taxonomies | The previous taxonomies for the tab
	 * @param array  | $new_taxonomies      | The new taxonomies for the tab 
	 *
	 * @return array | The difference between $previous_taxonomies and $new_taxonomies
	 */
	private function compare_tab_taxonomies( $previous_taxonomies, $new_taxonomies ) {

		$deleted_taxonomies = array();

		if ( ! empty( $previous_taxonomies ) ) {

			foreach( $previous_taxonomies as $taxonomy_slug => $taxonomy_terms ) {

				if ( isset( $new_taxonomies[ $taxonomy_slug ] ) && ! empty( $new_taxonomies[ $taxonomy_slug ] ) ) {

					// If the taxonomy slug is present in both arrays, check the difference between the terms
					$difference = array_diff( $taxonomy_terms, $new_taxonomies[ $taxonomy_slug ] );
					if ( ! empty( $difference ) ) {
						$deleted_taxonomies[ $taxonomy_slug ] = $difference;
					}

				} else {

					// If the taxonomy slug was present in the old but not in the new, these are ones we need to delete
					$deleted_taxonomies[ $taxonomy_slug ] = $taxonomy_terms;
				}
			}
		}

		return $deleted_taxonomies;
	}

	/**
	 * Fetch the product's tabs and then delete a saved tab by calling our `remove_saved_tab` functions
	 *
	 * @param int    | $product_id         | The ID of the product!!!
	 * @param array  | $tab                | The current tab to check
	 * @param array  | $saved_tabs_applied | Array of saved tabs applied from our option
	 * @param string | $original_tab_slug  | The tab slug before any potential name change. 
	 *                                       The slug is created from the title and is changed when the title is changed.
	 *
	 * @return array | $saved_tabs_applied | Array of saved tabs applied w/ our tab removed
	 */
	private function delete_saved_tab( $product_id, $tab, $saved_tabs_applied, $original_tab_slug = '' ) {

		$tabs = maybe_unserialize( get_post_meta( $product_id, 'yikes_woo_products_tabs', true ) );
		
		if ( empty( $tabs ) ) {
			return;
		}

		$this->remove_saved_tab_from_post_meta( $product_id, $tab, $tabs, $original_tab_slug );
		$saved_tabs_applied = $this->remove_saved_tab_from_option( $product_id, $tab, $saved_tabs_applied );

		return $saved_tabs_applied;
	}

	/**
	 * Remove a saved tab from a product's `yikes_woo_products_tabs` post meta
	 *
	 * @param int    | $product_id
	 * @param array  | $tab                 | The current tab to remove
	 * @param array  | $tabs                | Array of all the tabs assigned to this product
	 * @param string | $original_tab_slug  | The tab slug before any potential name change. 
	 *                                       The slug is created from the title and is changed when the title is changed.
	 */ 
	private function remove_saved_tab_from_post_meta( $product_id, $tab, $tabs, $original_tab_slug = '' ) {

		$tab_slug_to_check = empty( $original_tab_slug ) ? $tab['tab_slug'] : $original_tab_slug;

		foreach( $tabs as $index => $current_tab ) {
			if ( $current_tab['id'] === $tab_slug_to_check ) {
				unset( $tabs[ $index ] );
			}
		}

		update_post_meta( $product_id, 'yikes_woo_products_tabs', $tabs );
	}

	/**
	 * Remove a saved tab from the `yikes_woo_reusable_products_tabs_applied` option
	 *
	 * @param int    | $product_id         | The ID of the product of course!
	 * @param array  | $tab                | The current tab to remove
	 * @param array  | $saved_tabs_applied | Array of saved tabs applied from our option
	 *
	 * @return array | $saved_tabs_applied | Array of saved tabs applied w/ our new tab added
	 */
	private function remove_saved_tab_from_option( $product_id, $tab, $saved_tabs_applied ) {

		unset( $saved_tabs_applied[ $product_id ][ $tab['tab_id'] ] );

		if ( empty( $saved_tabs_applied[ $product_id ] ) ) {
			unset( $saved_tabs_applied[ $product_id ] );
		}
		
		return $saved_tabs_applied;
	}

	/**
	 * Get all of the tabs that have the specified terms assigned to them
	 *
	 * @param string | $taxonomy_slug | The taxonomy slug we're checking for
	 * @param array  | $term_ids      | Array of term IDs 
	 *
	 * @return array | Array of saved tabs assigned to the specified taxonomy's terms
	 */
	private function get_saved_tabs_with_terms( $taxonomy_slug, $term_ids ) {
		$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );
		$term_tabs  = array();

		if ( ! empty( $saved_tabs ) ) {

			foreach( $saved_tabs as $tab ) {

				// If this saved tab has any terms within the taxonomy
				if ( isset( $tab['taxonomies'] ) && isset( $tab['taxonomies'][ $taxonomy_slug ] ) ) {

					// Loop through each term
					foreach( $tab['taxonomies'][ $taxonomy_slug ] as $term_id => $term_slug ) {

						// If the term taxonomy ID matches one added to the product
						if ( in_array( $term_id, $term_ids ) && ! isset( $term_tabs[ $tab['tab_slug'] ] ) ) {
							$term_tabs[ $tab['tab_slug'] ] = $tab;
						}
					}
				}
			}
		}

		return $term_tabs;
	}

	/**
	 * Check if a saved tab was previously global
	 *
	 * @param array | $tab | A saved tab
	 *
	 * @return bool
	 */
	private function global_status_removed( $current_tab ) {
		$old_tab = $this->get_saved_tab_by_tab_id( $current_tab['tab_id'] );

		// If the old copy of the tab was global and the newly saved one is not global, then this is a saved tab with global status removed
		if ( isset( $old_tab['global_tab'] ) && $old_tab['global_tab'] === true && isset( $current_tab['global_tab'] ) && $current_tab['global_tab'] === false ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a saved tab is now global
	 *
	 * @param array | $tab | A saved tab
	 *
	 * @return bool
	 */
	private function global_status_added( $current_tab ) {
		$old_tab = $this->get_saved_tab_by_tab_id( $current_tab['tab_id'] );

		// If the old copy of the tab wasn't global and the newly saved one is global, then this is a saved tab with global status added
		if ( ( ! isset( $old_tab['global_tab'] ) || isset( $old_tab['global_tab'] ) && $old_tab['global_tab'] === false ) && isset( $current_tab['global_tab'] ) && $current_tab['global_tab'] === true ) {
			return true;
		}

		return false;
	}

	/**
	 * Get a saved tab from the `yikes_woo_reusable_products_tabs` option based on the $tab_id
	 *
	 * @param int    | $tab_id | The ID of the saved tab
	 *
	 * @return array | A tab array, or an empty array if the tab was not found
	 */
	private function get_saved_tab_by_tab_id( $tab_id ) {

		$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );
		return isset( $saved_tabs[ $tab_id ] ) ? $saved_tabs[ $tab_id ] : array();
	}


	    /*****************************/
	   /** **/                 /** **/
	  /*****     Bulk Edit     *****/
	 /** **/                 /** **/
	/*****************************/

	/**
	 * Add saved tabs checkbox HTML to the product bulk edit screen
	 */
	public function display_saved_tabs_on_bulk_edit() {

		$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );

		?>
			<br>
			<br>
			<div class="inline-edit-col">
				<div class="title inline-edit-saved-tabs-label">Saved Tabs</div>
				<ul class="cat-checklist saved-tabs-checklist">

					<?php foreach( $saved_tabs as $tab_id => $saved_tab ): ?>
						<li>
							<label class="selectit">
								<input value="<?php echo esc_attr( $tab_id ); ?>" type="checkbox" name="saved_tabs[]" id="saved-tab-<?php echo esc_attr( $tab_id ); ?>">
								<?php echo ! empty( $saved_tab['tab_name'] ) ? $saved_tab['tab_title'] . ' - ' . $saved_tab['tab_name'] : $saved_tab['tab_title']; ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<br>
		<?php
	}

	/**
	 * Handle the saved tabs that were checked off during bulk edit
	 */
	public function apply_saved_tabs_on_bulk_edit() {

		// Make sure we have saved tabs
		if ( ! isset( $_GET['saved_tabs'] ) || isset( $_GET['saved_tabs'] ) && empty( $_GET['saved_tabs'] ) ) {
			return;
		}

		// Make sure we have post IDs
		if ( ! isset( $_GET['post'] ) || isset( $_GET['post'] ) && empty( $_GET['post'] ) ) {
			return;
		}

		$product_ids        = $_GET['post'];
		$saved_tab_ids      = $_GET['saved_tabs'];
		$saved_tabs         = get_option( 'yikes_woo_reusable_products_tabs' );
		$saved_tabs_applied = get_option( 'yikes_woo_reusable_products_tabs_applied' );
		$saved_tabs_applied = empty( $saved_tabs_applied ) ? array() : $saved_tabs_applied;

		foreach ( $saved_tab_ids as $tab_id ) {

			if ( isset( $saved_tabs[ $tab_id ] ) && ! empty( $saved_tabs[ $tab_id ] ) ) {

				foreach( $product_ids as $product_id ) {

					$saved_tabs_applied = $this->add_saved_tab( $product_id, $saved_tabs[ $tab_id ], $saved_tabs_applied );
				}
			}
		}

		update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
	}
}

new YIKES_Custom_Product_Tabs_Pro_Saved_Tabs();
