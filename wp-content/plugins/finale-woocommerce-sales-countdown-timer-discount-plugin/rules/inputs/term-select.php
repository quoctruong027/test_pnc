<?php

class WCCT_Input_Term_Select extends WCCT_Input_Text {


	public function __construct() {
		// vars
		$this->type = 'Term_Select';

		$this->defaults = array(
			'multiple'      => 0,
			'allow_null'    => 0,
			'choices'       => array(),
			'default_value' => '',
			'class'         => 'chosen_select',
		);
	}

	public function render( $field, $value = null ) {

		$field = array_merge( $this->defaults, $field );
		if ( ! isset( $field['id'] ) ) {
			$field['id'] = sanitize_title( $field['id'] );
		}

		$objects_tax = get_taxonomies( '', 'objects' );
		$ignore      = array( 'nav_menu', 'link_category' );
		foreach ( $objects_tax as $post ) {
			if ( in_array( $post->name, $ignore ) ) {
				continue;
			}
			$terms = get_terms( array(
				'taxonomy' => $post->name,
			) );

			foreach ( $terms as $term ) {
				foreach ( $terms as $term ) {

					$k                       = "{$post->name}:{$term->slug}";
					$r[ $post->label ][ $k ] = $term->name;

				}
			}
		}

		$placeholder = ( isset( $field['placeholder'] ) ? $field['placeholder'] : __( 'Search...', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );

		echo '<select multiple="multiple" id="' . $field['id'] . '" class="chosen_select ' . $field['class'] . '" name="' . $field['name'] . '[]" data-placeholder="' . $placeholder . '" >';

		foreach ( $r as $optTitle => $select_data ) {
			?>
            <optgroup label="<?php echo $optTitle; ?>">
				<?php
				foreach ( $select_data as $key => $title ) {

					$selected = '';
					if ( in_array( $key, $value ) ) {
						$selected = "selected='selected'";
					}
					?>
                    <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $title; ?></option>
					<?php
				}
				?>
            </optgroup>
			<?php
		}

	}

}

?>
