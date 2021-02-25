<?php

/**
 * Description of A2WL_EpnAccount
 *
 * @author Andrey
 */
if (!class_exists('A2WL_EpnAccount')) {

    class A2WL_EpnAccount extends A2WL_AbstractAccount {
        static public function getInstance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function getDeeplink($hrefs) {
            $result = array();
            if ($hrefs) {
                $epn_account = A2WL_Account::getInstance()->get_epn_account();
                if (!empty($epn_account['cashback_url'])) {
                    $hrefs = is_array($hrefs) ? array_values($hrefs) : array(strval($hrefs));
                    foreach($hrefs as $href){
                        $nHref = $this->getNormalizedLink($href);

                        if(parse_url($epn_account['cashback_url'], PHP_URL_QUERY)){
                            $cashback_url = $epn_account['cashback_url'].'&to='.urlencode($nHref);
                        }else {
                            $cashback_url = $epn_account['cashback_url'].'?to='.urlencode($nHref);
                        }
                        
                        $result[] = array('url'=>$href, 'promotionUrl'=>$cashback_url);
                    }
                }
            }
            return $result;
        }
    }

}
