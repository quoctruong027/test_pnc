<?php

/*
 * Admin class for adding a new component
 */

class Xlwcty_Track_Order_Components {

	private static $instance = null;
	protected $components_dir;
	protected $components = array();
	protected $components_fields = array();

	public function __construct( $order = false ) {
		add_action( 'plugins_loaded', array( $this, 'xlwcty_load_components' ), 0 );
		add_filter( 'XLWCTY_Component', array( $this, 'xlwcty_add_new_component' ), 11, 1 );
		add_filter( 'XLWCTY_Component_fields', array( $this, 'xlwcty_add_new_component_fields' ), 11, 1 );
	}

	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Function to load track your order component on website
	 */
	public function xlwcty_load_components() {
		/** Return if not component builder page */
		if ( ( ! isset( $_GET['page'] ) || 'xlwcty_builder' != $_GET['page'] ) && ! isset( $_GET['key'] ) && ! isset( $_GET['order_id'] ) && ! isset( $_POST['action'] ) ) {
			return;
		}

		$this->components_dir = XLWCTY_POWER_PACK_PLUGIN_DIR . '/components';

		if ( $handle = opendir( XLWCTY_POWER_PACK_PLUGIN_DIR . '/components' ) ) {
			while ( false !== ( $entry = readdir( $handle ) ) ) {

				if ( ! is_file( $entry ) && '.' != $entry && '..' != $entry ) {
					$needed_file = $this->components_dir . '/' . $entry . '/data.php';

					if ( file_exists( $needed_file ) ) {
						$component_data = array();
						$component_data = include_once $needed_file;

						if ( isset( $component_data['instance'] ) && is_object( $component_data['instance'] ) ) {

							$slug                             = $component_data['slug'];
							$this->components_fields[ $slug ] = $component_data['fields'];
							$component_data['instance']->set_slug( $slug );
							$component_data['instance']->set_component( $component_data );
							$this->components[ $slug ] = $component_data['instance'];
						}
					}
				}
			}
			closedir( $handle );
		}

		do_action( 'xlwcty_power_pack_after_components_loaded' );
	}

	/**
	 * @param $components
	 *
	 * @return mixed
	 * Function to add track your order component on the website
	 */
	public function xlwcty_add_new_component( $components ) {
		if ( is_array( $this->components ) && count( $this->components ) > 0 ) {
			foreach ( $this->components as $key => $value ) {
				$components[ $key ] = $value;
			}
		}

		return $components;
	}

	/**
	 * @param $component_fields
	 *
	 * @return mixed
	 * Function to add track your component fields on the website
	 */
	public function xlwcty_add_new_component_fields( $component_fields ) {
		if ( is_array( $this->components_fields ) && count( $this->components_fields ) > 0 ) {
			foreach ( $this->components_fields as $key => $value ) {
				$component_fields[ $key ] = $value;
			}
		}

		return $component_fields;
	}
}

return Xlwcty_Track_Order_Components::get_instance();
