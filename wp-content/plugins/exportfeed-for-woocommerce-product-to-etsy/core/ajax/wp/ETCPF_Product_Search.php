<?php
if (!defined('ABSPATH')) {
    exit;
}
if (defined('ENV') && ENV == 'development') {
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
$file = dirname(__FILE__) . '/../../data/ETCPF_Productsw.php';
if (file_exists(realpath($file))) {
    include_once $file;
} else {
    die($file . " doesn't exists");
}

Class ETCPF_Product_Search extends ETCPF_Products_Store
{

    public function __construct()
    {
        parent::__construct();
    }

    public function searchProduct($args = array())
    {
        $selectedProducts = isset($args['products']) ? $args['products'] : array();
        $selectedProductArray = json_decode(stripslashes($selectedProducts), true);
        $page = $args['page'];
        $limit = 20;
        $offset = intval($limit) * intval($page);
        $productsData = parent::getProducts($args, $limit, $offset);
        $html = $this->getRowHtml($productsData['products'], $selectedProductArray);
        $productsData['products'] = $html;
        wp_send_json_success($productsData);
        exit;
    }

    public function getRowHtml(array $products, $selectedProductArray)
    {
        if (is_array($products) && count($products)>0) {
            $sn = 0;
            $productrows = '';
            foreach ($products as $key => $values) {
                if (array_key_exists($values->ID, $selectedProductArray)) {
                    $selected = 'checked';
                    $checkedClass = 'checked-fcked-class-bg';
                } else {
                    $selected = '';
                    $checkedClass = '';
                }
                $catString = '';
                foreach (get_the_terms($values->ID, 'product_cat') as $key => $cat) {
                    if (strlen($catString) > 3) {
                        $catString .= ',' . $cat->slug;
                    } else {
                        $catString .= $cat->slug;
                    }

                }
                /* $productData = parent::getWooProductData($values->ID);
                 echo "<pre>";
                 print_r($productData);
                 echo "</pre>";
                 exit();*/
                $metavalues = $this->getMetavalueByProductID($values->ID);
                $childrens = $this->getProductChildren($values->ID);
                $sn++;
                $childclass = 'childof-' . $values->ID;
                $parentclass = 'parent-' . $values->ID;
                if ($sn % 2 == 0) {
                    $class = 'even-striped';
                } else {
                    $class = 'odd-striped';
                }
                $metavalues = $this->getMetavalueByProductID($values->ID);
                $sprice = $sprice = isset($metavalues['_sale_price']) ? (isset($metavalues['_sale_price'][0]) ? $metavalues['_sale_price'][0] : '--') : '--';
                $rprice = isset($metavalues['_regular_price']) ? ($metavalues['_regular_price'][0] ? $metavalues['_regular_price'][0] : '--') : '';
                $quantity = $metavalues['_stock'][0] ? $metavalues['_stock'][0] : '--';
                $title = strlen($values->post_title) > 40 ? substr($values->post_title, 0, 100) . '...' : $values->post_title;
                $productrows .= '<tr class="' . $class . ' ' . $checkedClass . ' parent-' . $values->ID . ' parent-tr " data-cat_slugs="' . $catString . '">
                                    <td style="text-align:center;"><input data-child="' . $childclass . '" class="parent-product-checkbox" type="checkbox" ' . $selected . ' value="' . $values->ID . '"></td>
                                    <td>&nbsp;</td>
                                    <td class="index">' . $title . '</td>
                                    <td class="index">' . $metavalues['_sku'][0] . '</td>
                                    <td class="index">' . $catString . '</td>
                                    <td style="text-align:center;"> ' . $sprice . ' </td>
                                    <td style="text-align:center;"> ' . $rprice . ' </td>
                                    <td style="text-align:center;">' . $quantity . '</td>
                                </tr>';
                if (is_array($childrens) && count($childrens) > 0) {
                    foreach ($childrens as $key => $child) {
                        if (array_key_exists($values->ID, $selectedProductArray) && in_array($child->ID, $selectedProductArray[$values->ID]['child']['ids'])) {
                            $childselected = 'checked';
                            $chilcheckedClass = 'checked-fcked-class-bg';
                        } else {
                            $childselected = '';
                            $chilcheckedClass = '';
                        }
                        $childmetavalues = $this->getMetavalueByProductID($child->ID);
                        $csprice = isset($childmetavalues['_sale_price'][0]) ? $childmetavalues['_sale_price'][0] : '--';
                        $rprice = isset($childmetavalues['_regular_price']) ? ($childmetavalues['_regular_price'][0] ? $childmetavalues['_regular_price'][0] : '--') : '';
                        $quantity = isset($childmetavalues['_stock'][0]) ? $childmetavalues['_stock'][0] : '--';
                        $sku = isset($childmetavalues['_stock'][0]) ? $childmetavalues['_stock'][0] : '';
                        $category = explode(',', $child->category)[0];
                        $productrows .= '<tr class="' . $class . ' ' . $childclass . ' ' . $chilcheckedClass . '">
                                                    <td><img src="' . ETCPF_URL . '/images/enter.png" class="arrow_productlist" /></td>
                                                    <td style="text-align:center; width:40px;"><input data-parent="' . $parentclass . '" class="child-product-checkbox" type="checkbox" ' . $childselected . ' value="' . $child->ID . '">
                                                    </td>
                                                    <td class="index">' . $child->post_title . '</td>
                                                    <td class="index">' . $sku. '</td>
                                                    <td class="index">' . $category . '</td>
                                                    <td style="text-align:center;">' . $csprice . '</td>
                                                    <td style="text-align:center;">' . $rprice . '</td>
                                                    <td style="text-align:center;">' . $quantity . '</td>
                                                </tr>';
                    }
                }

            }
            return $productrows;
        }
        return null;
    }

    public function _Initiate()
    {
        $method = array_key_exists('perform', $_POST) ? $_POST['perform'] : null;
        $arguments = array_key_exists('params', $_POST)  ? $_POST['params'] : $_POST;
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

$OBJECT = New ETCPF_Product_Search();
$OBJECT->_Initiate();
