<?php
class WC_Dropshipping_Product_Extra_Fields {

	public function __construct() {

		add_action( 'woocommerce_after_shop_loop_item', array($this,'Show_Suppliername_On_Product_Page'), 9 );
		add_action( 'woocommerce_single_product_summary', array($this,'Show_Suppliername_On_Product_Page'), 21, 10 );

		// For custom extra Amazon Affiliate fields.
		add_action( 'woocommerce_product_options_general_product_data',array($this,'Woocommerce_Product_Custom_Fields_Amazon_Affiliate_ID' ),10);
		add_action( 'woocommerce_process_product_meta', array($this,'Woocommerce_Product_Custom_Fields_Amazon_Affiliate_ID_Save' ),10);

		add_action( 'woocommerce_product_options_general_product_data',array($this,'Woocommerce_Product_Custom_Fields_Amazon_Product_ID' ),10);
		add_action( 'woocommerce_process_product_meta', array($this,'Woocommerce_Product_Custom_Fields_Amazon_Product_ID_Save' ),10);
		// For custom extra inventory fields.
		add_action('woocommerce_product_options_inventory_product_data', array($this,'woocommerce_product_extra_fields') );
		add_action( 'woocommerce_process_product_meta', array($this,'save_woocommerce_product_extra_fields' ));

		// For custom supplier tab and its fields.
		add_filter( 'woocommerce_product_data_tabs', array($this,'my_custom_supplier_tab' ) );
		add_action( 'woocommerce_product_data_panels', array($this,'supplier_tab_panel' ) );
		add_action( 'woocommerce_process_product_meta', array($this,'save_supplier_field' ));

		// The code for displaying WooCommerce Product Custom Fields
        add_action( 'woocommerce_product_options_general_product_data',array($this,'woocommerce_product_custom_fields' ));
        add_action( 'woocommerce_process_product_meta', array($this,'woocommerce_product_custom_fields_save' ));
        add_action( 'woocommerce_variation_options_pricing', array($this,'bbloomer_add_custom_field_to_variations'), 10, 3 );
        add_action( 'woocommerce_save_product_variation', array($this,'bbloomer_save_custom_field_variations'), 10, 2 );
        add_filter( 'woocommerce_available_variation',array($this,'bbloomer_add_custom_field_variation_data' ));

		// For Description custom fields

        add_action( 'woocommerce_variation_options_pricing', array($this,'bbloomer_add_custom_field_description_to_variations'), 10, 3 );
        add_action( 'woocommerce_save_product_variation', array($this,'bbloomer_save_custom_field_description_variations'), 10, 2 );
        add_filter( 'woocommerce_available_variation',array($this,'bbloomer_add_custom_field_description_variation_data' ));
        add_action( 'woocommerce_product_options_general_product_data',array($this,'woocommerce_product_custom_fields_description' ));
        add_action( 'woocommerce_process_product_meta', array($this,'woocommerce_product_custom_fields_description_save' ));

        // Hide External Products Only Prices
		add_filter( 'woocommerce_variable_sale_price_html', array($this, 'woocommerce_remove_prices'), 10, 2 );
		add_filter( 'woocommerce_variable_price_html', array($this, 'woocommerce_remove_prices'), 10, 2 );
		add_filter( 'woocommerce_get_price_html', array($this, 'woocommerce_remove_prices'), 10, 2 );


        //end

		//add_action('woocommerce_process_product_meta', array($this,'save_supplier_field') );
		//add_action( 'woocommerce_process_product_meta', array($this,'supplier' ) );
		//add_action('rest_api_init', array($this,'rest_api_player_meta'));
		//add_filter( 'woocommerce_rest_prepare_product', array($this,'custom_products_api_data', 90, 2 ) );
		//add_action( 'rest_api_init', array($this,'slug_register_overhead' ));
		//add_action( 'rest_api_init', array($this,'slug_register_number_of_orders' ));
		// For Supplier price
		/*
		add_action( 'woocommerce_before_calculate_totals', array($this,'extra_price_add_custom_price'), 20, 1 );
		add_action( 'woocommerce_before_add_to_cart_button', array($this,'custom_product_price_field'), 5 );
		add_filter('woocommerce_cart_item_price', array($this,'display_cart_items_custom_price_details'), 20, 3 );
		add_filter('woocommerce_add_cart_item_data', array($this,'add_custom_field_data'), 20, 2 );
		*/
	}

	/* Related to COST OF GOODS - Custom field For Add Supplier Price

	function custom_product_price_field(){
	    echo '<div class="custom-text text">
	    <p>Extra Charge ('.get_woocommerce_currency_symbol().'):</p>
	    <input type="text" name="custom_price" value="" placeholder="e.g. 10" title="Custom Text" class="custom_price text_custom text">
	    </div>';
	}

	// Get custom field value, calculate new item price
	public function add_custom_field_data( $cart_item_data, $product_id ){

	    	if(!empty($product = wc_get_product($product_id))){// The WC_Product Object
		   	$postid = $product->get_id();
		    $suppid = get_post_meta($postid, 'supplierid', true);
		    $price = get_term_meta($suppid);
		    $var1 = unserialize($price['meta'][0]);
			$var = $var1['supplier_price'];
			$supp_price = $var;
		    $base_price = $product->get_price(); // Product reg price
		    $custom_price = $base_price / 100 * $supp_price; // New price calculation
		    $new_price = $base_price + $custom_price;
		    $cart_item_data['custom_data']['extra_charge'] = (float) $custom_price;
		    $cart_item_data['custom_data']['new_price'] = (float) $new_price;
		    $cart_item_data['custom_data']['unique_key'] = md5( microtime() . rand() ); // Make each item unique
		}
	    return $cart_item_data;
	}

	// Set the new calculated cart item price
	public function extra_price_add_custom_price( $cart ) {
	    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
	        return;

	    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
	        return;

	    foreach ( $cart->get_cart() as $cart_item ) {
	        if( isset($cart_item['custom_data']['new_price']) )
	            $cart_item['data']->set_price( (float) $cart_item['custom_data']['new_price'] );
	    }
	}

	//Display cart item custom price details

	function display_cart_items_custom_price_details( $product_price, $cart_item, $cart_item_key ){
	    if( isset($cart_item['custom_data']['extra_charge']) ) {
	        $product = $cart_item['data'];
	        //$product_price  = wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_price() ) ) );
	        //$product_price = '<br>' . wc_price( $cart_item['custom_data']['extra_charge'] ).'&nbsp;';
	        $product_price .= __("Supplier Charge", "woocommerce" );
	    }
	    return $product_price;
	}*/


	/*public function slug_register_number_of_orders() {
        register_rest_field( 'product',
            'number_of_orders',
            array(
                'get_callback'    => 'slug_get_number_of_orders',
                'update_callback' => null,
                'schema' => array(
            		'number_of_orders' => __( 'number_of_orders' ),
            		'type'        => 'text'
        		),
        	)
        );
	}

	public function slug_get_number_of_orders( $object ) {
	    return get_post_meta( $object[ 'id' ], 'number_of_orders', true );
	} */


	/**
	* Show supplier name on product page.
	**/
	function Show_Suppliername_On_Product_Page() {
		global $product;
		$options = get_option('wc_dropship_manager');
		if (isset($options['hide_suppliername_on_product_page'])) {

			$hide_suppliername_on_product_page = $options['hide_suppliername_on_product_page'];

		} else {

			$hide_suppliername_on_product_page = '';
		}

		if ($hide_suppliername_on_product_page == '1' ) {

	   		$products_id = $product->get_id();
			$s_name = get_post_meta($products_id, 'supplier', true);

	   		if(!empty($s_name)) {
	   			echo '<p id="supplier_product_page"> Supplier: '.$s_name.'</p>'.PHP_EOL;

			}
		}
	}

	/**
	* Adding a Field for Amazon_Product_ID
	**/
	function woocommerce_remove_prices( $price, $product ) {
		if( $product->is_type( 'external' )){
			if ($product->get_price() == 0){
				$price = '';
			}
		}
			return $price;
	}

	/**
	* Adding a Field for Amazon_Product_ID
	**/

	function Woocommerce_Product_Custom_Fields_Amazon_Product_ID () {

	    global $woocommerce, $post; ?>
	    <script>

			jQuery("#_product_url").keyup(function(){
		        var Text = jQuery(this).val();
		       	var creativeASIN = Text.split("/");
		        jQuery("#product_custom_field_amazon_product_id").val(creativeASIN[5]);
			});
		</script>

	    <?php

	    echo '<div class="product_custom_field_amazon_product_id">';

	    woocommerce_wp_text_input(
	    	array(
	        'id'          => 'product_custom_field_amazon_product_id',
	        'label'       => __( 'Amazon Product ID', 'woocommerce' ),
	        //'desc_tip'    => 'true',
			//'description' => __( 'Description - Type of Package value included in the supplier email', 'woocommerce' )
	        //'placeholder' => 'Custom Product Text Field',

	      	)
	    );

	    echo '</div>';
	}

    function Woocommerce_Product_Custom_Fields_Amazon_Product_ID_Save($post_id) {
	    // Custom Product Text Field

	    $woocommerce_product_custom_field_amazon_product_id = $_POST['product_custom_field_amazon_product_id'];

	    if(isset($woocommerce_product_custom_field_amazon_product_id))

	        update_post_meta($post_id, 'product_custom_field_amazon_product_id',
	        esc_attr($woocommerce_product_custom_field_amazon_product_id));
    }


    /**
	* Adding a Field for Amazon_Affiliate_ID
	**/

	function Woocommerce_Product_Custom_Fields_Amazon_Affiliate_ID () {

	    global $woocommerce, $post; ?>
	    <script>
		    jQuery('#product-type').on('change', function() {
		  		jQuery('.product_custom_field_amazon_affiliate_id').hide();
		  		jQuery('.product_custom_field_amazon_product_id').hide();
		     	var val = jQuery(this).val()
		    	if(val == 'external'){
		        	jQuery('.product_custom_field_amazon_affiliate_id').show();
		  			jQuery('.product_custom_field_amazon_product_id').show();
		    	}
			});

			jQuery("#_product_url").keyup(function(){

		        var Text = jQuery(this).val();
		       	var creativeASIN = Text.split("&");
		       	var value = creativeASIN[5].split("tag=");
				jQuery("#product_custom_field_amazon_affiliate_id").val(value[1]);

			});
		</script>

		<?php
	    echo '<div class=" product_custom_field_amazon_affiliate_id ">';

	    woocommerce_wp_text_input(
	    	array(
	        'id'          => 'product_custom_field_amazon_affiliate_id',
	        'label'       => __( 'Amazon Affiliate ID', 'woocommerce' ),
	        //'desc_tip'    => 'true',
			//'description' => __( 'Description - Type of Package value included in the supplier email', 'woocommerce' )
	        //'placeholder' => 'Custom Product Text Field',

	      	)
	    );

	    echo '</div>';
	}

    function Woocommerce_Product_Custom_Fields_Amazon_Affiliate_ID_Save($post_id) {
	    // Custom Product Text Field

	    $woocommerce_product_custom_field_amazon_affiliate_id = $_POST['product_custom_field_amazon_affiliate_id'];

	    if(isset($woocommerce_product_custom_field_amazon_affiliate_id))

	        update_post_meta($post_id, 'product_custom_field_amazon_affiliate_id',
	        esc_attr($woocommerce_product_custom_field_amazon_affiliate_id));
    }

	/**
	* Adding a Cost of goods
	**/

    function bbloomer_add_custom_field_to_variations( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( array(
			'id' => 'custom_field[' . $loop . ']',
			'class' => 'short',
			'label' => __( 'Cost of goods', 'woocommerce' ).' (' . get_woocommerce_currency_symbol().')',
			'value' => get_post_meta( $variation->ID, 'custom_field', true )
			)
		);
    }



    function bbloomer_save_custom_field_variations( $variation_id, $i ) {
		$custom_field = $_POST['custom_field'][$i];
		if ( isset( $custom_field ) ) update_post_meta( $variation_id, 'custom_field', esc_attr( $custom_field ) );
    }


    function bbloomer_add_custom_field_variation_data( $variations ) {
		$variations['custom_field'] = '<div class="woocommerce_custom_field">Custom Field: <span>' . get_post_meta( $variations[ 'variation_id' ], 'custom_field', true ) . '</span></div>';
		return $variations;
    }

	function woocommerce_product_custom_fields () {
	    global $woocommerce, $post;
	    echo '<div class=" product_custom_field ">';
	    woocommerce_wp_text_input(
	    	array(
	        'id'          => '_cost_of_goods',
	        'label'       => __( 'Cost of goods', 'woocommerce' ).' (' . get_woocommerce_currency_symbol().')',
	        'desc_tip'    => 'true',
			'description' => __( 'Cost of goods value included in the supplier email', 'woocommerce' )
	        //'placeholder' => 'Custom Product Text Field',

	      	)
	    );
	    echo '</div>';
	}

    function woocommerce_product_custom_fields_save($post_id) {
	    // Custom Product Text Field
	    $woocommerce_custom_product_text_field = $_POST['_cost_of_goods'];
	    if(isset($woocommerce_custom_product_text_field))
	        update_post_meta($post_id, '_cost_of_goods',
	        esc_attr($woocommerce_custom_product_text_field));

    }


	/**
	* Adding a custom number_of_orders
	**/

	public function woocommerce_product_extra_fields() {
		$args = array(
		  'id' => 'number_of_orders',
		  'label' => __('AliExpress Orders', 'order_placed'),
		  'description'=> __('AliExpress Orders placed.')
		);
		woocommerce_wp_text_input($args);
	}

 	public function save_woocommerce_product_extra_fields($post_id) {
 		$custom_fields_woocommerce_title = isset($_POST['number_of_orders']) ? $_POST['number_of_orders'] : '';
	    $product = wc_get_product($post_id);
        update_post_meta($post_id,'number_of_orders',$custom_fields_woocommerce_title);

	}


	/**
	* Adding a custom Supplier tab
	**/
	public function my_custom_supplier_tab( $tabs ) {

		$tabs['supplier_tab'] = array(
		'label'  => __( 'AliExpress Supplier', 'woocommerce' ),
		'target' => 'the_supplier_custom_panel',
		'class'  => array(),
		//'style' => 'content: "\f174";'
		);

	  	return $tabs;
	}

	public function supplier_tab_panel() { ?>
	  	<div id="the_supplier_custom_panel" class="panel woocommerce_options_panel">
	    	<div class="options_group">
	     	<?php
		        /*
		        $product_id = array(
		          	'id' => 'supplier_product_id',
		          	'label' => __( 'Product ID', 'woocommerce'),
		          	'desc_tip' => 'true',
		          	'description' => __( 'Product id on AliExpress' ),
		          	'custom_attributes' => array(
		    	  		'readonly' => 'readonly'
		    	  	),
		        );
		        */
		        $productUrl = array(
		          	'id' => 'ali_product_url',
		          	'label' => __( 'Product URL', 'woocommerce'),
		          	'description' => __( 'Enter URL to AliExpress Product' ),

		        );

		        $storeName = array(
		          	'id' => 'ali_store_name',
		          	'label' => __( 'Store Name', 'woocommerce' ),
		          	'description' => __( 'AliExpress Supplier Store Name' ),
		          	'desc_tip' => 'true',
		          	'custom_attributes' => array(
		    	  		'readonly' => 'readonly'
		    	  	),
		        );

		        $storeUrl = array(
		          	'id' => 'ali_store_url',
		          	'label' => __( 'Store URL', 'woocommerce'),
		          	'description' => __( 'AliExpress Supplier Store URL' ),
		          	'desc_tip' => 'true',
		          	'custom_attributes' => array(
		    	  		'readonly' => 'readonly'
		    	  	),
		        );
		        $price_range = array(
		          	'id' => 'ali_store_price_range',
		          	'label' => __( 'Store Price Range' ),
		          	'description' => __( 'AliExpress Supplier Store Price Range' ),
		          	'desc_tip' => 'true',
		          	'custom_attributes' => array(
		    	  		'readonly' => 'readonly'
		    	  	),
		        );
		         $currency = array(
		          	'id' => 'ali_currency',
		          	'label' => __( 'Currency' ),

		          	//'desc_tip' => 'true',

		        );

		        //woocommerce_wp_text_input( $product_id );
		        woocommerce_wp_text_input( $productUrl );
		        woocommerce_wp_text_input( $storeName );
		        woocommerce_wp_text_input( $storeUrl );
		        woocommerce_wp_text_input( $price_range );
		        woocommerce_wp_text_input( $currency );
	      	?>
	    	</div>
		</div>
	<?php
	}

	public function save_supplier_field( $post_id ) {

		//$supplier_product_id = isset( $_POST['supplier_product_id'] ) ? $_POST['supplier_product_id'] : '';
		$ali_product_url = isset( $_POST['ali_product_url'] ) ? $_POST['ali_product_url'] : '';
		$ali_store_url = isset( $_POST['ali_store_url'] ) ? $_POST['ali_store_url'] : '';
		$ali_store_name = isset( $_POST['ali_store_name'] ) ? $_POST['ali_store_name'] : '';
		$ali_store_price_range = isset( $_POST['ali_store_price_range'] ) ? $_POST['ali_store_price_range'] : '';
		$ali_currency = isset( $_POST['ali_currency'] ) ? $_POST['ali_currency'] : '';

		$product = wc_get_product($post_id);
        update_post_meta($post_id,'ali_product_url',$ali_product_url);
        update_post_meta($post_id,'ali_store_url',$ali_store_url);
        update_post_meta($post_id,'ali_store_name',$ali_store_name);
        update_post_meta($post_id,'ali_store_price_range',$ali_store_price_range);
        update_post_meta($post_id,'ali_currency',$ali_currency);
	}



	/**
	* Adding a Fields for Description - Type of Package
	**/

    function bbloomer_add_custom_field_description_to_variations( $loop, $variation_data, $variation ) {

		woocommerce_wp_text_input( array(

			'id' => 'custom_field_description[' . $loop . ']',

			'class' => 'short',

			'label' => __( 'Description - Type of Package', 'woocommerce' ),

			'value' => get_post_meta( $variation->ID, 'custom_field_description', true )

			)

		);

    }



    function bbloomer_save_custom_field_description_variations( $variation_id, $i ) {

		$custom_field_description = $_POST['custom_field_description'][$i];

		if ( isset( $custom_field_description ) ) update_post_meta( $variation_id, 'custom_field_description', esc_attr( $custom_field_description ) );

    }





    function bbloomer_add_custom_field_description_variation_data( $variations ) {

		$variations['custom_field_description'] = '<div class="woocommerce_custom_field_description">Custom Field: <span>' . get_post_meta( $variations[ 'variation_id' ], 'custom_field_description', true ) . '</span></div>';

		return $variations;

    }



	function woocommerce_product_custom_fields_description () {

	    global $woocommerce, $post;

	    echo '<div class=" product_custom_field_description ">';

	    woocommerce_wp_text_input(

	    	array(

	        'id'          => '_custom_product_text_field_description',

	        'label'       => __( 'Description - Type of Package', 'woocommerce' ),

	        'desc_tip'    => 'true',

			'description' => __( 'Description - Type of Package value included in the supplier email', 'woocommerce' )

	        //'placeholder' => 'Custom Product Text Field',



	      	)

	    );

	    echo '</div>';

	}



    function woocommerce_product_custom_fields_description_save($post_id) {

	    // Custom Product Text Field

	    $woocommerce_custom_product_text_field = $_POST['_custom_product_text_field_description'];

	    if(isset($woocommerce_custom_product_text_field))

	        update_post_meta($post_id, '_custom_product_text_field_description',

	        esc_attr($woocommerce_custom_product_text_field));



    }

}
