<?php

if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
	$order = wc_get_orders( array(
		'numberposts' => 1,
	) );
	if ( is_array( $order ) && count( $order ) > 0 ) {
		$this->order = $order[0];
	}
}

wc_get_template( 'emails/email-order-details.php', array(
	'order'         => $this->order,
	'sent_to_admin' => false,
	'plain_text'    => false,
	'email'         => '',
) );
?>
    <style>
        #template_header {
            width: 100% !important;
        }

        table img {
            max-width: 75px;
        }
    </style>
<?php
