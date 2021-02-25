<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'XT_Framework_Module' ) ) {

    /**
     * Class that takes care of rendering common message blocks
     *
     * @since      1.0.0
     * @package    XT_Framework
     * @subpackage XT_Framework/includes
     * @author     XplodedThemes
     */
    abstract class XT_Framework_Module {

        // Hold the class instance.
        private static $instances = array();

        // Hold all the plugins that are using this shared plugin
        private static $shared_by = array();

        /**
         * Core class reference.
         *
         * @since    1.0.0
         * @access   public
         * @var      XT_Framework    core    Core Class
         */
        public $core;

        /**
         * @var string  name    Module Name
         */
        protected $name;

        /**
         * @var string  name    Module File
         */
        protected $file;

        /**
         * @var XT_Framework_Loader  loader    Loader Class
         */
        protected $loader;

        /**
         * @var XT_Framework_Customizer  customizer    Customizer Class
         */
        protected $customizer;

        public function __construct( &$core, $name, $file ) {

            $this->core = $core;
            $this->name = $name;
            $this->file = $file;

            $this->init_loader();
            $this->add_hooks();

            $this->init_customizer();
        }

        /**
         * Initialize the module loader
         *
         * @since    1.0.0
         * @access   private
         */
        private function init_loader()
        {
            if (empty($this->loader)) {
                $this->loader = new XT_Framework_Loader();
            }
        }

        /**
         * Initialize the module customizer
         *
         * @since    1.0.0
         * @access   private
         */
        private function init_customizer() {

            if (empty($this->customizer) && has_filter($this->prefix('customizer_fields'))) {
                $this->customizer = new XT_Framework_Customizer($this->core, $this);
            }
        }


        /**
         * Get module id
         *
         * @return    string    id.
         * @since     1.0.0
         */
        public function id() {

            return $this->core->framework_prefix(sanitize_title($this->name));
        }

        /**
         * Get module name
         *
         * @return    string    name.
         * @since     1.0.0
         */
        public function name() {

            return 'XT '.$this->name;
        }

        /**
         * Get module menu name
         *
         * @return    string    name.
         * @since     1.0.0
         */
        public function menu_name() {

            return $this->name;
        }

        /**
         * The module file
         *
         * @return    string    The module file.
         * @since     1.0.0
         */
        public function file()
        {
            return $this->file;
        }

        /**
         * The module directory
         *
         * @return    string    The module directory.
         * @since     1.0.0
         */
        public function dir()
        {
            return basename(dirname($this->file()));
        }

        /**
         * The module path
         *
         * @return    string    The module path.
         * @since     1.0.0
         */
        public function path($dir = null, $file = null)
        {
            $path = plugin_dir_path($this->file());

            if (!empty($dir)) {
                $path .= $dir . "/";
            }

            if (!empty($file)) {
                $path .= $file;
            }

            return $path;
        }

        /**
         * The module URL
         *
         * @return    string    The module url.
         * @since     1.0.0
         */
        public function url($dir = null, $file = null)
        {
            $url = plugin_dir_url($this->file());

            if (!empty($dir)) {
                $url .= $dir . "/";
            }

            if (!empty($file)) {
                $url .= $file;
            }

            return $url;
        }

        /**
         * The admin URL
         *
         * @return    string    The module admin url.
         * @since     1.0.0
         */
        public function admin_url() {

            $sections = $this->customizer()->sections();
            $section = array_shift($sections);
            $section_id = !empty($section['id']) ? $section['id'] : null;

            return $this->customizer()->customizer_link(null, $section_id);
        }

        /**
         * Generate module prefix for hooks or ids based on module id
         *
         * @return    string    hook id.
         * @since     1.0.0
         */
        public function prefix($suffix = null)
        {
            return $this->id() . (!empty($suffix) ? '_' . $suffix : '');
        }

        public function loader() {

	        if (empty($this->loader)) {
		        $this->init_loader();
	        }

            return $this->loader;
        }

        public function customizer() {

	        if (empty($this->customizer)) {
		        $this->init_customizer();
	        }

            return $this->customizer;
        }

        final public static function get_instance($core, $name, $file)
        {

            self::$shared_by[$core->plugin_slug()] = $core->plugin_name();

            $calledClass = get_called_class();

            if (!isset(self::$instances[$calledClass]))
            {
                self::$instances[$calledClass] = new $calledClass($core, $name, $file);
            }

            return self::$instances[$calledClass];
        }

        final public static function shared_by() {

            return self::$shared_by;
        }

        final private function __clone(){}
    }
}
