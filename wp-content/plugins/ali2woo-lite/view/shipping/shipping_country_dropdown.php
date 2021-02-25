<?php
$a2wl_shipping_html = '<div id="a2wl_to_country">' .
	woocommerce_form_field('a2wl_to_country_field', array(
		   'type'       => 'select',
		   'class'      => array( 'chzn-drop' ),
		   'label'      => __('Ship my order(s) to: ', 'ali2woo-lite'),
		   'placeholder'    => __('Select a Country', 'ali2woo-lite'),
		   'options'    => $countries,
		   'default' => $default_country,
		   'return' => true
			)
	 ) .
'</div>';
$a2wl_shipping_html = str_replace(array("\r", "\n"), '', $a2wl_shipping_html);
?>
<div class="a2wl_shipping">
</div>
<script id="a2wl_country_selector_html" type="text/html">
<?php echo $a2wl_shipping_html; ?>
</script>
<script>
jQuery(document).ready(function($){
  $( "body" ).on('a2wl_shipping_js_loaded', function(e, a2wl_shipping_api){
    a2wl_shipping_api.init_in_cart( $('#a2wl_country_selector_html').html());      
  })   

});
</script>
