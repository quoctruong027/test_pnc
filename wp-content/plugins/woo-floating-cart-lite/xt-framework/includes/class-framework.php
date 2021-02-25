<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * XT Framework Core class.
 *
 * This class will be extended by XT Plugins and handles all common things between plugins
 *
 * @since      1.0.0
 * @package    XT_Framework
 * @subpackage XT_Framework/includes
 * @author     XplodedThemes
 *
 * Constructor requires plugin params array:
 *
 * array(
 *   'version'       => 'x.x.x',
 *   'name'          => 'Plugin Name',
 *   'menu_name'     => 'Plugin Menu Name',
 *   'url'           => 'https://xplodedthemes.com/products/plugin-slug',
 *   'icon'          => 'dashicons-icon',
 *   'slug'          => 'slug',
 *   'prefix'        => 'prefix',
 *   'short_prefix'  => 'short_prefix',
 *   'market'        => 'market',
 *   'markets'       => array(
 *      'freemius' => array(
 *          'id' => 0,
 *          'key' => 'public_key',
 *          'url' => 'https://xplodedthemes.com/products/plugin-slug',
 *          'premium_slug'  => 'premium-slug',
 *          'freemium_slug' => 'freemium-slug',
 *      ),
 *      'envato' => array(
 *          'id' => 0,
 *          'url' => 'https://codecanyon.net/item/plugin-slug'
 *      )
 *   ),
 *   'dependencies' => array(
 *      array(
 *          'class' => 'WooCommerce',
 *          'name'  => 'WooCommerce',
 *          'url'   => 'https://en-ca.wordpress.org/plugins/woocommerce/'
 *      ),
 *      ...
 *   ),
 *   'conflicts' => array(
 *      array(
 *          'name'  => 'Plugin Name',
 *          'path' => 'plugin-path/plugin-file.php',
 *      ),
 *      ...
 *   ),
 *   'top_menu'     => false
 *   'file'         => __FILE__
 * );
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
abstract class XT_Framework
{
    /**
     * Var that holds an array of all instances instances loaded
     *
     * @since    1.0.0
     * @access   protected
     * @var      array $instances
     */
    private static  $_instances = array() ;
    /**
     * Var that holds the plugin object.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Plugin $plugin
     */
    protected  $plugin ;
    /**
     * Dependencies Check instance to check if the plugin has passed required dependencies and is ready to be initialised.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Dependencies_Check dependencies_check
     */
    protected  $dependencies_check ;
    /**
     * Var that holds the framework slug.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $framework_slug
     */
    protected  $framework_slug = 'xt-plugins' ;
    /**
     * Var that holds the framework prefix.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $framework_prefix
     */
    protected  $framework_prefix = 'xt_framework' ;
    /**
     * Var that holds the framework short prefix.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $framework_short_prefix
     */
    protected  $framework_short_prefix = 'xtfw' ;
    /**
     * Var that holds the framework name.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $framework_name
     */
    protected  $framework_name = 'XT Plugins Framework' ;
    /**
     * Var that holds the framework menu name.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $framework_menu_name
     */
    protected  $framework_menu_name = 'XT Plugins' ;
    /**
     * The loader that's responsible for maintaining and registering all hooks that power the framework.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Loader $framework_loader
     */
    protected  $framework_loader ;
    /**
     * Framework Notices Instance
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Notices $framework_notices
     */
    protected  $framework_notices ;
    /**
     * The framework tabs instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Framework_Tabs $framework_tabs
     */
    protected  $framework_tabs ;
    /**
     * The access manager that's responsible for access management.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Access_Manager|Freemius $access_manager
     */
    protected  $access_manager ;
    /**
     * Base hooks responsible plugin activation, deactivation and uninstall hooks
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Base_Hooks $plugin_base_hooks
     */
    protected  $plugin_base_hooks ;
    /**
     * The loader that's responsible for maintaining and registering all hooks that power the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Loader $plugin_loader
     */
    protected  $plugin_loader ;
    /**
     * Admin Notices Instance
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Notices $plugin_notices
     */
    protected  $plugin_notices ;
    /**
     * Admin Messages Instance
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Admin_Messages $admin_messages
     */
    protected  $admin_messages ;
    /**
     * The system status instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_System_Status $system_status
     */
    protected  $system_status ;
    /**
     * The plugin locale instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_i18n $plugin_locale
     */
    protected  $plugin_locale ;
    /**
     * The plugin migrations instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Migration $plugin_migrations
     */
    protected  $plugin_migrations ;
    /**
     * The plugin modules.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Modules $modules
     */
    protected  $modules ;
    /**
     * The plugin customizer instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Customizer $customizer
     */
    protected  $customizer ;
    /**
     * The plugin settings instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Plugin_settings $plugin_settings
     */
    protected  $plugin_settings ;
    /**
     * The admin tabs instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Plugin_Tabs $plugin_tabs
     */
    protected  $plugin_tabs ;
    /**
     * The WC Ajax instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_WC_Ajax $wc_ajax
     */
    protected  $wc_ajax ;
    /**
     * The Cache instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Cache $cache
     */
    protected  $cache ;
    /**
     * The Transient instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Transient $transient
     */
    protected  $transient ;
    /**
     * Var that holds the public class instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Object $frontend
     */
    protected  $frontend ;
    /**
     * Var that holds the admin class instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Object $backend
     */
    protected  $backend ;
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct( $params )
    {
        if ( self::instance_exists( $params['slug'] ) ) {
            return self::get_instance( $params['slug'] );
        }
        $this->init_plugin( $params );
        $this->plugin_loader()->add_action( 'plugins_loaded', $this, 'bootstrap' );
        return $this;
    }
    
    // --------- INIT METHODS ------------- //
    /**
     * Initialize plugin instance from params
     *
     * @param $params
     * @since    1.0.0
     * @access   private
     */
    private function init_plugin( $params )
    {
        
        if ( empty($this->plugin) ) {
            $this->plugin = new XT_Framework_Plugin( $params );
            // Register the plugin globally
            self::register_instance( $this->plugin->slug, $this );
        }
    
    }
    
    /**
     * Bootstrap plugin
     *
     * @since    1.0.0
     * @access   public
     */
    public function bootstrap()
    {
        // Loaders needs to be initiated first
        $this->init_framework_loader();
        $this->init_plugin_loader();
        
        if ( is_admin() ) {
            $this->framework_loader()->add_action( 'admin_enqueue_scripts', $this, 'enqueue_framework_assets' );
            $this->init_framework_notices();
            $this->init_plugin_notices();
        }
        
        $this->init_conflicts_check();
        $this->init_dependencies_check();
        if ( $this->plugin_dependencies()->passed() ) {
            $this->load_plugin();
        }
    }
    
    /**
     * Dependencies checked, Load Plugin
     *
     * @since    1.0.0
     * @access   public
     */
    public function load_plugin()
    {
        $this->init_cache();
        $this->init_transient();
        $this->init_plugin_frontend_notices();
        $this->init_access_manager();
        $this->init_base_hooks();
        $this->init_plugin_locale();
        
        if ( is_admin() ) {
            $this->init_system_status();
            $this->init_plugins_search();
            $this->init_admin_messages();
        }
        
        $this->require_functions();
        $this->require_classes();
        $this->framework_loader()->add_action(
            'wp_enqueue_scripts',
            $this,
            'register_common_assets',
            1
        );
        $this->init_classes();
        $this->init_modules();
        $this->init_customizer();
        $this->init_wc_ajax();
        $this->init_plugin_settings();
        
        if ( is_admin() ) {
            $this->init_framework_tabs();
            $this->init_plugin_tabs();
            $this->init_plugin_migrations();
        }
    
    }
    
    /**
     * Initialize the framework loader
     *
     * @since    1.0.0
     * @access   private
     */
    private function init_framework_loader()
    {
        if ( empty($this->framework_loader) ) {
            $this->framework_loader = new XT_Framework_Loader();
        }
    }
    
    /**
     * Initialize the plugin loader
     *
     * @since    1.0.0
     * @access   private
     */
    private function init_plugin_loader()
    {
        if ( empty($this->plugin_loader) ) {
            $this->plugin_loader = new XT_Framework_Loader();
        }
    }
    
    /**
     * Enqueue framework assets
     *
     * @since    1.0.0
     * @access   private
     */
    public function enqueue_framework_assets()
    {
        // Enqueue Framework Assets
        wp_enqueue_style(
            $this->framework_prefix(),
            xtfw_dir_url( XTFW_DIR_ASSETS ) . '/css/admin.css',
            array(),
            XTFW_VERSION,
            'all'
        );
    }
    
    /**
     * Register common assets for on demand plugin use
     *
     * @since    1.0.0
     * @access   private
     */
    public function register_common_assets()
    {
        // Register common assets for on demand plugin use
        wp_register_style(
            'xt-icons',
            xtfw_dir_url( XTFW_DIR_CUSTOMIZER ) . '/controls/xt_icons/css/xt-icons.css',
            array(),
            XTFW_VERSION
        );
        wp_register_script(
            'xt-jquery-ajaxqueue',
            xtfw_dir_url( XTFW_DIR_ASSETS ) . '/js/jquery.ajaxqueue' . XTFW_SCRIPT_SUFFIX . '.js',
            array( 'jquery' ),
            XTFW_VERSION,
            true
        );
        wp_register_script(
            'xt-jquery-touch',
            xtfw_dir_url( XTFW_DIR_ASSETS ) . '/js/jquery.touch' . XTFW_SCRIPT_SUFFIX . '.js',
            array( 'jquery' ),
            XTFW_VERSION,
            true
        );
        wp_register_script(
            'xt-observers-polyfill',
            'https://polyfill.io/v3/polyfill.min.js?features=ResizeObserver%2CMutationObserver',
            array(),
            XTFW_VERSION,
            true
        );
        if ( !wp_script_is( 'xt-jquery-touch' ) ) {
            wp_add_inline_script( 'xt-jquery-touch', '
                window.XT = (typeof window.XT !== "undefined") ? window.XT : {};
    
                XT.isTouchDevice = function () {
                    return ("ontouchstart" in document.documentElement);
                };
    
                if (XT.isTouchDevice()) {
                    jQuery("html").addClass("xtfw-touchevents");
                }else{        
                    jQuery("html").addClass("xtfw-no-touchevents");
                }
            ' );
        }
    }
    
    /**
     * Initialize Admin Notices
     *
     * @since    1.0.0
     * @access   public
     */
    public function init_framework_notices()
    {
        if ( empty($this->framework_notices) ) {
            $this->framework_notices = new XT_Framework_Notices( $this->framework_slug(), $this->framework_menu_name() );
        }
    }
    
    /**
     * Initialize Admin Notices
     *
     * @since    1.0.0
     * @access   public
     */
    public function init_plugin_notices()
    {
        if ( empty($this->plugin_notices) ) {
            $this->plugin_notices = new XT_Framework_Notices( $this->plugin_slug(), $this->plugin_menu_name() );
        }
    }
    
    /**
     * Initialize Cache
     *
     * @since    1.0.0
     */
    public function init_cache()
    {
        if ( empty($this->cache) ) {
            $this->cache = new XT_Framework_Cache( $this->plugin_short_prefix() );
        }
    }
    
    /**
     * Initialize Transient
     *
     * @since    1.0.0
     */
    public function init_transient()
    {
        if ( empty($this->transient) ) {
            $this->transient = new XT_Framework_Transient( $this->plugin_short_prefix() );
        }
    }
    
    /**
     * Initialize Frontend Notices
     *
     * @since    1.0.0
     * @access   public
     */
    public function init_plugin_frontend_notices()
    {
        
        if ( empty($this->plugin_frontend_notices) ) {
            $is_plugin_for_woocommerce = $this->plugin_dependencies()->depends_on( 'WooCommerce' );
            $this->plugin_frontend_notices = new XT_Framework_Notices(
                $this->plugin_slug(),
                null,
                true,
                $is_plugin_for_woocommerce
            );
        }
    
    }
    
    /**
     * Check if conflict plugins are active, if yes, disabled them and show error notice
     *
     * @since    1.0.0
     */
    public function init_conflicts_check()
    {
        new XT_Framework_Conflicts_Check( $this );
    }
    
    /**
     * Check if required plugin dependencies are loaded, if not, show error notice
     *
     * @since    1.0.0
     */
    public function init_dependencies_check()
    {
        if ( empty($this->dependencies_check) ) {
            $this->dependencies_check = new XT_Framework_Dependencies_Check( $this );
        }
    }
    
    /**
     * Initialize the Access Manager
     *
     * @since    1.0.0
     * @access   protected
     */
    protected function init_access_manager()
    {
        if ( !empty($this->access_manager) ) {
            return;
        }
        
        if ( $this->market_is( 'freemius' ) ) {
            $this->access_manager = $this->freemius_access_manager();
            $this->access_manager->add_filter( 'plugin_icon', array( $this, 'freemius_plugin_thumb' ) );
            $this->access_manager->add_filter( 'hide_account_tabs', '__return_true' );
            $this->access_manager->add_filter(
                'is_submenu_visible',
                array( $this, 'is_submenu_visible' ),
                10,
                2
            );
            $this->access_manager->override_i18n( array(
                'contact-us' => esc_html__( "Support", 'xt-framework' ),
            ) );
            $this->plugin_loader()->add_action( 'admin_footer', $this, 'freemius_sdk_assets' );
            // Signal that Freemius SDK was initiated.
            do_action( $this->plugin_short_prefix( 'fs_loaded' ) );
        } else {
            $this->access_manager = $this->local_access_manager();
        }
    
    }
    
    public function is_submenu_visible( $is_visible, $menu_id )
    {
        $is_admin_tab = !empty($_GET['page']) && strpos( $_GET['page'], $this->plugin_slug() ) !== false;
        if ( !$is_admin_tab ) {
            $is_visible = false;
        }
        if ( $menu_id === 'affiliation' ) {
            $is_visible = false;
        }
        return $is_visible;
    }
    
    /**
     * Load Freemius License Manager
     *
     * @return Freemius
     * @since    1.0.0
     */
    protected abstract function freemius_access_manager();
    
    /**
     * Load Local Access Manager
     *
     * @return XT_Framework_Access_Manager
     * @since    1.0.0
     */
    private function local_access_manager()
    {
        require_once $this->plugin_framework_path( 'includes/license/includes', 'class-license.php' );
        require_once $this->plugin_framework_path( 'includes/license/includes', 'class-access-manager.php' );
        require_once $this->plugin_framework_path( 'includes/license/includes', 'plugin-update-checker/plugin-update-checker.php' );
        require_once $this->plugin_framework_path( 'includes/license/includes', 'class-plugin-updater.php' );
        // Init License Manager
        return new XT_Framework_Access_Manager( $this );
    }
    
    /**
     * Plugin main thumb
     *
     * @return string Plugin thumb
     */
    public function freemius_plugin_thumb()
    {
        return dirname( $this->plugin->file ) . '/admin/assets/images/icon.png';
    }
    
    /**
     * Enqueue Freemius SDK Custom assets
     */
    public function freemius_sdk_assets()
    {
        wp_enqueue_style(
            'xt-freemius-access_manager',
            'https://s3.amazonaws.com/xt-freemius/sdk.css',
            array(),
            $this->plugin->version,
            'all'
        );
        wp_enqueue_script(
            'xt-freemius-access_manager',
            'https://s3.amazonaws.com/xt-freemius/sdk.min.js',
            array(),
            $this->plugin->version,
            true
        );
    }
    
    /**
     * Plugin base hooks to handle activation, deactivation and uninstall
     */
    public function init_base_hooks()
    {
        if ( empty($this->plugin_base_hooks) ) {
            $this->plugin_base_hooks = new XT_Framework_Base_Hooks( $this );
        }
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the XT_Framework_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function init_plugin_locale()
    {
        if ( empty($this->plugin_locale) ) {
            $this->plugin_locale = new XT_Framework_i18n( $this->plugin_data( 'TextDomain' ), $this->plugin_file() );
        }
    }
    
    /**
     * Initialize plugin customizer
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_customizer()
    {
        if ( empty($this->customizer) && has_filter( $this->plugin_prefix( 'customizer_fields' ) ) ) {
            $this->customizer = new XT_Framework_Customizer( $this );
        }
    }
    
    /**
     * Initialize plugin settings
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_plugin_settings()
    {
        if ( empty($this->plugin_settings) && has_filter( $this->plugin_prefix( 'plugin_setting_tabs' ) ) ) {
            $this->plugin_settings = new XT_Framework_Plugin_settings( $this );
        }
    }
    
    /**
     * Initialize framework tabs
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_framework_tabs()
    {
        if ( $this->total_instances_within_framework() === 0 ) {
            return;
        }
        if ( empty($this->framework_tabs) ) {
            $this->framework_tabs = XT_Framework_Framework_Tabs::instance( $this );
        }
    }
    
    /**
     * Initialize plugin admin tabs
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_plugin_tabs()
    {
        $this->plugin_tabs = new XT_Framework_Plugin_Tabs( $this );
    }
    
    /**
     * Initialize system status tabs
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_system_status()
    {
        if ( empty($this->system_status) ) {
            $this->system_status = XT_Framework_System_Status::instance( $this );
        }
    }
    
    /**
     * Initialize migrations
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_plugin_migrations()
    {
        if ( empty($this->plugin_migrations) ) {
            $this->plugin_migrations = new XT_Framework_Migration( $this );
        }
    }
    
    /**
     * Initialize Plugins Search
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_plugins_search()
    {
        XT_Framework_Plugins_Search::instance();
    }
    
    /**
     * Initialize Admin Messages
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_admin_messages()
    {
        if ( empty($this->admin_messages) ) {
            $this->admin_messages = new XT_Framework_Admin_Messages( $this );
        }
    }
    
    /**
     * Initialize plugin WC Ajax events
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_wc_ajax()
    {
        if ( empty($this->wc_ajax) && (has_filter( $this->plugin_prefix( 'wc_ajax_add_events' ) ) || has_filter( $this->plugin_prefix( 'wc_ajax_remove_events' ) )) && function_exists( 'wc' ) ) {
            $this->wc_ajax = new XT_Framework_WC_Ajax( $this );
        }
    }
    
    /**
     * Load the required functions for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    protected function require_functions()
    {
        /**
         * Require admin functions
         */
        foreach ( glob( $this->plugin_path( 'admin', 'functions-*.php' ) ) as $filename ) {
            require_once $filename;
        }
        /**
         * Require public functions
         */
        foreach ( glob( $this->plugin_path( 'public', 'functions-*.php' ) ) as $filename ) {
            require_once $filename;
        }
    }
    
    /**
     * Load the required classes for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    protected function require_classes()
    {
        /**
         * Require common classes responsible for defining all common actions
         * side of the site.
         */
        foreach ( glob( $this->plugin_path( '/', 'class-*.php' ) ) as $filename ) {
            if ( basename( $filename ) === 'class-core.php' ) {
                continue;
            }
            require_once $filename;
        }
        /**
         * Require admin classes responsible for defining all admin actions.
         */
        foreach ( glob( $this->plugin_path( 'admin', 'class-*.php' ) ) as $filename ) {
            require_once $filename;
        }
        /**
         * Require public classes responsible for defining all public actions
         * side of the site.
         */
        foreach ( glob( $this->plugin_path( 'public', 'class-*.php' ) ) as $filename ) {
            require_once $filename;
        }
    }
    
    /**
     * Initialize plugin modules
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_modules()
    {
        if ( empty($this->modules) && has_filter( $this->plugin_prefix( 'modules' ) ) ) {
            $this->modules = new XT_Framework_Modules( $this );
        }
    }
    
    /**
     * Init main plugin classes
     *
     * @since    1.0.0
     * @access   protected
     */
    protected function init_classes()
    {
        $className = get_class( $this ) . '_Admin';
        if ( class_exists( $className ) ) {
            $this->backend = new $className( $this );
        }
        $className = get_class( $this ) . '_Public';
        if ( class_exists( $className ) ) {
            $this->frontend = new $className( $this );
        }
    }
    
    // --------- GETTERS ------------- //
    /**
     * The name of the framework
     *
     * @return    string    The name of the framework.
     * @since     1.0.0
     */
    public function framework_name()
    {
        return $this->framework_name;
    }
    
    /**
     * The menu name of the framework
     *
     * @return    string    The menu name of the framework.
     * @since     1.0.0
     */
    public function framework_menu_name()
    {
        return $this->framework_menu_name;
    }
    
    /**
     * The slug of the framework used to uniquely identify it
     *
     * @return    string    The slug of the framework.
     * @since     1.0.0
     */
    public function framework_slug( $suffix = null )
    {
        return $this->framework_slug . (( !empty($suffix) ? '-' . $suffix : '' ));
    }
    
    /**
     * Generate framework hooks or ids based on framework prefix
     *
     * @return    string    hook id.
     * @since     1.0.0
     */
    public function framework_prefix( $suffix = null )
    {
        return $this->framework_prefix . (( !empty($suffix) ? '_' . $suffix : '' ));
    }
    
    /**
     * Generate framework hooks or ids based on framework short prefix
     *
     * @return    string    hook id.
     * @since     1.0.0
     */
    public function framework_short_prefix( $suffix = null )
    {
        return $this->framework_short_prefix . (( !empty($suffix) ? '_' . $suffix : '' ));
    }
    
    /**
     * Get framework icon
     *
     * @since    1.0.0
     * @access   private
     */
    public function framework_icon()
    {
        return xtfw_dir_url( XTFW_DIR_ADMIN_TABS_ASSETS ) . '/images/icon.svg';
    }
    
    /**
     * Get framework logo
     *
     * @since    1.0.0
     * @access   private
     */
    public function framework_logo()
    {
        return xtfw_dir_url( XTFW_DIR_ADMIN_TABS_ASSETS ) . '/images/logo.svg';
    }
    
    /**
     * The framework admin URL
     *
     * @param string $slug
     * @param array $params
     *
     * @return    string    The framework admin url.
     * @since     1.0.0
     */
    public function framework_admin_url( $slug = '', $params = array() )
    {
        $url = admin_url( 'admin.php?page=' . $this->framework_slug( $slug ) );
        if ( !empty($params) ) {
            $url = add_query_arg( $params, $url );
        }
        return $url;
    }
    
    /**
     * Check is current page is framework admin URL
     *
     * @param string $slug
     *
     * @return    bool
     * @since     1.0.0
     */
    public function framework_is_admin_url( $slug = '' )
    {
        $page = ( !empty($_GET['page']) ? $_GET['page'] : '' );
        return $page === $this->framework_slug( $slug );
    }
    
    /**
     * The plugin info
     *
     * @return    XT_Framework_Plugin    The plugin info.
     * @since     1.0.0
     */
    public function plugin()
    {
        return $this->plugin;
    }
    
    /**
     * The name of the plugin
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function plugin_name()
    {
        return $this->plugin()->name;
    }
    
    /**
     * The menu name of the plugin
     *
     * @return    string    The menu name of the plugin.
     * @since     1.0.0
     */
    public function plugin_menu_name()
    {
        return $this->plugin()->menu_name;
    }
    
    /**
     * The slug of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @param string $suffix
     *
     * @return    string    The slug of the plugin.
     * @since     1.0.0
     */
    public function plugin_slug( $suffix = null )
    {
        return $this->plugin()->slug . (( !empty($suffix) ? '-' . $suffix : '' ));
    }
    
    /**
     * Generate plugin id based on plugin short prefix
     *
     * @return    string    id.
     * @since     1.0.0
     */
    public function plugin_id()
    {
        return $this->plugin()->short_prefix;
    }
    
    /**
     * Generate plugin prefix for hooks or ids based on plugin prefix
     *
     * @param string $suffix
     *
     * @return    string    prefix.
     * @since     1.0.0
     */
    public function plugin_prefix( $suffix = null )
    {
        return $this->plugin()->prefix . (( !empty($suffix) ? '_' . $suffix : '' ));
    }
    
    /**
     * Generate plugin prefix for hooks or ids based on plugin short prefix
     *
     * @param string $suffix
     *
     * @return    string    prefix.
     * @since     1.0.0
     */
    public function plugin_short_prefix( $suffix = null )
    {
        return $this->plugin()->short_prefix . (( !empty($suffix) ? '_' . $suffix : '' ));
    }
    
    /**
     * The dashicon of the plugin
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function plugin_icon()
    {
        return $this->plugin()->icon;
    }
    
    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function plugin_version()
    {
        return $this->plugin()->version;
    }
    
    /**
     * Retrieve the version number of the framework.
     *
     * @return    string    The version number of the framework.
     * @since     1.0.0
     */
    public function framework_version()
    {
        return XTFW_VERSION;
    }
    
    /**
     * The plugin file
     *
     * @return    string    The plugin file.
     * @since     1.0.0
     */
    public function plugin_file()
    {
        return $this->plugin()->file;
    }
    
    /**
     * The plugin directory
     *
     * @return    string    The plugin directory.
     * @since     1.0.0
     */
    public function plugin_dir()
    {
        return basename( dirname( $this->plugin_file() ) );
    }
    
    /**
     * The plugin path
     *
     * @param string $dir
     * @param string $file
     *
     * @return    string    The plugin path.
     * @since     1.0.0
     */
    public function plugin_path( $dir = null, $file = null )
    {
        $path = plugin_dir_path( $this->plugin_file() );
        if ( !empty($dir) ) {
            $path .= $dir . "/";
        }
        if ( !empty($file) ) {
            $path .= $file;
        }
        return $path;
    }
    
    /**
     * The plugin URL
     *
     * @param string $dir
     * @param string $file
     *
     * @return    string    The plugin url.
     * @since     1.0.0
     */
    public function plugin_url( $dir = null, $file = null )
    {
        $url = plugin_dir_url( $this->plugin_file() );
        if ( !empty($dir) ) {
            $url .= $dir . "/";
        }
        if ( !empty($file) ) {
            $url .= $file;
        }
        return $url;
    }
    
    /**
     * The plugin admin URL
     *
     * @return    string    The plugin admin url.
     * @since     1.0.0
     */
    public function plugin_admin_url( $slug = '', $params = array() )
    {
        $url = admin_url( 'admin.php?page=' . $this->plugin_slug( $slug ) );
        if ( !empty($params) ) {
            $url = add_query_arg( $params, $url );
        }
        return $url;
    }
    
    /**
     * Check is current page is plugin admin URL
     *
     * @return    bool
     * @since     1.0.0
     */
    public function plugin_is_admin_url( $slug = '' )
    {
        $page = ( !empty($_GET['page']) ? $_GET['page'] : '' );
        return $page === $this->plugin_slug( $slug );
    }
    
    /**
     * The plugin framework file
     *
     * @param bool $active_fw
     *
     * @return    string    The plugin framework file.
     * @since     1.0.0
     */
    public function plugin_framework_file( $active_fw = false )
    {
        if ( $active_fw ) {
            return XTFW_FILE;
        }
        return $this->plugin_path( 'xt-framework/start.php' );
    }
    
    /**
     * The plugin framework directory
     *
     * @param bool $active_fw
     *
     * @return    string    The plugin framework directory.
     * @since     1.0.0
     */
    public function plugin_framework_dir( $active_fw = false )
    {
        return dirname( $this->plugin_framework_file( $active_fw ) );
    }
    
    /**
     * The plugin framework path
     *
     * @param string $dir
     * @param string $file
     * @param bool $active_fw
     *
     * @return    string    The plugin framework path.
     * @since     1.0.0
     */
    public function plugin_framework_path( $dir = null, $file = null, $active_fw = false )
    {
        $path = plugin_dir_path( $this->plugin_framework_file( $active_fw ) );
        if ( !empty($dir) ) {
            $path .= $dir . "/";
        }
        if ( !is_dir( $path ) ) {
            foreach ( self::instances() as $instance ) {
                if ( is_dir( $instance->plugin_framework_path( $dir ) ) ) {
                    return $instance->plugin_framework_path( $dir, $file );
                }
            }
        }
        if ( !empty($file) ) {
            $path .= $file;
        }
        return $path;
    }
    
    /**
     * The plugin framework URL
     *
     * @param string $dir
     * @param string $file
     * @param bool $active_fw
     *
     * @return    string    The plugin framework url.
     * @since     1.0.0
     */
    public function plugin_framework_url( $dir = null, $file = null, $active_fw = false )
    {
        $url = plugin_dir_url( $this->plugin_framework_file( $active_fw ) );
        if ( !empty($dir) ) {
            $url .= $dir . "/";
        }
        if ( !empty($file) ) {
            $url .= $file;
        }
        return $url;
    }
    
    /**
     * Retrieve the plugin marketplace
     *
     * @return    string    The plugin marketplace.
     * @since     1.0.0
     */
    public function market()
    {
        return $this->plugin()->market;
    }
    
    /**
     * Check if current market
     *
     * @return    bool
     * @since     1.0.0
     */
    public function market_is( $market )
    {
        return $this->market() === $market;
    }
    
    /**
     * Check if is a freemius plugin
     *
     * @return    bool
     * @since     1.0.0
     */
    public function is_freemius()
    {
        return $this->market_is( 'freemius' );
    }
    
    /**
     * Retrieve the plugin marketplace product
     *
     * @return    object    The plugin marketplace.
     * @since     1.0.0
     */
    public function market_product()
    {
        return $this->plugin()->market_product;
    }
    
    /**
     * Check if current market
     *
     * @return    bool
     * @since     1.0.0
     */
    public function market_product_is( $market, $product_id )
    {
        return $this->market_is( $market ) && $this->market_product()->id === $product_id;
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the framework.
     *
     * @return    XT_Framework_Loader    Orchestrates the hooks of the framework.
     * @since     1.0.0
     */
    public function framework_loader()
    {
        if ( empty($this->framework_loader) ) {
            $this->init_framework_loader();
        }
        return $this->framework_loader;
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    XT_Framework_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function plugin_loader()
    {
        if ( empty($this->plugin_loader) ) {
            $this->init_plugin_loader();
        }
        return $this->plugin_loader;
    }
    
    /**
     * The reference to the class that manages the modules
     *
     * @return    XT_Framework_Modules
     * @since     1.0.0
     */
    public function modules()
    {
        if ( empty($this->modules) ) {
            $this->init_modules();
        }
        return $this->modules;
    }
    
    /**
     * Check is has modules
     *
     * @return    bool
     * @since     1.0.0
     */
    public function has_modules()
    {
        return !empty($this->modules()) && $this->modules()->count() > 0;
    }
    
    /**
     * The reference to the class that manages the cache
     *
     * @return    XT_Framework_Cache
     * @since     1.0.0
     */
    public function cache()
    {
        if ( empty($this->cache) ) {
            $this->init_cache();
        }
        return $this->cache;
    }
    
    /**
     * The reference to the class that manages the cache
     *
     * @return    XT_Framework_Transient
     * @since     1.0.0
     */
    public function transient()
    {
        if ( empty($this->transient) ) {
            $this->init_transient();
        }
        return $this->transient;
    }
    
    /**
     * The reference to the class that manages the frontend side of the plugin.
     *
     * @return    Object    Frontend side of the plugin.
     * @since     1.0.0
     */
    public function frontend()
    {
        if ( empty($this->frontend) ) {
            $this->init_frontend();
        }
        return $this->frontend;
    }
    
    /**
     * The reference to the class that manages the backend side of the plugin.
     *
     * @return    Object    Backend side of the plugin.
     * @since     1.0.0
     */
    public function backend()
    {
        if ( empty($this->backend) ) {
            $this->init_backend();
        }
        return $this->backend;
    }
    
    /**
     * Get Base Hooks Instance
     *
     * @return    XT_Framework_Base_Hooks
     * @since     1.0.0
     */
    public function plugin_base_hooks()
    {
        if ( empty($this->plugin_base_hooks) ) {
            $this->init_base_hooks();
        }
        return $this->plugin_base_hooks;
    }
    
    /**
     * Get Dependencies Check Instance
     *
     * @return    XT_Framework_Dependencies_Check
     * @since     1.0.0
     */
    public function plugin_dependencies()
    {
        if ( empty($this->dependencies_check) ) {
            $this->init_dependencies_check();
        }
        return $this->dependencies_check;
    }
    
    /**
     * Get License Manager Instance
     *
     * @return    XT_Framework_Access_Manager|Freemius
     * @since     1.0.0
     */
    public function access_manager()
    {
        if ( empty($this->access_manager) ) {
            $this->init_access_manager();
        }
        return $this->access_manager;
    }
    
    /**
     * Get Framework Notices Instance
     *
     * @return    XT_Framework_Notices
     * @since     1.0.0
     */
    public function framework_notices()
    {
        if ( empty($this->framework_notices) ) {
            $this->init_framework_notices();
        }
        return $this->framework_notices;
    }
    
    /**
     * Get Plugin Notices Instance
     *
     * @return    XT_Framework_Notices
     * @since     1.0.0
     */
    public function plugin_notices()
    {
        if ( empty($this->plugin_notices) ) {
            $this->init_plugin_notices();
        }
        return $this->plugin_notices;
    }
    
    /**
     * Get Plugin Frontend Notices Instance
     *
     * @return    XT_Framework_Notices
     * @since     1.0.0
     */
    public function plugin_frontend_notices()
    {
        if ( empty($this->plugin_frontend_notices) ) {
            $this->init_plugin_frontend_notices();
        }
        return $this->plugin_frontend_notices;
    }
    
    /**
     * Get Admin Messages Instance
     *
     * @return    XT_Framework_Admin_Messages
     * @since     1.0.0
     */
    public function admin_messages()
    {
        if ( empty($this->admin_messages) ) {
            $this->init_admin_messages();
        }
        return $this->admin_messages;
    }
    
    /**
     * Get System Status Instance
     *
     * @return    XT_Framework_System_Status
     * @since     1.0.0
     */
    public function system_status()
    {
        if ( empty($this->system_status) ) {
            $this->init_system_status();
        }
        return $this->system_status;
    }
    
    /**
     * Get Plugin Migrations Instance
     *
     * @return    XT_Framework_Migration
     * @since     1.0.0
     */
    public function plugin_migrations()
    {
        if ( empty($this->plugin_migrations) ) {
            $this->init_plugin_migrations();
        }
        return $this->plugin_migrations;
    }
    
    /**
     * Get Plugin Locale Instance
     *
     * @return    XT_Framework_i18n
     * @since     1.0.0
     */
    public function plugin_locale()
    {
        if ( empty($this->plugin_locale) ) {
            $this->init_plugin_locale();
        }
        return $this->plugin_locale;
    }
    
    /**
     * Get Plugin Customizer Instance
     *
     * @return    XT_Framework_Customizer
     * @since     1.0.0
     */
    public function customizer()
    {
        if ( empty($this->customizer) ) {
            $this->init_customizer();
        }
        return $this->customizer;
    }
    
    /**
     * Get Plugin Settings Instance
     *
     * @return    XT_Framework_Plugin_Settings
     * @since     1.0.0
     */
    public function plugin_settings()
    {
        if ( empty($this->plugin_settings) ) {
            $this->init_plugin_settings();
        }
        return $this->plugin_settings;
    }
    
    /**
     * Get Framework Tabs Instance
     *
     * @return    XT_Framework_Framework_Tabs
     * @since     1.0.0
     */
    public function framework_tabs()
    {
        if ( empty($this->framework_tabs) ) {
            $this->init_framework_tabs();
        }
        return $this->framework_tabs;
    }
    
    /**
     * Get Plugin Tabs Instance
     *
     * @return    XT_Framework_plugin_tabs
     * @since     1.0.0
     */
    public function plugin_tabs()
    {
        if ( empty($this->plugin_tabs) ) {
            $this->init_plugin_tabs();
        }
        return $this->plugin_tabs;
    }
    
    /**
     * Get Plugin WC Ajax
     *
     * @return    XT_Framework_WC_Ajax
     * @since     1.0.0
     */
    public function wc_ajax()
    {
        if ( empty($this->wc_ajax) ) {
            $this->init_wc_ajax();
        }
        return $this->wc_ajax;
    }
    
    /**
     * Get Plugin Header Data
     *
     * @param string $id
     *
     * @return    array
     * @since     1.0.4
     */
    public function plugin_data( $id = null )
    {
        $data = get_plugin_data( $this->plugin_file() );
        if ( !empty($id) ) {
            return ( !empty($data[$id]) ? $data[$id] : null );
        }
        return $data;
    }
    
    /**
     * Get Plugin Activate or Upgrade License Url
     *
     * @return    string
     * @since     1.1.1
     */
    public function plugin_upgrade_url()
    {
        
        if ( $this->market_is( 'freemius' ) ) {
            
            if ( $this->access_manager()->is_premium() && $this->access_manager()->is_registered() ) {
                return $this->access_manager()->get_account_url();
            } else {
                return $this->access_manager()->get_upgrade_url();
            }
        
        } else {
            return $this->access_manager()->get_account_url();
        }
    
    }
    
    /**
     * Check if doing ajax
     *
     * @return    bool
     * @since     1.0.0
     */
    public function doing_ajax()
    {
        return xtfw_doing_ajax();
    }
    
    /**
     * Check if is active theme
     *
     * @param mixed $theme
     *
     * @return    bool
     * @since     1.0.0
     */
    public function is_theme( $theme )
    {
        $active_theme = wp_get_theme( get_template() );
        if ( !empty($active_theme) ) {
            
            if ( is_array( $theme ) ) {
                return in_array( $active_theme->get( 'Name' ), $theme );
            } else {
                return $active_theme->get( 'Name' ) === $theme;
            }
        
        }
        return false;
    }
    
    /**
     * The plugin theme templates path
     *
     * @return    string    The plugin theme templates path.
     * @since     1.0.0
     */
    public function template_path()
    {
        return apply_filters( $this->plugin_prefix( 'template_path' ), $this->plugin_slug() . '/' );
    }
    
    /**
     * Get plugin template. Look within the plugin and active theme
     *
     * @param string $slug
     * @param array $vars
     * @param bool $return
     * @param bool $locateOnly
     *
     * @return    string    The template
     * @since     1.0.0
     */
    public function get_template(
        $slug,
        $vars = array(),
        $return = false,
        $locateOnly = false
    )
    {
        $template = null;
        // Look in your theme
        if ( !XTFW_TEMPLATE_DEBUG_MODE ) {
            $template = $this->get_theme_template( $slug );
        }
        // Get default slug.php
        if ( empty($template) ) {
            $template = $this->get_plugin_template( $slug );
        }
        // Allow 3rd party plugins to filter template file from their plugin.
        $template = apply_filters( $this->plugin_prefix( 'template' ), $template, $slug );
        if ( $locateOnly ) {
            return $template;
        }
        
        if ( $template ) {
            extract( $vars );
            
            if ( !$return ) {
                require $template;
                return '';
            } else {
                ob_start();
                require $template;
                return ob_get_clean();
            }
        
        }
    
    }
    
    /**
     * Get plugin template within active theme
     *
     * @param string $slug
     *
     * @return    string    The template
     * @since     1.0.0
     */
    public function get_theme_template( $slug )
    {
        // Look in your theme
        $template_path = $this->template_path();
        $template = locate_template( array( $template_path . "{$slug}.php" ) );
        // Try legacy slugs
        
        if ( empty($template) ) {
            $template_path = str_replace( "xt-", "", $template_path );
            $template = locate_template( array( $template_path . "{$slug}.php" ) );
        }
        
        return $template;
    }
    
    /**
     * Get plugin template
     *
     * @param string $slug
     *
     * @return    string    The template
     * @since     1.0.0
     */
    public function get_plugin_template( $slug )
    {
        $plugin_path = $this->plugin_path( 'public' );
        $template = null;
        // Get default slug.php
        if ( file_exists( $plugin_path . "templates/{$slug}.php" ) ) {
            $template = $plugin_path . "templates/{$slug}.php";
        }
        return $template;
    }
    
    /**
     * Check whether the plugin is active for the entire network.
     *
     * Only plugins installed in the plugins/ folder can be active.
     *
     * Plugins in the mu-plugins/ folder can't be "activated," so this function will
     * return false for those plugins.
     *
     * @since 3.0.0
     *
     * @return bool True, if active for the network, otherwise false.
     */
    public function is_plugin_active_for_network()
    {
        if ( !is_multisite() ) {
            return false;
        }
        $plugins = get_site_option( 'active_sitewide_plugins' );
        $plugin = basename( $this->plugin_dir() ) . '/' . basename( $this->plugin_file() );
        if ( isset( $plugins[$plugin] ) ) {
            return true;
        }
        return false;
    }
    
    /**
     * Get loaded instances array
     *
     * @return XT_Framework[]
     * @since 1.0.0
     * @static
     */
    public static function instances()
    {
        return self::$_instances;
    }
    
    /**
     * Get total loaded instances
     *
     * @return integer
     * @since 1.0.0
     * @static
     */
    public static function total_instances()
    {
        return count( self::$_instances );
    }
    
    /**
     * Get total loaded instances that are loaded within the framework
     *
     * @return integer
     * @since 1.0.0
     * @static
     */
    public static function total_instances_within_framework()
    {
        $count = 0;
        foreach ( self::$_instances as $instance ) {
            if ( !$instance->plugin()->top_menu() ) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Get total loaded instances that are loaded outside the framework
     *
     * @return integer
     * @since 1.0.0
     * @static
     */
    public static function total_instances_outside_framework()
    {
        $count = 0;
        foreach ( self::$_instances as $instance ) {
            if ( $instance->plugin()->top_menu() ) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Register plugin
     *
     * @param $slug
     * @param $instance XT_Framework
     *
     * @since 1.0.0
     * @static
     */
    public static function register_instance( $slug, &$instance )
    {
        self::$_instances[$slug] = $instance;
    }
    
    /**
     * Check if instance exists
     *
     * @param $slug
     *
     * @return bool
     * @since 1.0.0
     * @static
     */
    public static function instance_exists( $slug )
    {
        return !empty(self::$_instances[$slug]);
    }
    
    /**
     * Get existing instance
     *
     * @param $slug
     *
     * @return XT_Framework
     * @since 1.0.0
     * @static
     */
    public static function get_instance( $slug )
    {
        return ( self::instance_exists( $slug ) ? self::$_instances[$slug] : null );
    }
    
    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'xt-framework' ), $this->framework_version() );
    }
    
    // End __clone()
    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'xt-framework' ), $this->framework_version() );
    }

}