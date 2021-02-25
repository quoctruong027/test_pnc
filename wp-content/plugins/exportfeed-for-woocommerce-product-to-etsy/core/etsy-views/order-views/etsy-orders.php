<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Orderdisplay extends WP_List_Table
{
    
    public $perpage = 10;
    
    public function __construct()
    {
        global $status, $page;
        
        //Set parent defaults
        parent::__construct(array(
            'singular' => 'etsyorder',
            'plural' => 'etsyorders', //plural name of the listed records
            'ajax' => true, //does this table support ajax?
        ));
        
    }
    
    public function prepare_items()
    {
        global $wpdb; /* Comments */
        $per_page = $this->perpage; /* Item per page */
        $columns = $this->get_columns(); /* Get the table headers */
        $hidden = array(); /* Define if any hidden items exists */
        $sortable = $this->get_sortable_columns(); /* Get Sortables columns */
        $this->_column_headers = array($columns, $hidden, $sortable);
        //$this->process_bulk_action();
        $data = $this->get_items();
        usort($data, array($this, 'usort_reorder'));
        $current_page = $this->get_pagenum();
        $total_items = $this->get_total_items();
        $this->items = $data;
        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page, //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page), //WE have to calculate the total number of pages
        ));
    }
    
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'receipt_id' => 'Order Id',
            'price' => 'Price',
            'quantity' => 'Quantity',
            'state' => 'State',
            'buyers_info' => 'Buyers Info',
            'ordered_date' => 'Ordered On',
        );
        return $columns;
    }
    
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'price' => array('price', false),
            'quantity' => array('quantity', false),
            'ordered_date' => array('ordered_date', true),
        );
        return $sortable_columns;
    }
    
    public function get_items()
    {
        $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $offset = ($current_page - 1) * $this->perpage;
        global $wpdb;
        $table = $wpdb->prefix . "etcpf_orders";
        $query = "SELECT * FROM {$table}";
        $query .= " GROUP BY receipt_id ORDER BY creation_tsz DESC";
        $query .= " LIMIT {$offset},{$this->perpage}";
        $data = $wpdb->get_results($query);
        $items = array();
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $datum) {
                $items[$key]['id'] = $datum->id;
                $items[$key]['receipt_id'] = $datum->receipt_id;
                $items[$key]['quantity'] = intval($wpdb->get_var("SELECT SUM(quantity) FROM {$table} WHERE receipt_id={$datum->receipt_id}"));
                $items[$key]['price'] = number_format($wpdb->get_var("SELECT SUM(price * quantity) FROM {$table} WHERE receipt_id={$datum->receipt_id}") + $datum->shipping_cost, 2);
                $items[$key]['state'] = $datum->state;
                $items[$key]['shipping_cost'] = $datum->shipping_cost;
                $items[$key]['currency_code'] = $datum->currency_code;
                $items[$key]['buyers_info'] = $datum->buyers_info;
                $items[$key]['ordered_date'] = $datum->ordered_date;
            }
        }
        
        return $items;
    }
    
    public function get_total_items()
    {
        global $wpdb;
        $table = $wpdb->prefix . "etcpf_orders";
        return $wpdb->get_var("SELECT COUNT( DISTINCT  receipt_id ) as total_orders FROM {$table}");
    }
    
    public function usort_reorder($a, $b)
    {
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
        $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
        return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
    }
    
    public function column_default($data, $column_name)
    {
        switch ($column_name) {
            case 'quantity':
                $html = $data['quantity'];
                return $html;
            case 'state':
                $html = $data['state'];
                return $html;
            case 'ordered_date':
                $html = $data['ordered_date'];
                return $html;
            case 'price':
                $html = $data['price'];
                return $data['currency_code'] . ' ' . number_format($html, 2);
            case 'buyers_info':
                $raw = json_decode($data['buyers_info']);
                $html = "<p>Name: {$raw->name}<br>City:{$raw->city}<br>State:{$raw->state}<p>";
                return $html;
            default:
                return print_r($data, true); //Show the whole array for troubleshooting purposes
        }
        
    }
    
    public function process_bulk_action()
    {
        global $wpdb;
        $action = $this->current_action();
        $allowed_actions = array('edit');
        if (!in_array($action, $allowed_actions)) {
            return;
        }
    }
    
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item['id']
        );
    }
    
    public function column_receipt_id($item)
    {
        $action['View'] = '<a id="order-detail-btn-' . $item['receipt_id'] . '" class="etsy-detail-order-view-btn" data-id="' . $item['id'] . '" data-receipt-id="' . $item['receipt_id'] . '" data-expanded="false" data-details=' . "'" . json_encode($item) . "'" . ' href="javascript:void(0);">View Detail</a>';
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/
            '#' . $item['receipt_id'],
            /*$2%s*/
            $item['id'],
            /*$3%s*/
            $this->row_actions($action, 'visible')
        );
    }
    
    /*public function get_bulk_actions()
    {
        $actions = array(
            'test' => 'Test',
        );
        return $actions;
    }*/
    
    public function perform_export($post_id)
    {
        return true;
    }
    
} /* Class Ends Here */
$orderTable = new Orderdisplay();
$orderTable->prepare_items();
?>

<div style="display: none;" id="ajax-loader-cat-import"><span id="gif-message-span"></span></div>
<div class="wrap">
    <div id="poststuff">
        <h2>Etsy Order Detail</h2>
        <div id="postbox-container-2" class="postbox-container">
        </div>
    </div>
    <div class="clear"></div>

    <div id="post-body" class="metabox-holder columns-2" style="margin-bottom: 45px;">
        <div id="postbox-container-2" class="postbox-container">
            <select id="etsy-manual-order-sync-dropdown" name="days" value="">
                <option value="">Since latest order received</option>
                <option value="1">1 Days</option>
                <option value="2">2 Days</option>
                <option value="3">3 Days</option>
                <option value="5">5 Days</option>
                <option value="6">8 Days</option>
                <option value="14">2 Weeks</option>
                <option value="30">1 Month</option>
                <option value="90">3 Months</option>
                <option value="182">6 Months</option>
                <option value="365">1 Years</option>
                <option value="730">2 Years</option>
                <option value="1105">3 Years</option>
            </select>
            <button class="button button-primary button-update-order ">Update Orders</button>
            <img class="amwscpf_update_order" style="display:none"
                 src="<?php echo ETCPF_URL . 'images/ajax-loader.gif' ?>"
                 height="25" width="30">
            <p id="amwscp_order_update_msg"></p>
        </div>
    </div>

    <div class="clear"></div>
    <form id="etcpf-orders" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php
        $orderTable->search_box('search', 'search_id');
        $orderTable->display()
        ?>
    </form>
</div>

<script>
    (function ($) {
        let loader = $('#ajax-loader-cat-import');
        $(document).on('click', '.etsy-detail-order-view-btn', function () {
            $('.hidden-order-tr').remove();
            selector = this;
            let identifier = this.id,
                row_index = $(identifier).closest('tr').index();
            let order_id = $(selector).attr('data-receipt-id');
            let id = $(selector).attr('data-id'),
                order_detail_button = $('#order-detail-btn-' + order_id),
                etsy_order_detail_view_btn = $('.etsy-detail-order-view-btn');

            loader.show();
            etsy_order_detail_view_btn.html('View Details');
            etsy_order_detail_view_btn.not(this).attr('data-expanded', 'false');
            
            if ($(this).attr('data-expanded') === 'true' && typeof $('.etsy-order-detail').val() !== 'undefined') {
                $('.etsy-order-detail').remove();
                order_detail_button.attr('data-expanded', 'false');
                loader.hide();
            } else {
                getOrderDetailItems(order_id, function (html) {
                    $('.etsy-order-detail').remove();
                    order_detail_button.attr('data-expanded', 'true');
                    order_detail_button.html('Close');
                    order_detail_button.closest('tr').after(html);
                    loader.hide();
                })
            }
        });

        $(document).on('click', '.order_detail_close', function () {
            $(".etsy-order-detail").remove();
            $(".etsy-detail-order-view-btn").html('View Details')
        });

        let getOrderDetailItems = function (id, callback) {

            let payload = {
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmd_get_order_details,
                security: ETCPF.ETCPF_nonce,
                order_id: id,
                perform: 'fetch_shipping_providers',
            }
            etcpfglobalAjax(this, payload, function (error, data) {
                if (error) {
                    return '<tr class="hidden-order-tr" style="display: none"></tr><tr><td style="text-align:center;" colspan="7">No details Found</td></tr>'
                }
                let parent_data = $('#order-detail-btn-' + id).data('details'),
                    buyer_info = JSON.parse(parent_data.buyers_info),
                    time = new Date(parent_data.ordered_date);
                let html = '<tr class="hidden-order-tr" style="display: none"></tr>' +
                    '<tr class="etsy-order-detail">\n' +
                    '    <td colspan="7">\n' +
                    '        <h2 class="order_detail_page_heading">#' + id + '<span class="dashicons dashicons-no-alt order_detail_close" style="font-size: 25px; float:right; color: #0073aa; cursor: pointer;"></span></h2>' +
                    '          <table class="order_detail_page" id="order_data" style="border: 1px solid #dcd2d2;width:100%">' +
                    '            <tbody><tr>\n' +
                    '                <td class="order_detail_page_categoties"> <h3>General</h3></td>\n' +
                    '                <td class="order_detail_page_categoties"> <h3>Billing</h3></td>\n';

               /* if (parseFloat(parent_data.shipping_cost) > 0)
                    html += '                <td id="shipping-section" class="order_detail_page_categoties"> <h3>Shipping</h3></td>\n';*/

                html +=
                    '            </tr>\n' +
                    '            <tr>\n' +
                    '                <td class="order_detail_column">\n' +
                    '                    <table>\n' +
                    '                        <tbody><tr>\n' +
                    '                            <td>\n' +
                    '                                <p class="form-field form-field-wide">\n' +
                    '                                            <label style="width: auto" for="order_date">Date created: '+parent_data.ordered_date+'</label>\n' +
                    /*'                                            <input readonly type="text" class="date-picker hasDatepicker" name="order_date" maxlength="10" value="' + time.getFullYear() + '-' + '0'+(time.getMonth() + 1) + '-' + '0'+time.getDate() + '" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" id="dp1557210297487">@\n' +
                    '                                            ‎\n' +
                    '                                            <input readonly type="number" class="hour" placeholder="h" name="order_date_hour" min="0" max="23" step="1" value="' + time.getHours() + '" pattern="([01]?[0-9]{1}|2[0-3]{1})">:\n' +
                    '                                            <input readonly type="number" class="minute" placeholder="m" name="order_date_minute" min="0" max="59" step="1" value="' + time.getMinutes() + '" pattern="[0-5]{1}[0-9]{1}">\n' +
                    '                                            <input type="hidden" name="order_date_second" value="51">\n' +*/
                    '                                </p>\n' +
                    '                            </td>\n' +
                    '                        </tr>\n' +
                    '                        <tr>\n' +
                    '                            <td>\n' +
                    '                                <p>\n' +
                    '                                    <label style="width: auto"> Status:    ' + parent_data.state + '</label>\n' +
                    /*'                                    <select disabled class="" id="" name="" data-placeholder="Guest" data-allow_clear="true" tabindex="-1" aria-hidden="true">\n' +
                    '                                        <option value="" selected="selected">' + parent_data.state + '</option>\n' +
                    '                                    </select>\n' +*/
                    '                                </p>\n' +
                    '                            </td>\n' +
                    '                        </tr>\n' +
                   /* '                        <tr>\n' +
                    '                            <td>\n' +
                    '                                <p>\n' +
                    '                                    <label> Customer:</label>\n' +
                    '                                    <select disabled class="" id="" name="" data-placeholder="Guest" data-allow_clear="true" tabindex="-1" aria-hidden="true">\n' +
                    '                                        <option value="" selected="selected"> Guest</option>\n' +
                    '                                        <option value="" > User</option>\n' +
                    '                                    </select>\n' +
                    '                                </p>\n' +
                    '                            </td>\n' +
                    '                        </tr>\n' +*/
                    '                    </tbody></table>\n' +
                    '                </td>\n' +
                    '                <td>\n' +
                    '                    <table>\n' +
                    '                        <tbody>' +
                    '                          <tr>\n' +
                    '                            <td>\n' +
                    '                                <p> Name :  ' + buyer_info.name + '</p>\n' +
                    '                                <p> Email : ' + buyer_info.buyer_email + '</p>\n' +
                    '                                <p> Address : ' + buyer_info.formatted_address + '</p>\n' +
                    '                                <p> </p>\n' +
                    '                            </td>\n' +
                    '                        </tr>\n' +
                    '                      </tbody>' +
                    '                    </table>\n' +
                    '                </td>\n';

                /*if(parseFloat(parent_data.shipping_cost)>0)
                    html+='                <td>\n' +
                    '                         <table>\n' +
                    '                            <tbody>' +
                    '                             <tr>\n' +
                    '                               <td>\n' +
                    '                                 <label> Shipping Cost: '+parent_data.currency_code +' '+parent_data.shipping_cost+'</label>\n' +
                    '                               </td>\n' +
                    '                             </tr>\n' +
                    '                            </tbody>' +
                    '                          </table>\n' +
                    '                         </td>\n';*/
                html +=
                    '            </tr>\n' +
                    '            <tr>\n' +
                    '                <td colspan="4" class="list_order_items">\n' +
                    '                    <table class="order_items_list_rows" width="100%">\n' +
                    '                        <thead align="left">\n' +

                    '                            <tr>\n' +
                    '                                <th>Items Name</th>\n' +
                    '                                <th>Items Cost</th>\n' +
                    '                                <th>Items Qty</th>\n' +
                    '                                <th>Total Cost</th>\n' +
                    '                            </tr>\n' +

                    '                        </thead>\n' +

                    '                        <tbody>\n';

                $(data).each(function (index, data) {
                    html +=
                        '                            <tr>\n' +
                        '                                <td>' + data.title + '</td>\n' +
                        '                                <td>' + parseFloat(data.price).toFixed(2) + '</td>\n' +
                        '                                <td>' + data.quantity + '</td>\n' +
                        '                                <td>' + (parseInt(data.quantity) * parseFloat(data.price)).toFixed(2) + '</td>\n' +
                        '                            </tr>\n';
                });

                html += '                        </tbody>\n' +

                    '                    </table>\n' +
                    '                </td>\n' +
                    '            </tr>\n' +

                    '            <tr style="border-bottom: solid 1px #999999;">\n' +
                    '                <td align="left"><h3> Shipping: </h3></td>\n' +
                    '                <td style="text-align: center"><h3 style="margin-left: 48%">'+parseFloat(parent_data.shipping_cost).toFixed((2))+'</h3></td>\n' +
                    '            </tr>\n' +
                    '            <tr>\n' +
                    '                <td align="left"><h3> Total Cost: </h3></td>\n' +
                    '                <td style="text-align: center"><h3 style="margin-left: 48%">'+ parent_data.currency_code + ' ' + parseFloat(parent_data.price).toFixed(2) + '</h3></td>\n' +
                    '            </tr>\n' +

                    '        </tbody></table>\n' +
                    '    </td>\n' +
                    '</tr>';
                callback(html);
            });
        };
    })(jQuery)
</script>
