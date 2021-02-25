<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$table = new BWFAN_Abandoned_Cart_Table();

$table->search_box( 'Search' );

echo '<form action="" method="get">';

$table->process_bulk_action();
$table->data = $table->get_abandoned_cart_table_data();
$table->prepare_items();
$table->display();

echo '<input type="hidden" name="page" value="autonami"/>';
echo '<input type="hidden" name="tab" value="carts"/>';
echo '<input type="hidden" name="ab_section" value="recoverable"/>';
echo '</form>';

include_once __DIR__ . '/captured-modal.php';
