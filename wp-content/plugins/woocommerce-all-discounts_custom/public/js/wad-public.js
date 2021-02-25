(function ($) {
    'use strict';

    $(document).ready(function ()
    {
        $("[data-tooltip-title]").tooltip();
    	$('body').on('change', '#billing_country, #shipping_country, #shipping_state, #billing_state', function() {
            setTimeout(function(){ 
                $('body').trigger('update_checkout'); 
            }, 2000);
        });
        //trigger twice the cart when the payment gateway is change. because when the customer is logged in the metas are not updated the first time
        $('body').on('change', 'input[name="payment_method"], input[name*="shipping_method"]', function(){
            $('body').trigger('update_checkout');
            setTimeout(function(){
            	$('body').trigger('update_checkout');
            }, 2000);
        });
        
        $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
            // Fired when the user selects all the required dropdowns / attributes
            // and a final variation is selected / shown
            var variation_id = $("input[name='variation_id']").val();
            if (variation_id)
            {
                $(".wad-qty-pricing-table").hide();
                $(".wad-qty-pricing-table[data-id='"+variation_id+"']").show();
            }
        } );
        
        $('body').on('click', '.wad-add-gift-to-cart',function (){
            $(".wad-add-gift-to-cart").css( 'pointer-events', 'none' );
        } );
        
    });

})(jQuery);
