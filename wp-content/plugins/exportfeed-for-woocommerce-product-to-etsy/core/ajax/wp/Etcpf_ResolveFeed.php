<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}

Class Etcpf_ResolveFeed
{

    const FPT = 'etcpf_feedproducts'; /*Feed Product Table*/
    const RPDT = 'etcpf_resolved_product_data'; /*Resolved Product Data Table*/
    public $db;

    function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->date = date("Y-m-d H:i:s");
    }

    public function ClearDataFromDb($args = array())
    {
        $table = $this->db->prefix . self::FPT;
        if (array_key_exists('pid', $args) && array_key_exists('error_code', $args)) {
            if (!$args['pid'] || !$args['error_code']) {
                echo json_encode(array('success' => false, 'msg' => "Product Id or Error Code Does not exists."));
                exit;
            }
            $qry = $this->db->prepare("DELETE FROM $table WHERE (p_id=%d OR parent_id=%d) AND error_code=%d ", array($args['pid'], $args['pid'], $args['error_code']));
            /*$this->db->delete($table, ['p_id' => $args['pid'], OR 'parent_id'=>$args['pid'], 'error_code' => $args['error_code']])*/
            if ($this->db->query($qry)) {
                echo json_encode(array('success' => true, 'msg' => "Product Error removed from table"));
                exit();
            }
            echo json_encode(array('success' => false, 'msg' => "Product Error could not be removed from table. Try again later"));
            exit();
        }

    }

    public function AssignProductValueinWoo($args = array())
    {
        update_option('ETCPF_RESOLVED', 'yes');
        $productContainsError = true;
        $allProductResolved = false;
        $table = $this->db->prefix . self::FPT;
        $pid = $args['pid'];
        $value = $args['value'];
        $code = $args['error_code'];
        if (is_array($args) && isset($args['error_code'])) {
            $metakey = $this->getMetaKeyFromErrorCode($args['error_code']);
            if ($metakey) {
                if ($value) {
                    $result = $this->setValueInWoo($metakey, $value, $pid);
                    if ($result) {
                        /*$qry = $this->db->prepare("DELETE FROM $table WHERE (p_id=%d) AND error_code=%d ", array($pid, $args['error_code']));*/
                        $this->db->update($table, array('error_status' => '2'), array('p_id' => $pid, 'error_code' => $args['error_code']));
                        if (empty($this->db->last_error)) {
                            $productContainsError = $this->CheckIfProductStillContainsError($pid, $args['feedID']);
                            if ($productContainsError == false) {
                                $allProductResolved = $this->checkIfAllProductResolved($args['feedID']);
                            }
                            $response = array('status' => true, 'data' => $args, 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
                        } else {
                            $response = array('status' => false, 'data' => $args, 'error_msg' => 'Data updated in woocommerce but could not find in resolution table', 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
                        }
                    } else {
                        $existingvalue = get_post_meta($pid);
                        if ($existingvalue) {
                            $this->db->update($table, array('error_status' => '2'), array('p_id' => $pid, 'error_code' => $args['error_code']));
                            $response = array('status' => false, 'data' => $args, 'error_msg' => "Value in Product level could not be saved. Please try again later. Thanks", 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
                        } else {
                            $response = array('status' => true, 'data' => $args, 'error_msg' => "Value in Product level could not be saved. Please try again later. Thanks", 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
                        }
                    }
                } else {
                    $response = array('status' => false, 'data' => $args, 'error_msg' => "Value for error code $code was not supplied", 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
                }

            } else {
                $response = array('status' => false, 'data' => array('message' => 'MetaKey with code' . $args["error_code"] . ' cound not be found'), 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
            }
        } else {
            $response = array('status' => false, 'data' => array('message' => 'Error Code cannot be empty'), 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
        }
        echo json_encode($response);
        exit();
    }

    public function AssignProductValueinWooFromAllProductTab($args = array())
    {
        $table = $this->db->prefix . self::FPT;
        $pid = $args['pid'];
        $value = $args['value'];
        $code = $args['error_code'];
        $productContainsError = true;
        $allProductResolved = false;
        if (is_array($args) && isset($args['error_code'])) {
            $metakey = $this->getMetaKeyFromErrorCode($args['error_code']);
            if ($metakey) {
                if ($value) {
                    $result = $this->setValueInWoo($metakey, $value, $pid);
                    if ($result) {
                        $qry = $this->db->update($table, array('error_status' => '2'), array('p_id' => $pid, 'error_code' => $args['error_code']));
                        if ($qry) {
                            $productContainsError = $this->CheckIfProductStillContainsError($pid, $args['feedID']);
                            if ($productContainsError == false) {
                                $allProductResolved = $this->checkIfAllProductResolved($args['feedID']);
                            }
                            $response = array('status' => true, 'data' => $args, 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
                        } else {
                            $response = array('status' => false, 'data' => $args, 'error_msg' => 'Data updated in woocommerce but could not find in resolution table', 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
                        }
                    } else {
                        $response = array('status' => false, 'data' => $args, 'error_msg' => "Value in Product level could not be saved. Please try again later. Thanks", 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
                    }
                } else {
                    $response = array('status' => false, 'data' => $args, 'error_msg' => "Value for error code $code was not supplied", 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
                }

            } else {
                $response = array('status' => false, 'data' => array('message' => 'MetaKey with code' . $args["error_code"] . ' cound not be found'), 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
            }
        } else {
            $response = array('status' => false, 'data' => array('message' => 'Error Code cannot be empty'), 'contains_errors' => $productContainsError, 'all_product_resolved' => $allProductResolved);
        }
        echo json_encode($response);
        exit();
    }

    public function getHtmlOfPArticularProduct($args = array())
    {
        $table = $this->db->prefix . self::FPT;
        $feedID = $args['feedID'];
        $pid = $args['pid'];
        $data = array();
        $result = $this->db->get_results("SELECT * FROM {$table} WHERE p_id=$pid AND (error_status <> '1' AND error_status <> '2') AND FIND_IN_SET($feedID,`feed_id`) ORDER BY error_status");
        if ($result) {
            $html = '<td id="aetsmtd_' . $result[0]->p_id . '" style="color:green;"><a class="edit-error all-products-tab-edit-error" data-target="' . $result[0]->p_id . '"><span style="color:red;">Contains Erors</span>Resolve</a>
                            <div style="display: none;" class="error-resolve-div-pep" id="allproduct_' . $result[0]->p_id . '" data-id="' . $result[0]->p_id . '">
                                <ul>';
            foreach ($result as $key => $value) {
                $html .= "<li id='li_" . $value->p_id . "_" . $value->error_code . "'>
                            <a class='edit-error insideerror-each-type'>" . ucfirst(str_replace('_', ' ', $this->getMetaKeyFromErrorCode($value->error_code))) . " Missing</a>
                            <div class='div-for-input' style='display: none;'>
                                <input type='text' class='' id='product_value_" . $value->p_id . "_" . $value->error_code . "' placeholder='Enter  value'>
                                <p class='actions'>
                                    <button type='submit' id='fuckuptest' value='submit' onclick=\"return AMWSCP_AssignProductValueinWooFromAllProductTab(this,'" . $value->p_id . "','" . $value->error_code . "','" . $feedID . "');\">
                                        ✓
                                    </button>
                                    <button onclick='return closeProductEditBox(this);' type='submit' class='edit-cancel-button-xttpy' value='cancel'>✗
                                    </button>
                                </p>
                                 <br>";
                $products = $this->getproductbyErrorCode($feedID, $value->error_code);
                if (is_array($products) && count($products) > 0) {
                    $html .= '<span class="resolve-error-in-bluk-"' . $value->error_code . '>' . count($products) . ' has same error
                              <a onclick="return SelectBulkResolutionofecode(' . $value->error_code . ')" style="cursor: pointer;" class="' . $value->error_code . '">Resolve in bulk</a></span>';
                }

                $html .= "</div></li>";
            }
            $html .= "</ul></div></td>";
            echo json_encode(array('message' => true, 'html' => $html));
            exit();
        }
        echo json_encode(array('message' => false, 'html' => null));
        exit();
    }

    public function getdivHtmlOfPArticularProduct($args = array())
    {
        $table = $this->db->prefix . self::FPT;
        $feedID = $args['feedID'];
        $pid = $args['pid'];
        $data = array();
        $result = $this->db->get_results("SELECT * FROM {$table} WHERE p_id=$pid AND (error_status <> '1' AND error_status <> '2') AND FIND_IN_SET($feedID,`feed_id`) ORDER BY error_status");
        if ($result) {
            $html = '<ul>';
            foreach ($result as $key => $value) {
                $html .= "<li id='li_" . $value->p_id . "_" . $value->error_code . "'>
                            <a class='edit-error insideerror-each-type'>" . ucfirst(str_replace('_', ' ', $this->getMetaKeyFromErrorCode($value->error_code))) . " Missing</a>
                            <div class='div-for-input' style='display: none;'>
                                <input type='text' class='' id='product_value_" . $value->p_id . "_" . $value->error_code . "' placeholder='Enter  value'>
                                <p class='actions'>
                                    <button type='submit' id='fuckuptest' value='submit' onclick=\"return AMWSCP_AssignProductValueinWooFromAllProductTab(this,'" . $value->p_id . "','" . $value->error_code . "','" . $feedID . "');\">
                                        ✓
                                    </button>
                                    <button onclick='return closeProductEditBox(this);' type='submit' class='edit-cancel-button-xttpy' value='cancel'>✗
                                    </button>
                                </p>
                                 <br>";
                $products = $this->getproductbyErrorCode($feedID, $value->error_code);
                if (is_array($products) && count($products) > 0) {
                    $html .= '<span class="resolve-error-in-bluk-"' . $value->error_code . '>' . count($products) . ' products has same error
                              <a onclick="return SelectBulkResolutionofecode(' . $value->error_code . ')" style="cursor: pointer;" class="' . $value->error_code . '">Resolve in bulk</a></span>';
                }

                $html .= "</div></li>";
            }
            $html .= "</ul>";
            echo json_encode(array('message' => true, 'html' => $html));
            exit();
        }
        echo json_encode(array('message' => false, 'html' => null));
        exit();
    }

    public function getAllProducts($args = array())
    {
        $table = $this->db->prefix . self::FPT;
        $feedID = $args['feedID'];
        $data = array();
        $result = $this->db->get_results("SELECT DISTINCT p_id FROM {$table} WHERE FIND_IN_SET($feedID,`feed_id`) ORDER BY error_status");
        if ($result) {
            foreach ($result as $key => $value) {
                $relatedData = $this->db->get_results($this->db->prepare("SELECT * FROM $table WHERE p_id=%d", array($value->p_id)));
                $data[$value->p_id]['child_data'] = $relatedData;
                $data[$value->p_id]['contains_errors'] = false;
                foreach ($relatedData as $k => $val) {
                    $data[$value->p_id]['parent_data'] = $val;
                    if ($val->error_status == '-1' || $val->error_status == '0') {
                        $data[$value->p_id]['contains_errors'] = true;
                    }
                }
            }
            echo json_encode(array('success' => true, 'data' => $data));
            exit();
        }
        echo json_encode(array('success' => true, 'data' => null));
        exit();
    }

    public function getproductbyErrorCode($feed_id, $errorcode)
    {
        $table = $this->db->prefix . "etcpf_feedproducts";
        $productdata = $this->db->get_results("SELECT * FROM {$table} WHERE FIND_IN_SET($feed_id,`feed_id`) AND error_code={$errorcode} AND (error_status<>'2' AND error_status != '1')");
        return $productdata;
    }


    public function CheckIfProductStillContainsError($pid, $feed_id)
    {
        $table = $this->db->prefix . self::FPT;
        $qry = $this->db->prepare("SELECT id FROM $table WHERE p_id=%d AND FIND_IN_SET(%d,`feed_id`) AND (error_status=%s OR error_status=%s)", array($pid, $feed_id, '-1', '0'));
        $result = $this->db->get_results($qry);
        if (is_array($result) && count($result) > 0) {
            return true;
        }
        return false;
    }

    public function checkIfAllProductResolved($feed_id)
    {
        $table = $this->db->prefix . self::FPT;
        $qry = $this->db->prepare("SELECT id FROM $table WHERE FIND_IN_SET(%d,`feed_id`) AND (error_status=%s OR error_status=%s)", array($feed_id, '-1', '0'));
        $result = $this->db->get_results($qry);
        if (is_array($result) && count($result) > 0) {
            return false;
        }
        update_option('ETCPF_RESOLVED', 'yes');
        return true;
    }

    public function setValueInWoo($metakey, $value, $pid)
    {
        if ($value) {
            return update_post_meta($pid, $metakey, $value);
        }
        return false;
    }

    public function getMetaKeyFromErrorCode($code)
    {
        if ($code) {
            switch ($code) {
                case 5201:
                    $attributename = "_sku";
                    break;
                case 5202:
                    $attributename = "_regular_price";
                    break;
                case 5203:
                    $attributename = "_sale_price";
                    break;
                case 5204:
                    $attributename = "_stock_quantity";
                    break;
                case 5205:
                    $attributename = "_brand";
                    break;
                case 5206:
                    $attributename = "_category";
                    break;
                default:
                    $attributename = null;
                    break;
            }
            return $attributename;
        }
    }

    public function _Initiate()
    {
        $method = array_key_exists('perform', $_POST) ? $_POST['perform'] : null;
        $arguments = array_key_exists('params', $_POST) ? $_POST['params'] : $_POST;
        if (!is_array($arguments)) {
            $arguments = array($arguments);
        }
        if (is_null($method)) {
            echo json_encode(array('success' => false, 'msg' => "Methods was null"));
        } elseif (!method_exists($this, $method)) {
            echo json_encode(array('success' => false, 'msg' => "Methods {$method} does not exists."));
        } else {
            call_user_func_array(array($this, $method), array($arguments));
        }
    }

}

$OBJECT = New Etcpf_ResolveFeed();
$OBJECT->_Initiate();
