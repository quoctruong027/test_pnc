<?php
	$value = ! empty( $field['default_value'] ) ? $field['default_value'] : '';
?>
<input type="url" <?php echo $this->get_render_attribute_string( $field_key ); ?> value="<?php echo $value; ?>" placeholder="<?php echo $field['placeholder']; ?>" />