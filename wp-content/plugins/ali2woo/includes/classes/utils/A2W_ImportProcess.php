<?php

/**
 * Description of A2W_ImportProcess
 *
 * @author Andrey
 * 
 */
if (!class_exists('A2W_ImportProcess')) {


    class A2W_ImportProcess extends WP_Background_Process {
        
        protected $action = 'a2w_import_process';

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
            a2w_init_error_handler();
            try {
                $woocommerce_model = new A2W_Woocommerce();
                $product_import_model = new A2W_ProductImport();

                $product = $product_import_model->get_product($item['id'], true);
                if ($product) {
                    $result = $woocommerce_model->add_product($product, $item);
                    if ($result['state'] === 'error') {
                        error_log($result['message']);
                    }
                }
            } catch (Exception $e) {
                error_log($e->getTraceAsString());
            }

            return false;
        }

    }
}
