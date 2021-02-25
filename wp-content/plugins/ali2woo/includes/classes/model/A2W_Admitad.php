<?php

/**
 * Description of A2W_Admitad
 *
 * @author Andrey
 */
if (!class_exists('A2W_Admitad')) {

    class A2W_Admitad {
        private static $_instance = null;

        static public function getInstance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function getDeeplink($hrefs) {
            $result = array();
            if ($hrefs) {
                $admitad_account = A2W_Account::getInstance()->get_admitad_account();
                if (!empty($admitad_account['cashback_url'])) {
                    $hrefs = is_array($hrefs) ? array_values($hrefs) : array(strval($hrefs));
                    foreach($hrefs as $href){
                        $href2 = $this->getNormalizedLink($href);

                        if(parse_url($admitad_account['cashback_url'], PHP_URL_QUERY)){
                            $cashback_url = $admitad_account['cashback_url'].'&ulp='.urlencode($href2);
                        }else {
                            $cashback_url = $admitad_account['cashback_url'].'?ulp='.urlencode($href2);
                        }
                        
                        $result[] = array('url'=>$href, 'promotionUrl'=>$cashback_url);
                    }
                }
            }
            return $result;
        }

        private function getNormalizedLink($href){
            preg_match('/([0-9]+)\.html/', $href, $match);
            $ext_id = $match[1];
            $href = str_replace("{$ext_id}/", "", $href);

            return $href;
        }

    }

}
