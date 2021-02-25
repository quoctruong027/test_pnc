<?php
namespace PowerpackElements\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class PP_Faq_Schema.
 */
class PP_Faq_Schema {

	/**
	 * FAQ Data
	 *
	 * @var faq_data
	 */
	private $faq_data = [];

	private $widget_data = [];

	public function __construct() {
		//add_action('wp_head', array($this, 'render_faq_schema'));
		add_filter( 'elementor/frontend/builder_content_data', [ $this, 'grab_faq_data' ], 10, 2 );
		add_action( 'wp_footer', [ $this, 'render_faq_schema' ] );
	}

	public function grab_faq_data( $data, $post_id ) {
		$widgets = [];

		pp_get_elementor()->db->iterate_data( $data, function ( $element ) use ( &$widgets ) {
			$type = $this->get_widget_type( $element );
			if ( 'pp-faq' === $type ) {
				array_push( $widgets, $element );
			}
			return $element;
		} );

		if ( ! empty( $widgets ) ) {
			$this->widget_data[ $post_id ] = $widgets;

			foreach ( $widgets as $widget_data ) {
				$widget = pp_get_elementor()->elements_manager->create_element_instance( $widget_data );
				if ( isset( $widget_data['templateID'] ) ) {
					$type = $this->get_global_widget_type( $widget_data['templateID'], 1 );
					$element_class = $type->get_class_name();
					try {
						$widget = new $element_class( $widget_data, [] );
					} catch ( \Exception $e ) {
						return null;
					}
				}
				$settings = $widget->get_settings();
				$enable_schema = $settings['enable_schema'];
				$faq_items = $widget->get_faq_items();

				if ( ! empty( $faq_items ) && $enable_schema == 'yes' ) {
					foreach ( $faq_items as $faqs ) {
						$faq_data = array(
							'@type'          => 'Question',
							'name'           => $faqs['question'],
							'acceptedAnswer' =>
							array(
								'@type' => 'Answer',
								'text'  => $faqs['answer'],
							),
						);
						array_push( $this->faq_data, $faq_data );
					}
				}
			}
		}

		return $data;
	}

	public function render_faq_schema() {
		//$faqs_data = $this->get_faqs_data();
		$faqs_data = $this->faq_data;

		if ( $faqs_data ) {
			$schema_data = array(
				'@context'      => 'https://schema.org',
				'@type'         => 'FAQPage',
				'mainEntity'    => $faqs_data,
			);

			$encoded_data = wp_json_encode( $schema_data );
			?>
			<script type="application/ld+json">
				<?php echo( $encoded_data ); ?>
			</script>
			<?php
		}
	}

	private function get_widget_type( $element ) {
		if ( empty( $element['widgetType'] ) ) {
			$type = $element['elType'];
		} else {
			$type = $element['widgetType'];
		}

		if ( 'global' === $type && ! empty( $element['templateID'] ) ) {
			$type = $this->get_global_widget_type( $element['templateID'] );
		}
		return $type;
	}

	private function get_global_widget_type( $template_id, $return_type = false ) {
		$template_data = pp_get_elementor()->templates_manager->get_template_data( [
			'source'        => 'local',
			'template_id'   => $template_id,
		] );

		if ( is_wp_error( $template_data ) ) {
			return '';
		}

		if ( empty( $template_data['content'] ) ) {
			return '';
		}

		$original_widget_type = pp_get_elementor()->widgets_manager->get_widget_types( $template_data['content'][0]['widgetType'] );

		if ( $return_type ) {
			return $original_widget_type;
		}

		return $original_widget_type ? $template_data['content'][0]['widgetType'] : '';
	}

	// public function get_faqs_data() {
	// 	$elementor = \Elementor\Plugin::$instance;
	// 	$document = $elementor->documents->get( get_the_ID() );

	// 	if ( ! $document ) {
	// 		return;
	// 	}

	// 	$data = $document->get_elements_data();
	// 	$widget_ids = $this->get_widget_ids();
	// 	$faq_data = [];

	// 	foreach ( $widget_ids as $widget_id ) {
	// 		$widget_data = $this->find_element_recursive( $data, $widget_id );
	// 		$widget = $elementor->elements_manager->create_element_instance( $widget_data );

	// 		$settings = $widget->get_settings();
	// 		$enable_schema = $settings['enable_schema'];
	// 		$faq_items = $widget->get_faq_items();

	// 		if ( !empty($faq_items) && $enable_schema == 'yes' ) {
	// 			foreach ( $faq_items as $faqs ) {
	// 				$new_data = array(
	// 					'@type'          => 'Question',
	// 					'name'           => $faqs['question'],
	// 					'acceptedAnswer' =>
	// 					array(
	// 						'@type' => 'Answer',
	// 						'text'  => $faqs['answer'],
	// 					),
	// 				);
	// 				array_push( $faq_data, $new_data );
	// 			}
	// 		}
	// 	}

	// 	return $faq_data;
	// }

	// public function get_widget_ids() {
	// 	$elementor = \Elementor\Plugin::$instance;
	// 	$document = $elementor->documents->get( get_the_ID() );

	// 	if ( ! $document ) {
	// 		return;
	// 	}

	// 	$data = $document->get_elements_data();
	// 	$widget_ids = [];

	// 	$elementor->db->iterate_data( $data, function ( $element ) use ( &$widget_ids ) {
	// 		$type = $this->get_widget_type( $element );
	// 		if ( 'pp-faq' === $type ) {
	// 			array_push( $widget_ids, $element['id'] );
	// 		}
	// 		return $element;
	// 	} );

	// 	return $widget_ids;
	// }

	/**
	 * Get Widget Setting data.
	 *
	 * @since 1.4.13.2
	 * @access public
	 * @param array  $elements Element array.
	 * @param string $form_id Element ID.
	 * @return Boolean True/False.
	 */
	// public function find_element_recursive( $elements, $form_id ) {

	// 	foreach ( $elements as $element ) {
	// 		if ( $form_id === $element['id'] ) {
	// 			return $element;
	// 		}

	// 		if ( ! empty( $element['elements'] ) ) {
	// 			$element = $this->find_element_recursive( $element['elements'], $form_id );

	// 			if ( $element ) {
	// 				return $element;
	// 			}
	// 		}
	// 	}

	// 	return false;
	// }
}
new PP_Faq_Schema();
