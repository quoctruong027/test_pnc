<?php
require_once dirname(__FILE__) . '/../data/rules.php';
require_once dirname(__FILE__) . '/Etcpf_Data_Store.php';

class Data_overrides extends Etcpf_Data_Store
{

    private $overrides;
    private $data;
    private $preparedOverrides;
    private $overridingMethods;
    private $tokens;
    private $overridingValues = array();
    private $product;
    private $ruleParams = array();

    function __construct($value)
    {
        $this->__set('overrides', $value);
        $this->__set('overridingMethods', array('setattributedefaults', 'setAttribute', 'command', '$', 'rule'));
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return false;
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
        return $this;
    }

    public function prepareOverrides($params)
    {
        $this->overrides = explode("\n", $this->overrides);
        if ($this->overrides) {
            // run the overrides fo all provided commands
            foreach ($this->overrides as $key => $singleOverrides) {
                $singleOverrides = trim($singleOverrides);

                // converts human readable string to array
                $this->tokens = $this->getTokens($singleOverrides);

                // Searches if the method is allowed or not,
                // specially for in script injection purpose

                if (array_search(strtolower($this->tokens[0]), $this->overridingMethods, true) !== false) {
                    $method = strtolower($this->tokens[0]);
                    $params = $this->$method($params);
                }
            }
        }
        return $params;
    }

    public function handleExtras($params)
    {
        if (is_array($params[$this->tokens[1]])) {
            if ($this->tokens[4] == 'append') {
                if (stripos($this->tokens[3], '|')) {
                    $values = explode('|', $this->tokens[3]);
                    $params[$this->tokens[1]] = array_merge($params[$this->tokens[1]], $values);
                } else {
                    $params[$this->tokens[1]][] = $this->tokens[3];
                }
            } else $params[$this->tokens[1]][0] = $this->tokens[3];
        } else {
            $params[$this->tokens[1]] = $this->tokens[3];
        }
        return $params;
    }

    public function rule($params)
    {
        $this->ruleParams = $this->extractRuleParams();
        $item = new stdClass();
        foreach ($params as $key => $pav) {
            $item->attributes[$key] = $pav;
        }

        $item = parent::getproductAttributes($this, $item);

        $className = 'ETCPF_FeedRule' . ucwords(strtolower($this->tokens[1]));
        if (class_exists($className)) {
            $initializer = new $className();
            $initializer->parameters = $this->ruleParams;
            $initializer->initialize();
            $initializer->clearValue();
            $initializer->process($item);
        }

        foreach($item->attributes as $key=> $attributeVal){
            if(array_key_exists($key,$params))
                $params[$key] = $attributeVal;
        }

        return $params;
    }

    public function extractRuleParams()
    {
        $start = false;
        $ruleParams = array();
        foreach ($this->tokens as $param) {
            if ($param == ')') {
                break;
            }

            if ($start) {
                $ruleParams[] = $param;
            }

            if ($param == '(') {
                $start = true;
            }

        }
        return $ruleParams;
    }

    public function setattributedefaults($params)
    {
        // if empty, simply put th provided values
        if (empty($params[$this->tokens[1]])) {
            $params[$this->tokens[1]] = $this->tokens[3];
        } elseif (isset($this->tokens[3]) && isset($this->tokens[4])) {
            // if the attributes already contains value act according to user sup0plied command
            $params = $this->handleExtras($params);
        }

        return $params;
    }

    public function implementOverrides($params, $product)
    {
        $this->product = $product;
        return $this->prepareOverrides($params);
    }

    public function getTokens($source)
    {
        $items = array();
        $index = 0;
        $used_so_far = 0;
        $this_token = '';
        while ($used_so_far < strlen($source)) {
            switch ($source[$used_so_far]) {
                case ' ':
                case ',':
                    if (strlen($this_token) > 0) {
                        $items[$index] = $this_token;
                        $this_token = '';
                        $index++;
                    }
                    break;
                case '"':
                    $used_so_far++;
                    while (($used_so_far < strlen($source)) && ($source[$used_so_far] != '"')) {
                        $this_token .= $source[$used_so_far];
                        $used_so_far++;
                    }
                    break;
                case '(':
                case ')':
                    if (strlen($this_token) > 0) {
                        $items[$index] = $this_token;
                        $this_token = '';
                        $index++;
                    }
                    $items[$index] = $source[$used_so_far];
                    $this_token = '';
                    $index++;
                    break;
                default:
                    $this_token .= $source[$used_so_far];
            }
            $used_so_far++;
        }
        $items[$index] = $this_token;

        return $items;
    }

}
