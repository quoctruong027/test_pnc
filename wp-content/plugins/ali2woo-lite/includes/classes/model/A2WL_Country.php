<?php

/**
 * Description of A2WL_Country
 *
 * @author Andrey
 */
if (!class_exists('A2WL_Country')) {

    class A2WL_Country {
        private $countries = array();
        
        public function get_countries() {
            if(empty($this->countries)){
                $this->countries = json_decode(file_get_contents(A2WL()->plugin_path() . '/assets/data/countries.json'), true);
                $this->countries = $this->countries["countries"];
                array_unshift($this->countries, array('c' => '', 'n' => 'N/A'));
            }
            return $this->countries;
        }
        
        public function get_country($code) {
            $countries = $this->get_countries();
            foreach($countries as $c){
                if($c['c'] === $code){
                    return $c;
                    break;
                }
            }
            return false;
        }

    }

}