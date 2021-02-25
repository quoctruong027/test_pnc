<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$table = new BWFAN_Recovered_Cart_Table();
?>
<form action="" method="get">
	<?php
	$table->search_box( 'Search' );
	$table->data = $table->get_recovered_cart_table_data();
	$table->prepare_items();
	$table->display();
	?>
    <input type="hidden" name="page" value="autonami"/>
    <input type="hidden" name="tab" value="carts"/>
    <input type="hidden" name="ab_section" value="recovered"/>
</form>

<?php

$table->order_preview_template();
include_once __DIR__ . '/recovered-modal.php';
?>
