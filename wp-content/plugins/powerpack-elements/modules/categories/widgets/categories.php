<?php
namespace PowerpackElements\Modules\Categories\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Posts_Helper;

// Elementor Classes
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Typography;
use Elementor\Scheme_Color;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Categories Widget
 */
class Categories extends Powerpack_Widget {

	/**
	 * Retrieve categories widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Categories' );
	}

	/**
	 * Retrieve categories widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Categories' );
	}

	/**
	 * Retrieve categories widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Categories' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Categories' );
	}

	/**
	 * Retrieve the list of scripts the logo carousel widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'jquery-swiper',
			'pp-treeview',
			'powerpack-frontend',
		);
	}

	/**
	 * Register categories widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * Remove this after Elementor v3.8.0
	 *
	 * @access protected
	 */
	protected function _register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$this->register_controls();
	}

	/**
	 * Register FAQ widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Content Tab */
		$this->register_content_content_controls();
		$this->register_content_layout_controls();
		$this->register_content_carousel_settings_controls();

		/* Style Tab */
		$this->register_style_layout_controls();
		$this->register_style_box_controls();
		$this->register_style_list_controls();
		$this->register_style_cat_content_controls();
		$this->register_style_title_controls();
		$this->register_style_posts_count_controls();
		$this->register_style_cat_description_controls();
		$this->register_style_overlay_controls();
		$this->register_style_arrows_controls();
		$this->register_style_dots_controls();
	}

	/*
	-----------------------------------------------------------------------------------*/
	/*
	  Content Tab
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Content Tab: Content
	 */
	protected function register_content_content_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Content', 'powerpack' ),
			)
		);

		$post_types    = array();
		$taxonomy_type = array();

		foreach ( PP_Posts_Helper::get_post_types() as $slug => $type ) {

			$taxonomies = PP_Posts_Helper::get_post_taxonomies( $slug );

			if ( ! empty( $taxonomies ) ) {
				$post_types[ $slug ] = $type;

				foreach ( $taxonomies as $tax_slug => $tax ) {
					$taxonomy_type[ $slug ][ $tax_slug ] = $tax->label;
				}
			}
		}

		$this->add_control(
			'post_type',
			array(
				'label'   => __( 'Post Type', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $post_types,
				'default' => 'post',
			)
		);

		foreach ( $post_types as $post_type_slug => $post_type_label ) {

			$taxonomy = PP_Posts_Helper::get_post_taxonomies( $post_type_slug );

			if ( ! empty( $taxonomy ) ) {

				$taxonomy_keys        = array_keys( $taxonomy_type[ $post_type_slug ] );
				$taxonomy_default_val = $taxonomy_keys[0];

				// Taxonomy filter type
				$this->add_control(
					$post_type_slug . '_tax_type',
					array(
						/* translators: %s Label */
						'label'       => __( 'Taxonomy', 'powerpack' ),
						'type'        => Controls_Manager::SELECT,
						'default'     => $taxonomy_default_val,
						'label_block' => true,
						'options'     => $taxonomy_type[ $post_type_slug ],
						'condition'   => array(
							'post_type' => $post_type_slug,
						),
					)
				);

				foreach ( $taxonomy as $index => $tax ) {
					$terms = get_terms( $index );

					$tax_terms = array();

					if ( ! empty( $terms ) ) {

						foreach ( $terms as $term_index => $term_obj ) {

							$tax_terms[ $term_obj->term_id ] = $term_obj->name;
						}

						$this->add_control(
							'tax_' . $post_type_slug . '_' . $index . '_filter_rule',
							array(
								'label'       => sprintf( __( '%s Filter Rule', 'powerpack' ), $tax->label ),
								'type'        => Controls_Manager::SELECT,
								'label_block' => true,
								'default'     => 'all',
								'options'     => array(
									'all'     => __( 'Show All', 'powerpack' ),
									'top'     => __( 'Only Top Level', 'powerpack' ),
									'include' => sprintf( __( 'Match These %s', 'powerpack' ), $tax->label ),
									'exclude' => sprintf( __( 'Exclude These %s', 'powerpack' ), $tax->label ),
									'child'   => sprintf( __( 'Child Categories', 'powerpack' ), $tax->label ),
								),
								'condition'   => array(
									'post_type' => $post_type_slug,
									$post_type_slug . '_tax_type' => $index,
								),
							)
						);

						// Add control for all taxonomies.
						$this->add_control(
							'tax_' . $post_type_slug . '_' . $index . '_parent',
							array(
								'label'       => __( 'Parent Type', 'powerpack' ),
								'type'        => Controls_Manager::SELECT,
								'multiple'    => false,
								'default'     => 'current_cat',
								'label_block' => true,
								'options'     => array(
									'current_cat' => __( 'Current Category', 'powerpack' ),
									'sel_parent'  => __( 'Choose Parent', 'powerpack' ),
								),
								'condition'   => array(
									'post_type' => $post_type_slug,
									$post_type_slug . '_tax_type' => $index,
									'tax_' . $post_type_slug . '_' . $index . '_filter_rule' => 'child',
								),
							)
						);

						$this->add_control(
							'tax_' . $post_type_slug . '_' . $index . '_child_notice',
							array(
								'raw'             => __( 'Current category option works best on Category Archive pages.', 'powerpack' ),
								'type'            => Controls_Manager::RAW_HTML,
								'content_classes' => 'pp-editor-info',
								'condition'       => array(
									'post_type' => $post_type_slug,
									$post_type_slug . '_tax_type' => $index,
									'tax_' . $post_type_slug . '_' . $index . '_filter_rule' => 'child',
									'tax_' . $post_type_slug . '_' . $index . '_parent' => 'current_cat',
								),
							)
						);

						// Add control for all taxonomies.
						$this->add_control(
							'tax_' . $post_type_slug . '_' . $index . '_parent_term',
							array(
								'label'       => __( 'Parent Category', 'powerpack' ),
								'type'        => Controls_Manager::SELECT2,
								'multiple'    => false,
								'default'     => '',
								'label_block' => true,
								'options'     => $tax_terms,
								'condition'   => array(
									'post_type' => $post_type_slug,
									$post_type_slug . '_tax_type' => $index,
									'tax_' . $post_type_slug . '_' . $index . '_filter_rule' => 'child',
									'tax_' . $post_type_slug . '_' . $index . '_parent' => 'sel_parent',
								),
							)
						);

						// Add control for all taxonomies.
						$this->add_control(
							'tax_' . $post_type_slug . '_' . $index,
							array(
								'label'       => $tax->label,
								'type'        => Controls_Manager::SELECT2,
								'multiple'    => true,
								'default'     => '',
								'label_block' => true,
								'options'     => $tax_terms,
								'condition'   => array(
									'post_type' => $post_type_slug,
									$post_type_slug . '_tax_type' => $index,
									'tax_' . $post_type_slug . '_' . $index . '_filter_rule' => array( 'include', 'exclude', 'related' ),
								),
							)
						);

					}
				}
			}
		}

		$this->add_control(
			'display_empty_cat',
			array(
				'label'        => __( 'Display Empty Categories', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'DESC' => __( 'Descending', 'powerpack' ),
					'ASC'  => __( 'Ascending', 'powerpack' ),
				),
				'default' => 'ASC',
			)
		);

		$this->add_control(
			'orderby',
			[
				'label'             => __( 'Order By', 'powerpack' ),
				'type'              => Controls_Manager::SELECT,
				'options'           => [
					'name'  => __( 'Name', 'powerpack' ),
					'count' => __( 'Taxonomy Count', 'powerpack' ),
				],
				'default'           => 'name',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Layout
	 */
	protected function register_content_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'powerpack' ),
			)
		);

		$this->add_control(
			'skin',
			array(
				'label'   => __( 'Skin', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'classic' => __( 'Classic', 'powerpack' ),
					'cover'   => __( 'Cover', 'powerpack' ),
					'list'    => __( 'List', 'powerpack' ),
				),
				'default' => 'classic',
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'              => __( 'Layout', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'grid'     => __( 'Grid', 'powerpack' ),
					'carousel' => __( 'Carousel', 'powerpack' ),
				),
				'default'            => 'grid',
				'frontend_available' => true,
				'condition'          => array(
					'skin!' => 'list',
				),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'              => __( 'Columns', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => '3',
				'tablet_default'     => '2',
				'mobile_default'     => '1',
				'options'            => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'prefix_class'       => 'elementor-grid%s-',
				'frontend_available' => true,
				'condition'          => array(
					'skin!' => 'list',
				),
			)
		);

		$this->add_control(
			'list_style',
			array(
				'label'        => __( 'List Style', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'stacked',
				'options'      => array(
					'inline'  => __( 'Inline', 'powerpack' ),
					'stacked' => __( 'Stacked', 'powerpack' ),
				),
				'prefix_class' => 'pp-category-list-style-',
				'condition'    => array(
					'skin' => 'list',
				),
			)
		);

		$this->add_control(
			'list_icon_type',
			array(
				'label'       => esc_html__( 'List Icon', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => array(
					'none'  => array(
						'title' => esc_html__( 'None', 'powerpack' ),
						'icon'  => 'fa fa-ban',
					),
					'icon'  => array(
						'title' => esc_html__( 'Icon', 'powerpack' ),
						'icon'  => 'fa fa-star',
					),
					'image' => array(
						'title' => esc_html__( 'Image', 'powerpack' ),
						'icon'  => 'fa fa-picture-o',
					),
				),
				'default'     => 'icon',
				'condition'   => array(
					'skin' => 'list',
				),
			)
		);

		$this->add_control(
			'list_icon',
			array(
				'label'     => __( 'Icon', 'powerpack' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-angle-right',
					'library' => 'fa-solid',
				),
				'condition' => array(
					'skin'           => 'list',
					'list_icon_type' => 'icon',
				),
			)
		);

		$this->add_control(
			'list_image_source',
			array(
				'label'     => __( 'Image Source', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'custom_image',
				'options'   => array(
					'category_image' => __( 'Category Images', 'powerpack' ),
					'custom_image'   => __( 'Custom Image', 'powerpack' ),
				),
				'condition' => array(
					'skin'           => 'list',
					'list_icon_type' => 'image',
				),
			)
		);

		$this->add_control(
			'list_image',
			array(
				'label'     => __( 'Image', 'powerpack' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => array(
					'active' => true,
				),
				'default'   => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'condition' => array(
					'skin'              => 'list',
					'list_icon_type'    => 'image',
					'list_image_source' => 'custom_image',
				),
			)
		);

		$this->add_control(
			'equal_height',
			array(
				'label'              => __( 'Equal Height', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'label_on'           => __( 'Show', 'powerpack' ),
				'label_off'          => __( 'Hide', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
				'condition'          => array(
					'skin' => 'classic',
				),
			)
		);

		$this->add_control(
			'cat_thumbnails',
			array(
				'label'        => __( 'Category Thumbnails', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Show', 'powerpack' ),
				'label_off'    => __( 'Hide', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'skin' => 'classic',
				),
			)
		);

		$this->add_control(
			'cat_thumbnails_note',
			array(
				'label'           => '',
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => sprintf( __( '<a href="%s" target="_blank">Click here</a> to enable thumbnail for taxonomies.', 'powerpack' ), admin_url( 'admin.php?page=powerpack-settings&tab=modules' ) ),
				'content_classes' => 'pp-editor-info',
				'conditions'      => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'classic',
								),
								array(
									'name'     => 'cat_thumbnails',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'list',
								),
								array(
									'name'     => 'list_icon_type',
									'operator' => '==',
									'value'    => 'image',
								),
							),
						),
						array(
							'name'     => 'skin',
							'operator' => '==',
							'value'    => 'cover',
						),
					),
				),
			)
		);

		$this->add_control(
			'image_height',
			array(
				'label'       => __( 'Image height', 'powerpack' ),
				'description' => __( 'Leave blank for auto height', 'powerpack' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 50,
				'step'        => 1,
				'default'     => 300,
				'selectors'   => array(
					'{{WRAPPER}} .pp-categories .pp-category-inner img' => 'height: {{SIZE}}px;',
				),
				'condition'   => array(
					'skin'           => 'classic',
					'cat_thumbnails' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'       => 'cat_thumbnails',
				'label'      => __( 'Image Size', 'powerpack' ),
				'default'    => 'medium_large',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'classic',
								),
								array(
									'name'     => 'cat_thumbnails',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'list',
								),
								array(
									'name'     => 'list_icon_type',
									'operator' => '==',
									'value'    => 'image',
								),
							),
						),
						array(
							'name'     => 'skin',
							'operator' => '==',
							'value'    => 'cover',
						),
					),
				),
			)
		);

		$this->add_control(
			'fallback_image',
			array(
				'label'      => __( 'Fallback Image', 'powerpack' ),
				'type'       => Controls_Manager::SELECT,
				'options'    => array(
					''            => __( 'None', 'powerpack' ),
					'placeholder' => __( 'Placeholder', 'powerpack' ),
					'custom'      => __( 'Custom', 'powerpack' ),
				),
				'default'    => 'placeholder',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'classic',
								),
								array(
									'name'     => 'cat_thumbnails',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
						array(
							'name'     => 'skin',
							'operator' => '==',
							'value'    => 'cover',
						),
					),
				),
			)
		);

		$this->add_control(
			'fallback_image_custom',
			array(
				'label'      => __( 'Fallback Image Custom', 'powerpack' ),
				'type'       => Controls_Manager::MEDIA,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'classic',
								),
								array(
									'name'     => 'cat_thumbnails',
									'operator' => '==',
									'value'    => 'yes',
								),
								array(
									'name'     => 'fallback_image',
									'operator' => '==',
									'value'    => 'custom',
								),
							),
						),
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'cover',
								),
								array(
									'name'     => 'fallback_image',
									'operator' => '==',
									'value'    => 'custom',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'cat_title',
			array(
				'label'        => __( 'Category Title', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Show', 'powerpack' ),
				'label_off'    => __( 'Hide', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'cat_title_html_tag',
			array(
				'label'     => __( 'Title HTML Tag', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'div',
				'options'   => array(
					'h1'   => __( 'H1', 'powerpack' ),
					'h2'   => __( 'H2', 'powerpack' ),
					'h3'   => __( 'H3', 'powerpack' ),
					'h4'   => __( 'H4', 'powerpack' ),
					'h5'   => __( 'H5', 'powerpack' ),
					'h6'   => __( 'H6', 'powerpack' ),
					'div'  => __( 'div', 'powerpack' ),
					'span' => __( 'span', 'powerpack' ),
					'p'    => __( 'p', 'powerpack' ),
				),
				'condition' => array(
					'cat_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'posts_count',
			array(
				'label'        => __( 'Posts Count', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Show', 'powerpack' ),
				'label_off'    => __( 'Hide', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'count_text_singular',
			array(
				'label'     => __( 'Count Text (Singular)', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Post', 'powerpack' ),
				'condition' => array(
					'posts_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'count_text_plural',
			array(
				'label'     => __( 'Count Text (Plural)', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Posts', 'powerpack' ),
				'condition' => array(
					'posts_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'cat_description',
			array(
				'label'        => __( 'Category Description', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Show', 'powerpack' ),
				'label_off'    => __( 'Hide', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Carousel Settings
	 * -------------------------------------------------
	 */
	protected function register_content_carousel_settings_controls() {
		$this->start_controls_section(
			'section_carousel_settings',
			array(
				'label'     => __( 'Carousel Settings', 'powerpack' ),
				'condition' => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'slider_speed',
			array(
				'label'       => __( 'Slider Speed', 'powerpack' ),
				'description' => __( 'Duration of transition between slides (in ms)', 'powerpack' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array( 'size' => 600 ),
				'range'       => array(
					'px' => array(
						'min'  => 100,
						'max'  => 3000,
						'step' => 1,
					),
				),
				'size_units'  => '',
				'condition'   => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => __( 'Autoplay', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'separator'    => 'before',
				'condition'    => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'autoplay_speed',
			array(
				'label'      => __( 'Autoplay Speed', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array( 'size' => 2400 ),
				'range'      => array(
					'px' => array(
						'min'  => 500,
						'max'  => 5000,
						'step' => 1,
					),
				),
				'size_units' => '',
				'condition'  => array(
					'layout'   => 'carousel',
					'autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'infinite_loop',
			array(
				'label'        => __( 'Infinite Loop', 'powerpack' ),
				'description'  => '',
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'              => __( 'Pause on Hover', 'powerpack' ),
				'description'        => '',
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
				'condition'          => array(
					'layout'   => 'carousel',
					'autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'grab_cursor',
			array(
				'label'        => __( 'Grab Cursor', 'powerpack' ),
				'description'  => __( 'Shows grab cursor when you hover over the slider', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Show', 'powerpack' ),
				'label_off'    => __( 'Hide', 'powerpack' ),
				'return_value' => 'yes',
				'separator'    => 'before',
				'condition'    => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'navigation_heading',
			array(
				'label'     => __( 'Navigation', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'arrows',
			array(
				'label'        => __( 'Arrows', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'dots',
			array(
				'label'        => __( 'Pagination', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'pagination_type',
			array(
				'label'     => __( 'Pagination Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'bullets',
				'options'   => array(
					'bullets'  => __( 'Dots', 'powerpack' ),
					'fraction' => __( 'Fraction', 'powerpack' ),
				),
				'condition' => array(
					'layout' => 'carousel',
					'dots'   => 'yes',
				),
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'     => __( 'Direction', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => array(
					'auto'  => __( 'Auto', 'powerpack' ),
					'left'  => __( 'Left', 'powerpack' ),
					'right' => __( 'Right', 'powerpack' ),
				),
				'separator' => 'before',
				'condition' => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->end_controls_section();
	}

	/*
	-----------------------------------------------------------------------------------*/
	/*
	  STYLE TAB
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Style Tab: Layout
	 */
	protected function register_style_layout_controls() {
		$this->start_controls_section(
			'section_layout_style',
			array(
				'label' => __( 'Layout', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'       => __( 'Columns Gap', 'powerpack' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 20,
				),
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .pp-categories:not(.pp-categories-carousel) .pp-category-wrap' => 'padding-right: calc( {{SIZE}}{{UNIT}}/2 ); padding-left: calc( {{SIZE}}{{UNIT}}/2 );',
					'{{WRAPPER}} .pp-categories:not(.pp-categories-carousel)' => 'margin-left: calc( -{{SIZE}}{{UNIT}}/2 ); margin-right: calc( -{{SIZE}}{{UNIT}}/2 );',
				),
				'render_type' => 'template',
				'condition'   => array(
					'skin!' => 'list',
				),
			)
		);

		$this->add_responsive_control(
			'row_gap',
			array(
				'label'      => __( 'Rows Gap', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'size' => 20,
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-categories .pp-category-wrap' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'grid',
						),
						array(
							'name'     => 'skin',
							'operator' => '==',
							'value'    => 'list',
						),
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Box
	 */
	protected function register_style_box_controls() {
		$this->start_controls_section(
			'section_box_style',
			array(
				'label'     => __( 'Box', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'skin' => array( 'classic', 'cover' ),
				),
			)
		);

		$this->add_control(
			'height',
			array(
				'label'      => __( 'Height', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 100,
						'max'  => 1000,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 300,
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-category-inner' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'skin' => 'cover',
				),
			)
		);

		$this->start_controls_tabs( 'cat_box_tabs_style' );

		$this->start_controls_tab(
			'cat_box_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'cat_box_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-categories .pp-category' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'cat_box_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-categories .pp-category',
			)
		);

		$this->add_control(
			'cat_box_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-categories .pp-category' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'category_box_shadow',
				'selector' => '{{WRAPPER}} .pp-categories .pp-category',
			)
		);

		$this->add_control(
			'category_box_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-categories .pp-category' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'cat_box_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'cat_box_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-categories .pp-category:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'cat_box_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-categories .pp-category:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'category_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-categories .pp-category:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: List
	 */
	protected function register_style_list_controls() {
		$this->start_controls_section(
			'section_list_style',
			array(
				'label'     => __( 'List', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'skin' => 'list',
				),
			)
		);

		$this->add_control(
			'cat_list_background',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-categories-list .pp-category' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'skin' => 'list',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'cat_list_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-categories-list .pp-category',
				'condition'   => array(
					'skin' => 'list',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'cat_list_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-categories-list .pp-category',
				'condition' => array(
					'skin' => 'list',
				),
			)
		);

		$this->add_control(
			'cat_list_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-categories-list .pp-category' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'skin' => 'list',
				),
			)
		);

		$this->add_control(
			'list_icon_heading',
			array(
				'label'     => __( 'List Icon', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'skin'           => 'list',
					'list_icon_type' => 'icon',
				),
			)
		);

		$this->add_control(
			'list_icon_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-categories-list .pp-category-icon' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'skin'           => 'list',
					'list_icon_type' => 'icon',
				),
			)
		);

		$this->add_control(
			'list_icon_size',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min'  => 5,
						'max'  => 100,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-categories-list .pp-category-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'skin'           => 'list',
					'list_icon_type' => 'icon',
				),
			)
		);

		$this->add_control(
			'list_image_heading',
			array(
				'label'     => __( 'List Icon Image', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'skin'           => 'list',
					'list_icon_type' => 'image',
				),
			)
		);

		$this->add_control(
			'list_image_size',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 400,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-categories-list .pp-category-icon img' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'skin'           => 'list',
					'list_icon_type' => 'image',
				),
			)
		);

		$this->add_control(
			'list_icon_spacing',
			array(
				'label'      => __( 'Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-categories-list .pp-category-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'skin'           => 'list',
					'list_icon_type' => array( 'icon', 'image' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Content
	 */
	protected function register_style_cat_content_controls() {
		$this->start_controls_section(
			'section_style_cat_content',
			array(
				'label' => __( 'Category Content', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'cat_content_vertical_align',
			array(
				'label'                => __( 'Vertical Align', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
				'label_block'          => false,
				'default'              => 'middle',
				'options'              => array(
					'top'    => array(
						'title' => __( 'Top', 'powerpack' ),
						'icon'  => 'eicon-v-align-top',
					),
					'middle' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'powerpack' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'selectors'            => array(
					'{{WRAPPER}} .pp-categories-cover .pp-category .pp-category-content-wrap'   => 'justify-content: {{VALUE}};',
				),
				'selectors_dictionary' => array(
					'top'    => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				),
				'condition'            => array(
					'skin' => 'cover',
				),
			)
		);

		$this->add_control(
			'cat_content_horizontal_align',
			array(
				'label'                => __( 'Horizontal Align', 'powerpack' ),
				'type'                 => Controls_Manager::CHOOSE,
				'label_block'          => false,
				'options'              => array(
					'left'    => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'   => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-h-align-right',
					),
					'justify' => array(
						'title' => __( 'Stretch', 'powerpack' ),
						'icon'  => 'eicon-h-align-stretch',
					),
				),
				'default'              => 'center',
				'selectors_dictionary' => array(
					'left'    => 'flex-start',
					'center'  => 'center',
					'right'   => 'flex-end',
					'justify' => 'stretch',
				),
				'selectors'            => array(
					'{{WRAPPER}} .pp-categories-cover .pp-category .pp-category-content-wrap' => 'align-items: {{VALUE}};',
				),
				'condition'            => array(
					'skin' => 'cover',
				),
			)
		);

		$this->add_control(
			'cat_content_text_align',
			array(
				'label'       => __( 'Text Alignment', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .pp-categories .pp-category .pp-category-content' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->start_controls_tabs( 'cat_content_tabs_style' );

		$this->start_controls_tab(
			'cat_content_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'cat_content_background',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-categories .pp-category .pp-category-content' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'cat_content_margin',
			array(
				'label'      => __( 'Margin', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-categories .pp-category .pp-category-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_control(
			'cat_content_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-categories .pp-category .pp-category-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'cat_content_opacity',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 1,
				),
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-categories-cover .pp-category .pp-category-content' => 'opacity: {{SIZE}};',
				),
				'separator' => 'before',
				'condition' => array(
					'skin' => 'cover',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'cat_content_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'cat_content_background_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-categories .pp-category:hover .pp-category-content' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'cat_content_opacity_hover',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 1,
				),
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-categories .product .pp-grid-item:hover .pp-product-cat-content' => 'opacity: {{SIZE}};',
				),
				'separator' => 'before',
				'condition' => array(
					'skin' => 'cover',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Category Title
	 */
	protected function register_style_title_controls() {
		$this->start_controls_section(
			'section_title_style',
			array(
				'label'     => __( 'Category Title', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'cat_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'title_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-category-title' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'cat_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'title_text_color_hover',
			array(
				'label'     => __( 'Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-category:hover .pp-category-title' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'cat_title' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'title_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
				'selector'  => '{{WRAPPER}} .pp-category-title',
				'condition' => array(
					'cat_title' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'title_margin_bottom',
			array(
				'label'      => __( 'Margin Bottom', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-category-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'list',
								),
								array(
									'name'     => 'list_style',
									'operator' => '==',
									'value'    => 'stacked',
								),
							),
						),
						array(
							'name'     => 'skin',
							'operator' => '!=',
							'value'    => 'list',
						),
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Posts Count
	 */
	protected function register_style_posts_count_controls() {
		$this->start_controls_section(
			'section_posts_count_style',
			array(
				'label'     => __( 'Posts Count', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'posts_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'counter_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-category-count' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'posts_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'counter_text_color_hover',
			array(
				'label'     => __( 'Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-category:hover .pp-category-count' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'posts_count' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'counter_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
				'selector'  => '{{WRAPPER}} .pp-category-count',
				'condition' => array(
					'posts_count' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'counter_margin_bottom',
			array(
				'label'      => __( 'Margin Bottom', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-category-count' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => 'skin',
									'operator' => '==',
									'value'    => 'list',
								),
								array(
									'name'     => 'list_style',
									'operator' => '==',
									'value'    => 'stacked',
								),
							),
						),
						array(
							'name'     => 'skin',
							'operator' => '!=',
							'value'    => 'list',
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'counter_margin_left',
			array(
				'label'      => __( 'Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array( 'size' => 5 ),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-category-count' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'skin'        => 'list',
					'posts_count' => 'yes',
					'list_style'  => 'inline',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Category Description
	 */
	protected function register_style_cat_description_controls() {
		$this->start_controls_section(
			'section_cat_description_style',
			array(
				'label'     => __( 'Category Description', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'cat_description' => 'yes',
				),
			)
		);

		$this->add_control(
			'cat_description_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-category-description' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'cat_description' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cat_description_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
				'selector'  => '{{WRAPPER}} .pp-category-description',
				'condition' => array(
					'cat_description' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'cat_description_margin_left',
			array(
				'label'      => __( 'Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array( 'size' => 5 ),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-category-description' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'skin'        => 'list',
					'posts_count' => 'yes',
					'list_style'  => 'inline',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Overlay
	 */
	protected function register_style_overlay_controls() {
		$this->start_controls_section(
			'section_overlay_style',
			array(
				'label'     => __( 'Overlay', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'skin' => 'cover',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_overlay_style' );

		$this->start_controls_tab(
			'tab_overlay_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'skin' => 'cover',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'post_overlay_bg',
				'label'     => __( 'Overlay Background', 'powerpack' ),
				'types'     => array( 'classic', 'gradient' ),
				'exclude'   => array( 'image' ),
				'selector'  => '{{WRAPPER}} .pp-media-overlay',
				'condition' => array(
					'skin' => 'cover',
				),
			)
		);

		$this->add_control(
			'post_overlay_opacity',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-media-overlay' => 'opacity: {{SIZE}};',
				),
				'condition' => array(
					'skin' => 'cover',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_overlay_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'skin' => 'cover',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'post_overlay_bg_hover',
				'label'     => __( 'Overlay Background', 'powerpack' ),
				'types'     => array( 'classic', 'gradient' ),
				'exclude'   => array( 'image' ),
				'selector'  => '{{WRAPPER}} .pp-category:hover .pp-media-overlay',
				'condition' => array(
					'skin' => 'cover',
				),
			)
		);

		$this->add_control(
			'post_overlay_opacity_hover',
			array(
				'label'     => __( 'Opacity', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-category:hover .pp-media-overlay' => 'opacity: {{SIZE}};',
				),
				'condition' => array(
					'skin' => 'cover',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Arrows
	 * -------------------------------------------------
	 */
	protected function register_style_arrows_controls() {
		$this->start_controls_section(
			'section_arrows_style',
			array(
				'label'     => __( 'Arrows', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'select_arrow',
			array(
				'label'                  => __( 'Choose Arrow', 'powerpack' ),
				'type'                   => Controls_Manager::ICONS,
				'fa4compatibility'       => 'arrow',
				'label_block'            => false,
				'default'                => array(
					'value'   => 'fas fa-angle-right',
					'library' => 'fa-solid',
				),
				'skin'                   => 'inline',
				'exclude_inline_options' => 'svg',
				'recommended'            => array(
					'fa-regular' => array(
						'arrow-alt-circle-right',
						'caret-square-right',
						'hand-point-right',
					),
					'fa-solid'   => array(
						'angle-right',
						'angle-double-right',
						'chevron-right',
						'chevron-circle-right',
						'arrow-right',
						'long-arrow-alt-right',
						'caret-right',
						'caret-square-right',
						'arrow-circle-right',
						'arrow-alt-circle-right',
						'toggle-right',
						'hand-point-right',
					),
				),
			)
		);

		$this->add_responsive_control(
			'arrows_size',
			array(
				'label'      => __( 'Arrows Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array( 'size' => '22' ),
				'range'      => array(
					'px' => array(
						'min'  => 15,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'align_arrows',
			array(
				'label'      => __( 'Align Arrows', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => -100,
						'max'  => 40,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next' => 'right: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_arrows_style' );

		$this->start_controls_tab(
			'tab_arrows_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_color_normal',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'arrows_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev',
				'condition'   => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_arrows_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next:hover, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev:hover' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next:hover, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev:hover' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->add_control(
			'arrows_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next:hover, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'arrows_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-button-next, {{WRAPPER}} .swiper-container-wrap .swiper-button-prev' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
				'condition'  => array(
					'layout' => 'carousel',
					'arrows' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Pagination: Dots
	 * -------------------------------------------------
	 */
	protected function register_style_dots_controls() {
		$this->start_controls_section(
			'section_dots_style',
			array(
				'label'     => __( 'Pagination: Dots', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_position',
			array(
				'label'     => __( 'Position', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'inside'  => __( 'Inside', 'powerpack' ),
					'outside' => __( 'Outside', 'powerpack' ),
				),
				'default'   => 'outside',
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_responsive_control(
			'dots_size',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 2,
						'max'  => 40,
						'step' => 1,
					),
				),
				'size_units' => '',
				'selectors'  => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_responsive_control(
			'dots_spacing',
			array(
				'label'      => __( 'Gap Between Dots', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 1,
						'max'  => 30,
						'step' => 1,
					),
				),
				'size_units' => '',
				'selectors'  => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_dots_style' );

		$this->start_controls_tab(
			'tab_dots_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_color_normal',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'active_dot_color_normal',
			array(
				'label'     => __( 'Active Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet-active' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'dots_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet',
				'condition'   => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_border_radius_normal',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_responsive_control(
			'dots_margin',
			array(
				'label'              => __( 'Margin', 'powerpack' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => array( 'px', 'em', '%' ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => array(
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				),
				'selectors'          => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullets' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'          => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dots_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet:hover' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->add_control(
			'dots_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-container-wrap .swiper-pagination-bullet:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'bullets',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Pagination: Dots
	 * -------------------------------------------------
	 */
	protected function register_style_fraction_controls() {
		$this->start_controls_section(
			'section_fraction_style',
			array(
				'label'     => __( 'Pagination: Fraction', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'fraction',
				),
			)
		);

		$this->add_control(
			'fraction_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .swiper-pagination-fraction' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'fraction',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'fraction_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
				'selector'  => '{{WRAPPER}} .swiper-pagination-fraction',
				'condition' => array(
					'layout'          => 'carousel',
					'dots'            => 'yes',
					'pagination_type' => 'fraction',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Slider Settings.
	 *
	 * @access public
	 */
	public function slider_settings() {
		$settings = $this->get_settings();

		$slider_options = array(
			'direction'     => 'horizontal',
			'speed'         => ( $settings['slider_speed']['size'] ) ? $settings['slider_speed']['size'] : 400,
			'effect'        => 'slide',
			'slidesPerView' => ( $settings['columns'] ) ? absint( $settings['columns'] ) : 3,
			'spaceBetween'  => ( $settings['column_gap']['size'] ) ? $settings['column_gap']['size'] : 10,
			'grabCursor'    => ( 'yes' === $settings['grab_cursor'] ),
			'autoHeight'    => true,
			'loop'          => ( 'yes' === $settings['infinite_loop'] ),
		);

		if ( 'yes' === $settings['autoplay'] && ! empty( $settings['autoplay_speed']['size'] ) ) {
			$autoplay_speed = $settings['autoplay_speed']['size'];
		} else {
			$autoplay_speed = 999999;
		}

		$slider_options['autoplay'] = array(
			'delay' => $autoplay_speed,
		);

		if ( 'yes' === $settings['dots'] ) {
			$slider_options['pagination'] = array(
				'el'        => '.swiper-pagination-' . esc_attr( $this->get_id() ),
				'type'      => $settings['pagination_type'],
				'clickable' => true,
			);
		}

		if ( 'yes' === $settings['arrows'] ) {
			$slider_options['navigation'] = array(
				'nextEl' => '.swiper-button-next-' . esc_attr( $this->get_id() ),
				'prevEl' => '.swiper-button-prev-' . esc_attr( $this->get_id() ),
			);
		}

		$elementor_bp_lg = get_option( 'elementor_viewport_lg' );
		$elementor_bp_md = get_option( 'elementor_viewport_md' );
		$bp_desktop      = ! empty( $elementor_bp_lg ) ? $elementor_bp_lg : 1025;
		$bp_tablet       = ! empty( $elementor_bp_md ) ? $elementor_bp_md : 768;
		$bp_mobile       = 320;

		$slider_options['breakpoints'] = array(
			$bp_desktop => array(
				'slidesPerView' => ( $settings['columns'] ) ? absint( $settings['columns'] ) : 3,
				'spaceBetween'  => ( $settings['column_gap']['size'] ) ? $settings['column_gap']['size'] : 10,
			),
			$bp_tablet  => array(
				'slidesPerView' => ( $settings['columns_tablet'] ) ? absint( $settings['columns_tablet'] ) : 2,
				'spaceBetween'  => ( $settings['column_gap_tablet']['size'] ) ? $settings['column_gap_tablet']['size'] : 10,
			),
			$bp_mobile  => array(
				'slidesPerView' => ( $settings['columns_mobile'] ) ? absint( $settings['columns_mobile'] ) : 1,
				'spaceBetween'  => ( $settings['column_gap_mobile']['size'] ) ? $settings['column_gap_mobile']['size'] : 10,
			),
		);

		$this->add_render_attribute(
			'container-wrap',
			array(
				'data-slider-settings' => wp_json_encode( $slider_options ),
			)
		);
	}

	/**
	 * Render category title output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_category_title( $settings, $cat ) {
		if ( 'yes' === $settings['cat_title'] ) {
			printf( '<%s class="pp-category-title">', $settings['cat_title_html_tag'] );
				echo $cat->name;
			printf( '</%s>', $settings['cat_title_html_tag'] );
		}
	}

	/**
	 * Render category title output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_category_description( $settings, $cat ) {
		if ( 'yes' === $settings['cat_description'] ) { ?>
			<div class="pp-category-description">
				<?php echo $cat->category_description; ?>
			</div>
			<?php
		}
	}

	/**
	 * Render category title output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_category_posts_count( $settings, $cat ) {
		if ( 'yes' === $settings['posts_count'] ) {
			?>
			<div class="pp-category-count">
				<?php
					printf(
						esc_html(
							/* translators: number of posts in category */
							_nx(
								'%1$s %2$s',
								'%1$s %3$s',
								$cat->count,
								'posts count',
								'powerpack'
							)
						),
						intval( number_format_i18n( $cat->count ) ),
						$settings['count_text_singular'],
						$settings['count_text_plural']
					);
				?>
			</div>
			<?php
		}
	}

	/**
	 * Render overlay skin output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_cat_thumbnail( $settings, $cat ) {
		$enabled_thumbnails = get_option( 'pp_elementor_taxonomy_thumbnail_enable', 'enabled' );

		$category_image = '';

		$cat_thumb_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
		if ( $enabled_thumbnails ) {
			$taxonomy_thumbnail_id = get_term_meta( $cat->term_id, 'taxonomy_thumbnail_id', true );

			if ( empty( $cat_thumb_id ) ) {
				$cat_thumb_id = $taxonomy_thumbnail_id;
			}
		}
		$category_image = wp_get_attachment_image_src( $cat_thumb_id, $settings['cat_thumbnails_size'] );

		if ( is_array( $category_image ) && ! empty( $category_image ) ) {
			?>
			<img src="<?php echo $category_image[0]; ?>" alt="<?php echo $cat->name; ?>">
			<?php
		} elseif ( 'custom' === $settings['fallback_image'] && ! empty( $settings['fallback_image_custom']['url'] ) ) {
			?>
				<?php echo Group_Control_Image_Size::get_attachment_image_html( $settings, 'cat_thumbnails', 'fallback_image_custom' ); ?>
			<?php
		} elseif ( ! empty( $settings['fallback_image'] ) ) {
			?>
				<img src="<?php echo Utils::get_placeholder_image_src(); ?>" alt="<?php echo $cat->name; ?>">
			<?php
		}
	}

	/**
	 * Render overlay skin output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_skin_classic( $settings, $cat ) {
		?>
		<div class="pp-category-inner">
			<?php
			if ( 'yes' === $settings['cat_thumbnails'] ) {
				$this->render_cat_thumbnail( $settings, $cat );
			}
			?>
			<div class="pp-category-content">
				<?php
					$this->render_category_title( $settings, $cat );

					$this->render_category_posts_count( $settings, $cat );

					$this->render_category_description( $settings, $cat );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render cover skin output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_skin_cover( $settings, $cat ) {
		?>
		<div class="pp-category-inner">
			<?php
				$this->render_cat_thumbnail( $settings, $cat );
			?>
			<div class="pp-media-overlay"></div>
			<div class="pp-category-content-wrap">
				<div class="pp-category-content">
					<?php
						$this->render_category_title( $settings, $cat );

						$this->render_category_posts_count( $settings, $cat );

						$this->render_category_description( $settings, $cat );
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render cover skin output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_skin_list( $settings, $cat ) {
		?>
		<div class="pp-category-inner">
			<div class="pp-category-icon">
				<?php
				if ( 'icon' === $settings['list_icon_type'] ) {
					Icons_Manager::render_icon( $settings['list_icon'], array( 'aria-hidden' => 'true' ) );
				} elseif ( 'image' === $settings['list_icon_type'] ) {
					if ( 'custom_image' === $settings['list_image_source'] ) {
						echo Group_Control_Image_Size::get_attachment_image_html( $settings, 'list_image', 'list_image' );
					} elseif ( 'category_image' === $settings['list_image_source'] ) {
						$this->render_cat_thumbnail( $settings, $cat );
					}
				}
				?>
			</div>
			<div class="pp-category-content">
				<?php
					$this->render_category_title( $settings, $cat );

					$this->render_category_posts_count( $settings, $cat );

					$this->render_category_description( $settings, $cat );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render coupons carousel dots output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_dots() {
		$settings = $this->get_settings_for_display();

		if ( 'yes' === $settings['dots'] ) {
			?>
			<!-- Add Pagination -->
			<div class="swiper-pagination swiper-pagination-<?php echo esc_attr( $this->get_id() ); ?>"></div>
			<?php
		}
	}

	/**
	 * Render coupons carousel arrows output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_arrows() {
		$settings = $this->get_settings_for_display();

		$migration_allowed = Icons_Manager::is_migration_allowed();

		if ( ! isset( $settings['arrow'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default.
			$settings['arrow'] = 'fa fa-angle-right';
		}

		$has_icon = ! empty( $settings['arrow'] );

		if ( ! $has_icon && ! empty( $settings['select_arrow']['value'] ) ) {
			$has_icon = true;
		}

		$migrated = isset( $settings['__fa4_migrated']['select_arrow'] );
		$is_new   = ! isset( $settings['arrow'] ) && $migration_allowed;

		if ( 'yes' === $settings['arrows'] ) {
			?>
			<?php
			if ( $has_icon ) {
				if ( $is_new || $migrated ) {
					$next_arrow = str_replace( 'left', 'right', $settings['select_arrow']['value'] );
					$prev_arrow = str_replace( 'right', 'left', $settings['select_arrow']['value'] );
				} else {
					$next_arrow = $settings['arrow'];
					$prev_arrow = str_replace( 'right', 'left', $settings['arrow'] );
				}
			} else {
				$next_arrow = 'fa fa-angle-right';
				$prev_arrow = 'fa fa-angle-left';
			}
			?>

			<?php if ( ! empty( $settings['arrow'] ) || ( ! empty( $settings['select_arrow']['value'] ) && $is_new ) ) { ?>
				<!-- Add Arrows -->
				<div class="swiper-button-prev swiper-button-prev-<?php echo esc_attr( $this->get_id() ); ?>">
					<i aria-hidden="true" class="<?php echo esc_attr( $prev_arrow ); ?>"></i>
				</div>
				<div class="swiper-button-next swiper-button-next-<?php echo esc_attr( $this->get_id() ); ?>">
					<i aria-hidden="true" class="<?php echo esc_attr( $next_arrow ); ?>"></i>
				</div>
			<?php } ?>
			<?php
		}
	}

	/**
	 * Render categories widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings();

		$this->add_render_attribute( 'container-wrap', 'class', 'pp-categories-wrap' );

		$this->add_render_attribute(
			'container',
			'class',
			array(
				'pp-categories',
				'pp-categories-' . $settings['layout'],
				'pp-categories-' . $settings['skin'],
			)
		);

		if ( 'carousel' === $settings['layout'] ) {
			$this->slider_settings();
		}

		if ( 'right' === $settings['direction'] || ( 'auto' === $settings['direction'] && is_rtl() ) ) {
			$this->add_render_attribute( 'container', 'dir', 'rtl' );
		}

		$this->add_render_attribute( 'grid-wrap', 'class', 'pp-category-wrap' );
		$this->add_render_attribute( 'grid', 'class', 'pp-category' );

		if ( 'list' !== $settings['skin'] ) {
			if ( 'carousel' === $settings['layout'] ) {
				$this->add_render_attribute( 'container-wrap', 'class', 'swiper-container-wrap' );
				if ( 'outside' === $settings['dots_position'] ) {
					$this->add_render_attribute( 'container-wrap', 'class', 'swiper-container-wrap-dots-outside' );
				}
				$this->add_render_attribute(
					'container',
					array(
						'class'           => array( 'swiper-container', 'swiper-container-' . esc_attr( $this->get_id() ) ),
						'data-pagination' => '.swiper-pagination-' . esc_attr( $this->get_id() ),
						'data-arrow-next' => '.swiper-button-next-' . esc_attr( $this->get_id() ),
						'data-arrow-prev' => '.swiper-button-prev-' . esc_attr( $this->get_id() ),
					)
				);
				$this->add_render_attribute( 'wrapper', 'class', 'swiper-wrapper' );
				$this->add_render_attribute( 'grid-wrap', 'class', 'swiper-slide' );
			} else {
				if ( 'list' !== $settings['skin'] ) {
					$this->add_render_attribute( 'wrapper', 'class', 'pp-elementor-grid' );
					$this->add_render_attribute( 'grid-wrap', 'class', 'pp-grid-item-wrap' );
					$this->add_render_attribute( 'grid', 'class', 'pp-grid-item' );
				}
			}
		}

		if ( ! isset( $settings['post_type'] ) ) {
			$post_type = 'post';
		} else {
			$post_type = $settings['post_type'];
		}
		$var_tax_type = $post_type . '_tax_type';

		$taxonomy = $settings[ $var_tax_type ];
		?>
		<div <?php echo $this->get_render_attribute_string( 'container-wrap' ); ?>>
			<div <?php echo $this->get_render_attribute_string( 'container' ); ?>>
				<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
					<?php
						$args = array(
							'taxonomy'      => $taxonomy,
							'order'         => $settings['order'],
							'orderby'       => $settings['orderby'],
							'pad_counts'    => 1,
							'hierarchical'  => 1,
							'hide_empty'    => ( 'yes' === $settings['display_empty_cat'] ) ? false : true,
						);

						$category_filter_type = $settings[ 'tax_' . $post_type . '_' . $taxonomy . '_filter_rule' ];
						$filter_categories    = $settings[ 'tax_' . $post_type . '_' . $taxonomy ];

						if ( 'top' === $category_filter_type ) {
							$args['parent'] = 0;
						}

						if ( 'child' === $category_filter_type ) {
							if ( 'current_cat' === $settings[ 'tax_' . $post_type . '_' . $taxonomy . '_parent' ] ) {
								$term = get_queried_object();
								$term_id = $term->term_id;
							} elseif ( 'sel_parent' === $settings[ 'tax_' . $post_type . '_' . $taxonomy . '_parent' ] ) {
								$term_id = $settings[ 'tax_' . $post_type . '_' . $taxonomy . '_parent_term' ];
							}
							$args['parent'] = $term_id;
						}

						if ( ! empty( $filter_categories ) ) {
							if ( 'include' === $category_filter_type ) {
								$args['include'] = $filter_categories;
							} elseif ( 'exclude' === $category_filter_type ) {
								$args['exclude'] = $filter_categories;
							}
						}

						$all_categories = get_categories( $args );

						if ( $all_categories ) {
							foreach ( $all_categories as $index => $cat ) {
								$term_link = get_term_link( $cat, $taxonomy );
								?>
								<div <?php echo $this->get_render_attribute_string( 'grid-wrap' ); ?> id="pp-cat-<?php echo $cat->term_id; ?>">
									<div <?php echo $this->get_render_attribute_string( 'grid' ); ?>>
										<a href="<?php echo $term_link; ?>" class="pp-category-link">
											<?php
											switch ( $settings['skin'] ) {
												case 'classic':
													$this->render_skin_classic( $settings, $cat );
													break;

												case 'cover':
													$this->render_skin_cover( $settings, $cat );
													break;

												case 'list':
													$this->render_skin_list( $settings, $cat );
													break;
											}
											?>
										</a>
									</div>
								</div>
								<?php
							}
						}
						?>
				</div>
			</div>
			<?php
			if ( 'carousel' === $settings['layout'] ) {
				$this->render_dots();

				$this->render_arrows();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Get post title length.
	 *
	 * @access protected
	 */
	protected function get_cat_description_length( $title ) {
		$settings = $this->get_settings();

		$length = absint( $settings['cat_description_length'] );

		if ( $length ) {
			if ( strlen( $title ) > $length ) {
				$title = substr( $title, 0, $length ) . '&hellip;';
			}
		}

		return $title;
	}
}
