<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('XT_Framework_Plugin_Settings')) {

    /**
     * Plugin Settings, extend admin tabs to support setting panels
     *
     * @package    XT_Framework_Plugin_settings
     * @author     XplodedThemes
     */

    class XT_Framework_Plugin_Settings {

        /**
         * Core class reference.
         *
         * @since    1.0.0
         * @access   private
         * @var      XT_Framework    core    Core Class
         */
        private $core;

        /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         * @var      XT_Framework    core    Core Class
         */
        public function __construct( &$core ) {

            $this->core = $core;

            // Add setting tabs to the rest of the plugin tabs
            $this->core->plugin_loader()->add_filter($this->core->plugin_prefix('admin_tabs'), $this, 'add_admin_tabs', 1, 1);

            // Show settings tab content type
            $this->core->plugin_loader()->add_action($this->core->plugin_prefix('admin_tabs_show_tab'), $this, 'show_settings_tab_panel', 10, 1);

            // Handle settings save
            $this->core->plugin_loader()->add_action('admin_post_'.$this->core->plugin_prefix('plugin_settings_save'), $this, 'save_settings');

        }


        /**
         * Get setting tabs
         *
         * @since    1.0.0
         */
        public function get_setting_tabs() {

             $setting_tabs = apply_filters($this->core->plugin_prefix('plugin_setting_tabs'), array() );

             foreach($setting_tabs as $key => $tab) {

                $setting_tabs[$key]['callback'] = array($this, 'setting_tab_callback');

                $tab['settings'] = apply_filters( $this->core->plugin_prefix('plugin_settings_'.$tab['id']), $tab['settings'] );

                foreach($tab['settings'] as $k => $field) {

                    if(isset($setting_tabs[$key]['settings'][$k]['id'])) {
                        $setting_tabs[$key]['settings'][$k]['id'] = $this->core->plugin_short_prefix($field['id']);
                    }
                }
             }
             return $setting_tabs;
        }

        /**
         * Get setting tab fields by tab id
         *
         * @var      array    $tab_id          Tab ID
         * @since    1.0.0
         */
        public function get_setting_tab_fields($tab_id) {

            $setting_tabs = $this->get_setting_tabs();

            $tab_key = array_search($tab_id, array_column($setting_tabs, 'id'));
            $tab = $setting_tabs[$tab_key];

            return ((!empty($tab['settings']))) ? $tab['settings'] : array();
        }

        /**
         * Get all setting tab fields
         *
         * @since    1.0.0
         */
        public function get_all_setting_tab_fields() {

            $fields = array();
            $setting_tabs = $this->get_setting_tabs();

            foreach($setting_tabs as $tab) {

                $fields = array_merge($fields, $tab['settings']);
            }

            return $fields;
        }

        /**
         * Get all setting tab admin actions
         *
         * @since    1.0.0
         */
        public function get_all_setting_tab_admin_actions() {

            $fields = $this->get_all_setting_tab_fields();

            $fields = array_filter($fields, function($field) {
                return !empty($field['id']) && !empty($field['type']) && empty($field['ajax']) && $field['type'] === 'admin_action';
            });

            return array_map(function($field) {
                return array(
                    'id' =>  $field['id'],
                    'callback' => $field['callback']
                );
            }, $fields);
        }

        /**
         * Check of an admin action exists
         *
         * @since    1.0.0
         */
        public function get_admin_action_callback($action) {

            $actions = $this->get_all_setting_tab_admin_actions();

            $actions = array_filter($actions, function($field) use ($action) {
                return $field['id'] === $action;
            });

            $action = array_shift($actions);

            return !empty($action) && !empty(['callback']) ? $action['callback'] : null;
        }

        /**
         * Add admin tabs
         *
         * @since    1.0.0
         * @var      array    $tabs          tabs
         */
        public function add_admin_tabs($tabs) {

            $setting_tabs = $this->get_setting_tabs();

            return array_merge($setting_tabs, $tabs);
        }


        /**
         * Show settings tab content
         *
         * @since    1.0.0
         * @var      array    $tab          tab
         * @var      string   $tab_type     tab type
         */
        public function show_settings_tab_panel($tab) {

            if (!empty($tab['settings'])) {

                $this->render_settings_form($tab['id'], $tab['settings']);
            }
        }

         /**
         * Output settings form.
         *
         * Loops though the setting fields array and outputs each field.
         *
         * @param string $tab_id Tab ID
         * @param array[] $settings Opens array to output.
         */
        public function render_settings_form($tab_id, &$settings) {

            do_action($this->core->plugin_prefix('plugin_settings_rendering'), $tab_id);
            do_action($this->core->plugin_prefix('plugin_settings_'.$tab_id.'_rendering'));

            ?>
            <form class="xtfw-settings-form" method="post" action="admin-post.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $this->core->plugin_prefix('plugin_settings_save');?>" />
                <input type="hidden" name="tab" value="<?php echo $tab_id;?>" />
                <?php
                    wp_nonce_field( $this->core->plugin_prefix('plugin_settings_save_verify') );
                    $this->render_settings($settings);
                ?>
                <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'xt-framework' ) ?>" />
            </form>
            <?php

            do_action($this->core->plugin_prefix('plugin_settings_rendered'), $tab_id);
            do_action($this->core->plugin_prefix('plugin_settings_'.$tab_id.'_rendered'));
        }


         /**
         * Output settings fields.
         *
         * Loops though the setting fields array and outputs each field.
         *
         * @param array[] $settings Opens array to output.
         */
        public function render_settings(&$settings) {

            foreach ( $settings as $value ) {
                if ( ! isset( $value['type'] ) ) {
                    continue;
                }
                if ( ! isset( $value['id'] ) ) {
                    $value['id'] = '';
                }
                if ( ! isset( $value['title'] ) ) {
                    $value['title'] = isset( $value['name'] ) ? $value['name'] : '';
                }
                if ( ! isset( $value['class'] ) ) {
                    $value['class'] = '';
                }
                if ( ! isset( $value['css'] ) ) {
                    $value['css'] = '';
                }
                if ( ! isset( $value['default'] ) ) {
                    $value['default'] = '';
                }
                if ( ! isset( $value['desc'] ) ) {
                    $value['desc'] = '';
                }
                if ( ! isset( $value['desc_tip'] ) ) {
                    $value['desc_tip'] = false;
                }
                if ( ! isset( $value['placeholder'] ) ) {
                    $value['placeholder'] = '';
                }
                if ( ! isset( $value['suffix'] ) ) {
                    $value['suffix'] = '';
                }

                // Custom attribute handling.
                $custom_attributes = array();

                if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
                    foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
                        $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                    }
                }

                // Description handling.
                $field_description = $this->get_field_description( $value );
                $description       = $field_description['description'];
                $tooltip_html      = $field_description['tooltip_html'];

                // Switch based on type.
                switch ( $value['type'] ) {

                    // Section Titles.
                    case 'title':
                        if ( ! empty( $value['title'] ) ) {
                            echo '<h2>' . esc_html( $value['title'] ) . '</h2>';
                        }
                        if ( ! empty( $value['desc'] ) ) {
                            echo '<div id="' . esc_attr( sanitize_title( $value['id'] ) ) . '-description">';
                            echo wp_kses_post( wpautop( wptexturize( $value['desc'] ) ) );
                            echo '</div>';
                        }
                        echo '<table class="form-table">' . "\n\n";
                        if ( ! empty( $value['id'] ) ) {
                            do_action( $this->core->plugin_prefix('plugin_settings_') . sanitize_title( $value['id'] ) );
                        }
                        break;

                    // Section Ends.
                    case 'sectionend':
                        if ( ! empty( $value['id'] ) ) {
                            do_action( $this->core->plugin_prefix('plugin_settings_') . sanitize_title( $value['id'] ) . '_end' );
                        }
                        echo '</table>';
                        if ( ! empty( $value['id'] ) ) {
                            do_action( $this->core->plugin_prefix('plugin_settings_') . sanitize_title( $value['id'] ) . '_after' );
                        }
                        break;


                    // Standard text inputs and subtypes like 'number'.
                    case 'text':
                    case 'password':
                    case 'datetime':
                    case 'datetime-local':
                    case 'date':
                    case 'month':
                    case 'time':
                    case 'week':
                    case 'number':
                    case 'email':
                    case 'url':
                    case 'tel':
                        $option_value = $this->get_option( $value['id'], $value['default'] );

                        ?><tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
                            </th>
                            <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                                <input
                                    name="<?php echo esc_attr( $value['id'] ); ?>"
                                    id="<?php echo esc_attr( $value['id'] ); ?>"
                                    type="<?php echo esc_attr( $value['type'] ); ?>"
                                    style="<?php echo esc_attr( $value['css'] ); ?>"
                                    value="<?php echo esc_attr( $option_value ); ?>"
                                    class="<?php echo esc_attr( $value['class'] ); ?>"
                                    placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                    <?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
                                />
                                <?php echo esc_html( $value['suffix'] ); ?> <?php echo $description; // WPCS: XSS ok. ?>
                            </td>
                        </tr>
                        <?php
                        break;


                    case 'image':

                        if ( ! empty( $value['image'] ) ) {
                        ?>
                        <tr valign="top">
                            <th scope="row" colspan="2">

                                <?php if(!empty($value['link'])): ?>
                                <a href="<?php echo esc_url($value['link']);?>">
                                <?php endif; ?>

                                    <img width="100%" class="xtfw-settings-image" src="<?php echo esc_url( $value['image'] );?>" />

                                    <?php if(!empty($value['image_mobile'])): ?>
                                        <img width="100%" class="xtfw-settings-image-mobile" src="<?php echo esc_url( $value['image_mobile'] );?>" />
                                    <?php endif; ?>

                                <?php if(!empty($value['link'])): ?>
                                </a>
                                <?php endif; ?>

                            </th>
                        </tr>
                        <?php
                        }
                        break;

                    // Color picker.
                    case 'color':
                        $option_value = $this->get_option( $value['id'], $value['default'] );

                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
                            </th>
                            <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">&lrm;
                                <span class="colorpickpreview" style="background: <?php echo esc_attr( $option_value ); ?>">&nbsp;</span>
                                <input
                                    name="<?php echo esc_attr( $value['id'] ); ?>"
                                    id="<?php echo esc_attr( $value['id'] ); ?>"
                                    type="text"
                                    dir="ltr"
                                    style="<?php echo esc_attr( $value['css'] ); ?>"
                                    value="<?php echo esc_attr( $option_value ); ?>"
                                    class="<?php echo esc_attr( $value['class'] ); ?>colorpick"
                                    placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                    <?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
                                    />&lrm; <?php echo $description; // WPCS: XSS ok. ?>
                                    <div id="colorPickerDiv_<?php echo esc_attr( $value['id'] ); ?>" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Textarea.
                    case 'textarea':
                        $option_value = $this->get_option( $value['id'], $value['default'] );

                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
                            </th>
                            <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">

                                <textarea
                                    name="<?php echo esc_attr( $value['id'] ); ?>"
                                    id="<?php echo esc_attr( $value['id'] ); ?>"
                                    style="<?php echo esc_attr( $value['css'] ); ?>"
                                    class="<?php echo esc_attr( $value['class'] ); ?>"
                                    placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                    <?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
                                ><?php echo esc_textarea( $option_value ); // WPCS: XSS ok. ?></textarea>
                                <?php echo $description; // WPCS: XSS ok. ?>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Select boxes.
                    case 'select':
                    case 'multiselect':
                        $option_value = $this->get_option( $value['id'], $value['default'] );

                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
                            </th>
                            <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                                <select
                                    name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
                                    id="<?php echo esc_attr( $value['id'] ); ?>"
                                    style="<?php echo esc_attr( $value['css'] ); ?>"
                                    class="xtfw-select <?php echo esc_attr( $value['class'] ); ?>"
                                    <?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
                                    <?php echo 'multiselect' === $value['type'] ? 'multiple="multiple"' : ''; ?>
                                    >
                                    <?php
                                    foreach ( $value['options'] as $key => $val ) {
                                        ?>
                                        <option value="<?php echo esc_attr( $key ); ?>"
                                            <?php

                                            if ( is_array( $option_value ) ) {
                                                selected( in_array( (string) $key, $option_value, true ), true );
                                            } else {
                                                selected( $option_value, (string) $key );
                                            }

                                        ?>
                                        ><?php echo esc_html( $val ); ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <?php echo $description; // WPCS: XSS ok. ?>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Radio inputs.
                    case 'radio':
                        $option_value = $this->get_option( $value['id'], $value['default'] );

                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
                            </th>
                            <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                                <fieldset>
                                    <ul>
                                    <?php
                                    foreach ( $value['options'] as $key => $val ) {
                                        ?>
                                        <li>
                                            <label>
                                            <input
                                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                                value="<?php echo esc_attr( $key ); ?>"
                                                type="radio"
                                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                                <?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
                                                <?php checked( $key, $option_value ); ?>
                                            />
                                            <?php echo esc_html( $val ); ?></label>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                    </ul>
                                    <?php echo $description; // WPCS: XSS ok. ?>
                                </fieldset>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Checkbox input.
                    case 'checkbox':
                        $option_value     = $this->get_option( $value['id'], $value['default'] );
                        $visibility_class = array();

                        if ( ! isset( $value['hide_if_checked'] ) ) {
                            $value['hide_if_checked'] = false;
                        }
                        if ( ! isset( $value['show_if_checked'] ) ) {
                            $value['show_if_checked'] = false;
                        }
                        if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) {
                            $visibility_class[] = 'hidden_option';
                        }
                        if ( 'option' === $value['hide_if_checked'] ) {
                            $visibility_class[] = 'hide_options_if_checked';
                        }
                        if ( 'option' === $value['show_if_checked'] ) {
                            $visibility_class[] = 'show_options_if_checked';
                        }

                        if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
                            ?>
                                <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
                                    <th scope="row" class="titledesc">
                                        <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                                    </th>
                                    <td class="forminp forminp-checkbox">
                                        <fieldset>
                            <?php
                        } else {
                            ?>
                                <fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
                            <?php
                        }

                        if ( ! empty( $value['title'] ) ) {
                            ?>
                                <legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>
                            <?php
                        }

                        ?>
                            <label for="<?php echo esc_attr( $value['id'] ); ?>">
                                <input
                                    name="<?php echo esc_attr( $value['id'] ); ?>"
                                    id="<?php echo esc_attr( $value['id'] ); ?>"
                                    type="checkbox"
                                    class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
                                    value="1"
                                    <?php checked( $option_value, 'yes' ); ?>
                                    <?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
                                />
                                <?php echo $description; // WPCS: XSS ok. ?>
                            </label>
                            <?php echo $tooltip_html; // WPCS: XSS ok. ?>
                        <?php

                        if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) {
                                        ?>
                                        </fieldset>
                                    </td>
                                </tr>
                            <?php
                        } else {
                            ?>
                                </fieldset>
                            <?php
                        }
                        break;

                    case 'admin_action':

                        if (isset($value['title']) && isset($value['button_text']) && isset($value['id'])) {

                            $classes = $value['class'];

                            if(!empty($value['ajax'])) {
                                $classes .= 'xtfw_ajax_admin_action';
                            }
                            ?>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label for="admin_action">
                                        <?php echo wp_kses_post($value['title']); ?>
                                        <?php echo xtfw_help_tip($value['desc_tip']); ?>
                                    </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <fieldset>
                                        <a data-confirm="<?php echo esc_attr__('Are you sure you want to proceed?', 'xt-framework'); ?> <?php echo esc_attr($field['desc_tip']); ?>"
                                           href="<?php echo esc_url(add_query_arg(array('action' => $value['id']))); ?>"
                                           class="button <?php echo esc_attr($classes); ?>"
                                           id="<?php echo $value['id']; ?>"
                                       >
                                           <?php echo esc_html($value['button_text']); ?>
                                       </a>
                                    </fieldset>
                                </td>
                            </tr>
                            <?php
                            break;
                        }

                    // Single page selects.
                    case 'single_select_page':
                        $args = array(
                            'name'             => $value['id'],
                            'id'               => $value['id'],
                            'sort_column'      => 'menu_order',
                            'sort_order'       => 'ASC',
                            'show_option_none' => ' ',
                            'class'            => $value['class'],
                            'echo'             => false,
                            'selected'         => absint( $this->get_option( $value['id'], $value['default'] ) ),
                            'post_status'      => 'publish,private,draft',
                        );

                        if ( isset( $value['args'] ) ) {
                            $args = wp_parse_args( $value['args'], $args );
                        }

                        ?>
                        <tr valign="top" class="single_select_page">
                            <th scope="row" class="titledesc">
                                <label><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
                            </th>
                            <td class="forminp">
                                <?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'xt-framework' ) . "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); // WPCS: XSS ok. ?>
                                <?php echo $description; // WPCS: XSS ok. ?>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Single country selects.
                    case 'single_select_country':
                        $country_setting = (string) $this->get_option( $value['id'], $value['default'] );

                        if ( strstr( $country_setting, ':' ) ) {
                            $country_setting = explode( ':', $country_setting );
                            $country         = current( $country_setting );
                            $state           = end( $country_setting );
                        } else {
                            $country = $country_setting;
                            $state   = '*';
                        }
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
                            </th>
                            <td class="forminp">
                                <select name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'xt-framework' ); ?>" aria-label="<?php esc_attr_e( 'Country', 'xt-framework' ); ?>" class="wc-enhanced-select">
                                    <?php WC()->countries->country_dropdown_options( $country, $state ); ?>
                                </select>
                                <?php echo $description; // WPCS: XSS ok. ?>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Country multiselects.
                    case 'multi_select_countries':
                        $selections = (array) $this->get_option( $value['id'], $value['default'] );

                        if ( ! empty( $value['options'] ) ) {
                            $countries = $value['options'];
                        } else {
                            $countries = WC()->countries->countries;
                        }

                        asort( $countries );
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
                            </th>
                            <td class="forminp">
                                <select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="width:350px" data-placeholder="<?php esc_attr_e( 'Choose countries&hellip;', 'xt-framework' ); ?>" aria-label="<?php esc_attr_e( 'Country', 'xt-framework' ); ?>" class="wc-enhanced-select">
                                    <?php
                                    if ( ! empty( $countries ) ) {
                                        foreach ( $countries as $key => $val ) {
                                            echo '<option value="' . esc_attr( $key ) . '"' . xtfw_selected( $key, $selections ) . '>' . esc_html( $val ) . '</option>'; // WPCS: XSS ok.
                                        }
                                    }
                                    ?>
                                </select>
                                <?php echo ( $description ) ? $description : ''; // WPCS: XSS ok. ?> <br /><a class="select_all button" href="#"><?php esc_html_e( 'Select all', 'xt-framework' ); ?></a> <a class="select_none button" href="#"><?php esc_html_e( 'Select none', 'xt-framework' ); ?></a>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Days/months/years selector.
                    case 'relative_date_selector':
                        $periods      = array(
                            'days'   => __( 'Day(s)', 'xt-framework' ),
                            'weeks'  => __( 'Week(s)', 'xt-framework' ),
                            'months' => __( 'Month(s)', 'xt-framework' ),
                            'years'  => __( 'Year(s)', 'xt-framework' ),
                        );
                        $option_value = xtfw_parse_relative_date_option( $this->get_option( $value['id'], $value['default'] ) );
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
                            </th>
                            <td class="forminp">
                            <input
                                    name="<?php echo esc_attr( $value['id'] ); ?>[number]"
                                    id="<?php echo esc_attr( $value['id'] ); ?>"
                                    type="number"
                                    style="width: 80px;"
                                    value="<?php echo esc_attr( $option_value['number'] ); ?>"
                                    class="<?php echo esc_attr( $value['class'] ); ?>"
                                    placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                    step="1"
                                    min="1"
                                    <?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
                                />&nbsp;
                                <select name="<?php echo esc_attr( $value['id'] ); ?>[unit]" style="width: auto;">
                                    <?php
                                    foreach ( $periods as $value => $label ) {
                                        echo '<option value="' . esc_attr( $value ) . '"' . selected( $option_value['unit'], $value, false ) . '>' . esc_html( $label ) . '</option>';
                                    }
                                    ?>
                                </select>
                                <?php echo ( $description ) ? $description : ''; // WPCS: XSS ok. ?>
                            </td>
                        </tr>
                        <?php
                        break;

                    // Default: run an action.
                    default:
                        do_action( $this->core->plugin_prefix('plugin_settings_field_') . $value['type'], $value );
                        break;
                }
            }
        }

        /**
         * Helper function to get the formatted description and tip HTML for a
         * given form field. Plugins can call this when implementing their own custom
         * settings types.
         *
         * @param  array $value The form field value array.
         *
         * @return array The description and tip as a 2 element array.
         */
        public function get_field_description( $value ) {
            $description  = '';
            $tooltip_html = '';

            if ( true === $value['desc_tip'] ) {
                $tooltip_html = $value['desc'];
            } elseif ( ! empty( $value['desc_tip'] ) ) {
                $description  = $value['desc'];
                $tooltip_html = $value['desc_tip'];
            } elseif ( ! empty( $value['desc'] ) ) {
                $description = $value['desc'];
            }

            if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
                $description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
            } elseif ( $description && in_array( $value['type'], array( 'checkbox' ), true ) ) {
                $description = wp_kses_post( $description );
            } elseif ( $description ) {
                $description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
            }

            if ( $tooltip_html && in_array( $value['type'], array( 'checkbox' ), true ) ) {
                $tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
            } elseif ( $tooltip_html ) {
                $tooltip_html = xtfw_help_tip( $tooltip_html );
            }

            return array(
                'description'  => $description,
                'tooltip_html' => $tooltip_html,
            );
        }

        /**
         * Save the settings page
         *
         * @since 1.0
         */
        public function save_settings() {

            // Check the nonce.
            check_admin_referer( $this->core->plugin_prefix('plugin_settings_save_verify') );

            // Get the tab settings.
            $tab_id = filter_input(INPUT_POST, 'tab');
            $fields = $this->get_setting_tab_fields($tab_id);

            // Save the settings
            if($this->save_fields( $fields )) {

                $this->core->plugin_notices()->add_success_message( esc_html__('Settings saved successfully!', 'xt-framework') );

                do_action($this->core->plugin_prefix('plugin_settings_saved'), $tab_id);
                do_action($this->core->plugin_prefix('plugin_settings_'.$tab_id.'_saved'));

                // flush rewrite to make sure new endpoint works if any
                flush_rewrite_rules();

            }else{

                $this->core->plugin_notices()->add_error_message( esc_html__('Failed saving settings!', 'xt-framework') );
            }

            // Set redirect args
            $redirect_args = array();
            if(!empty($_GET['sub_id'])) {
                $redirect_args['sub_id'] = filter_input(INPUT_GET, 'sub_id');
            }

            // Go back to the settings page.
            wp_redirect( $this->core->plugin_admin_url( $tab_id, $redirect_args) );
            exit;

        }

        /**
         * Save admin fields.
         *
         * Loops though the plugin options array and outputs each field.
         *
         * @param array $fields Options array to output.
         * @param array $data    Optional. Data to use for saving. Defaults to $_POST.
         *
         * @return bool
         */
        public function save_fields( $fields, $data = null ) {

            if ( is_null( $data ) ) {
                $data = $_POST; // WPCS: input var okay, CSRF ok.
            }

            if ( empty( $data ) ) {
                return false;
            }

            // Options to update will be stored here and saved later.
            $update_options   = array();
            $autoload_options = array();

            // Loop options and get values to save.
            foreach ( $fields as $option ) {
                if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
                    continue;
                }

                // Get posted value.
                if ( strstr( $option['id'], '[' ) ) {
                    parse_str( $option['id'], $option_name_array );
                    $option_name  = current( array_keys( $option_name_array ) );
                    $setting_name = key( $option_name_array[ $option_name ] );
                    $raw_value    = isset( $data[ $option_name ][ $setting_name ] ) ? wp_unslash( $data[ $option_name ][ $setting_name ] ) : null;
                } else {
                    $option_name  = $option['id'];
                    $setting_name = '';
                    $raw_value    = isset( $data[ $option['id'] ] ) ? wp_unslash( $data[ $option['id'] ] ) : null;
                }

                $option_type = $option['type'];

                // Format the value based on option type.
                switch ( $option_type ) {
                    case 'checkbox':
                        $value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
                        break;
                    case 'textarea':
                        $value = wp_kses_post( trim( $raw_value ) );
                        break;
                    case 'multiselect':
                    case 'multi_select_countries':
                        $value = array_filter( array_map( 'xtfw_clean', (array) $raw_value ) );
                        break;
                    case 'image_width':
                        $value = array();
                        if ( isset( $raw_value['width'] ) ) {
                            $value['width']  = xtfw_clean( $raw_value['width'] );
                            $value['height'] = xtfw_clean( $raw_value['height'] );
                            $value['crop']   = isset( $raw_value['crop'] ) ? 1 : 0;
                        } else {
                            $value['width']  = $option['default']['width'];
                            $value['height'] = $option['default']['height'];
                            $value['crop']   = $option['default']['crop'];
                        }
                        break;
                    case 'select':
                        $allowed_values = empty( $option['options'] ) ? array() : array_map( 'strval', array_keys( $option['options'] ) );
                        if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
                            $value = null;
                            break;
                        }
                        $default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
                        $value   = in_array( $raw_value, $allowed_values, true ) ? $raw_value : $default;
                        break;
                    case 'relative_date_selector':
                        $value = xtfw_parse_relative_date_option( $raw_value );
                        break;
                    default:
                        $value = xtfw_clean( $raw_value );
                        break;
                }

                /**
                 * Sanitize the value of an option.
                 *
                 * @since 1.0.0
                 */
                $value = apply_filters( $this->core->plugin_prefix('plugin_settings_sanitize_option'), $value, $option, $raw_value );

                /**
                 * Sanitize the value of an option by option name.
                 *
                 * @since 1.0.0
                 */
                $value = apply_filters( $this->core->plugin_prefix("plugin_settings_sanitize_option_$option_name"), $value, $option, $raw_value );

                /**
                 * Sanitize the value of an option by option type.
                 *
                 * @since 1.0.0
                 */
                $value = apply_filters( $this->core->plugin_prefix("plugin_settings_sanitize_option_type_$option_type"), $value, $option, $raw_value );


                if ( is_null( $value ) ) {
                    continue;
                }

                // Check if option is an array and handle that differently to single values.
                if ( $option_name && $setting_name ) {
                    if ( ! isset( $update_options[ $option_name ] ) ) {
                        $update_options[ $option_name ] = get_option( $option_name, array() );
                    }
                    if ( ! is_array( $update_options[ $option_name ] ) ) {
                        $update_options[ $option_name ] = array();
                    }
                    $update_options[ $option_name ][ $setting_name ] = $value;
                } else {
                    $update_options[ $option_name ] = $value;
                }

                $autoload_options[ $option_name ] = isset( $option['autoload'] ) ? (bool) $option['autoload'] : true;
            }

            // Save all options in our array.
            foreach ( $update_options as $name => $value ) {

                update_option( $name, $value, $autoload_options[ $name ] ? 'yes' : 'no' );
            }

            return true;
        }


        /**
         * Get a setting from the settings API.
         *
         * @param string $option_name Option name.
         * @param mixed  $default     Default value.
         *
         * @return mixed
         */
        public function get_option( $option_name, $default = '' ) {

            $prefix = $this->core->plugin_short_prefix();
            $option_name = str_replace($prefix.'_', "", $option_name);

            $option_name = $this->core->plugin_short_prefix($option_name);

            // Array value.
            if ( strstr( $option_name, '[' ) ) {

                parse_str( $option_name, $option_array );

                // Option name is first key.
                $option_name = current( array_keys( $option_array ) );

                // Get value.
                $option_values = get_option( $option_name, '' );

                $key = key( $option_array[ $option_name ] );

                if ( isset( $option_values[ $key ] ) ) {
                    $option_value = $option_values[ $key ];
                } else {
                    $option_value = null;
                }
            } else {
                // Single value.
                $option_value = get_option( $option_name, null );
            }

            if ( is_array( $option_value ) ) {
                $option_value = array_map( 'stripslashes', $option_value );
            } elseif ( ! is_null( $option_value ) ) {
                $option_value = stripslashes( $option_value );
            }

            return ( null === $option_value ) ? $default : $option_value;
        }

        /**
         * Get a bool setting from the settings API.
         *
         * @param string $option_name Option name.
         * @param mixed  $default     Default value.
         *
         * @return bool
         */
        public function get_option_bool( $option_name, $default = false ) {

            $value = $this->get_option($option_name, $default);

            return ($value === 'yes' || $value === '1' || $value === true);
        }

        /**
         * Get settings page sub section id
         *
         * @since 1.0
         */
        public function get_settings_section_id() {

            return $this->core->plugin_tabs()->get_tab_id() !== '' && isset($_GET['sub_id']) ? $_GET['sub_id'] : null;
        }

        /**
         * Setting tab callback function
         *
         * @since    1.0.0
         */
        public function setting_tab_callback() {

            $this->enqueue_assets();

            // Handle admin actions
            $current_action = (empty($_REQUEST['action'])) ? null : sanitize_text_field(urldecode($_REQUEST['action']));
            if(!empty($current_action)) {
                $callback = $this->get_admin_action_callback($current_action);
                if(!empty($callback)) {
                    call_user_func($callback);
                }
            }

            do_action($this->core->plugin_prefix('plugin_settings_handle_actions'));

            $this->core->framework_notices()->render_backend_messages();
        }


        /**
         * Register the JavaScript & stylesheets for the settings tabs area.
         *
         * @since    1.0.0
         */
        public function enqueue_assets() {

            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'jquery-ui-accordion' );
            wp_enqueue_script( 'selectWoo' );

            wp_enqueue_style( $this->core->framework_prefix('jquery-ui'), xtfw_dir_url( XTFW_DIR_ASSETS ) . '/css/jquery-ui/jquery-ui.css', array(), XTFW_VERSION, false );
            wp_enqueue_style( $this->core->framework_prefix('jquery-tiptip'), xtfw_dir_url( XTFW_DIR_ASSETS ) . '/css/jquery-tiptip.css', array(), XTFW_VERSION, false );
            wp_enqueue_script( $this->core->framework_prefix('jquery-tiptip'), xtfw_dir_url( XTFW_DIR_ASSETS ) . '/js/jquery.tiptip'.XTFW_SCRIPT_SUFFIX.'.js', array( 'jquery' ), XTFW_VERSION, false );

            wp_enqueue_style( $this->core->framework_prefix('plugin-settings'), xtfw_dir_url( XTFW_DIR_PLUGIN_SETTINGS_ASSETS ) . '/css/plugin-settings.css', array(), XTFW_VERSION, 'all' );

            if(!empty($_GET['premium_css'])) {
                wp_enqueue_style( $this->core->framework_prefix('plugin-settings-premium'), xtfw_dir_url( XTFW_DIR_PLUGIN_SETTINGS_ASSETS ) . '/css/plugin-settings-premium.css', array(), XTFW_VERSION, 'all' );
            }

            wp_register_script( $this->core->framework_prefix('plugin-settings'), xtfw_dir_url( XTFW_DIR_PLUGIN_SETTINGS_ASSETS ) . '/js/plugin-settings'.XTFW_SCRIPT_SUFFIX.'.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-accordion'), XTFW_VERSION, false );

            wp_localize_script(
                $this->core->framework_prefix('plugin-settings'),
                'xtfw_plugin_settings',
                array(
                    'assets_url' => xtfw_dir_url(XTFW_DIR_PLUGIN_SETTINGS_ASSETS),
                    'sub_id' => $this->get_settings_section_id(),
                )
            );

            wp_enqueue_script($this->core->framework_prefix('plugin-settings'));

        }
    }
}