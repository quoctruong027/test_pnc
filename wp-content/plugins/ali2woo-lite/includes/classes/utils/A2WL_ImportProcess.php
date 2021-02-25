<?php

/**
 * Description of A2WL_ImportProcess
 *
 * @author Andrey
 * 
 */
if (!class_exists('A2WL_ImportProcess')) {


    class A2WL_ImportProcess extends WP_Background_Process {
        
        protected $action = 'a2wl_import_process';

        private static $_instance = null;

        public function __construct() {
            parent::__construct();
        }

        static public function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Task
         *
         * @param mixed $item Queue item to iterate over
         *
         * @return mixed
         */
        protected function task( $item ) {
            a2wl_init_error_handler();
            try {
                $woocommerce_model = new A2WL_Woocommerce();
                $product_import_model = new A2WL_ProductImport();

                $product = $product_import_model->get_product($item['id'], true);
                if ($product) {
                    $ts = microtime(true);
                    
                    $result = $woocommerce_model->add_product($product, $item);

                    a2wl_info_log("IMPORT[time: ".(microtime(true)-$ts).", id:".$item['product_id'].", extId: ".$item['product_id'].", step: ".$item['step']."]");

                    if ($result['state'] === 'error') {
                        throw new Exception($result['message']);
                    }
                }
            } catch (Throwable $e) {
                a2wl_print_throwable($e);
            } catch (Exception $e) {
                a2wl_print_throwable($e);
            }

            return false;
        }

    }
}
