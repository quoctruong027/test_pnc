<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Transient' ) ) {

	class XT_Framework_Transient {

        /**
         * Transient prefix
         *
         * @since    1.0.0
         * @access   protected
         * @var      string $prefix
         */
        protected $cache_group;

        public function __construct( $prefix = '' ) {

            $this->prefix = $prefix.'_';
		}

        public function set($key, $val, $expiration = 0) {

            set_transient( $this->prefix.$key, $val, $expiration );
        }

        public function get($key) {

            return get_transient( $this->prefix.$key );
        }

        public function delete($key) {

            return delete_transient( $key, $this->cache_group );
        }

        public function result($key, callable $callback, $expiration = 0) {

            $cached = $this->get($key);

            if($cached === false) {

                $cached = $callback();

                $this->set($key, $cached, $expiration);
            }

            return $cached;
        }
	}
}