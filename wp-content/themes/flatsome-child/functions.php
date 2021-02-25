<?php
// Add custom Theme Functions here

function add_column_to_importer( $options ) {

  // column slug => column name
  $options['slug'] = 'Slug';

  return $options;
}
add_filter('woocommerce_csv_product_import_mapping_options', 'add_column_to_importer');


function add_column_to_mapping_screen( $columns ) {
  
  // potential column name => CSV column slug
  $columns['Slug'] = 'slug';

  return $columns;
}
add_filter('woocommerce_csv_product_import_mapping_default_columns', 'add_column_to_mapping_screen');

function process_import( $object, $data ) {
  
  if (!empty($data['slug'])) {
    $object->set_slug($data['slug']);
  }

  return $object;
}
add_filter('woocommerce_product_import_pre_insert_product_object', 'process_import', 10, 2);

