<?php
defined( 'ABSPATH' ) || exit;

global $post;

// vars
$groups = get_post_meta( $post->ID, 'xlwcty_rule', true );


// at lease 1 location rule
if ( empty( $groups ) ) {
	$default_rule_id = 'rule' . uniqid();
	$groups          = array(
		'group0' => array(
			$default_rule_id => array(
				'rule_type' => 'general_always',
				'operator'  => '==',
				'condition' => '',
			),
		),
	);
}
?>

<div class="xlwcty-rules-builder woocommerce_options_panel">

    <div class="label">
        <h4><?php _e( 'Rules', 'thank-you-page-for-woocommerce-nextmove' ); ?></h4>
        <p class="description"><?php _e( 'Create a set of rules to determine when this Thank You Page  will be displayed.', 'thank-you-page-for-woocommerce-nextmove' ); ?></p>
    </div>

    <div id="xlwcty-rules-groups">
        <div class="xlwcty-rule-group-target">
			<?php if ( is_array( $groups ) ) : ?>
			<?php
			$group_counter = 0;
			foreach ( $groups as $group_id => $group ) :
				if ( empty( $group_id ) ) {
					$group_id = 'group' . $group_id;
				}
				?>

                <div class="xlwcty-rule-group-container" data-groupid="<?php echo $group_id; ?>">
                    <div class="xlwcty-rule-group-header">
						<?php if ( $group_counter == 0 ) : ?>
                            <h4><?php _e( 'Open this page when these conditions are matched:', 'thank-you-page-for-woocommerce-nextmove' ); ?></h4>
						<?php else : ?>
                            <h4><?php _e( 'or', 'thank-you-page-for-woocommerce-nextmove' ); ?></h4>
						<?php endif; ?>
                        <a href="#" class="xlwcty-remove-rule-group button"></a>
                    </div>
					<?php if ( is_array( $group ) ) : ?>
                        <table class="xlwcty-rules" data-groupid="<?php echo $group_id; ?>">
                            <tbody>
							<?php
							foreach ( $group as $rule_id => $rule ) :
								if ( empty( $rule_id ) ) {
									$rule_id = 'rule' . $rule_id;
								}
								?>
                                <tr data-ruleid="<?php echo $rule_id; ?>" class="xlwcty-rule">
                                    <td class="rule-type">
										<?php
										// allow custom location rules
										$types = apply_filters( 'xlwcty_xlwcty_rule_get_rule_types', array() );

										// create field
										$args = array(
											'input'   => 'select',
											'name'    => 'xlwcty_rule[' . $group_id . '][' . $rule_id . '][rule_type]',
											'class'   => 'rule_type',
											'choices' => $types,
										);
										xlwcty_Input_Builder::create_input_field( $args, $rule['rule_type'] );
										?>
                                    </td>

									<?php
									XLWCTY_Common::ajax_render_rule_choice( array(
										'group_id'  => $group_id,
										'rule_id'   => $rule_id,
										'rule_type' => $rule['rule_type'],
										'condition' => isset( $rule['condition'] ) ? $rule['condition'] : false,
										'operator'  => $rule['operator'],
									) );
									?>
                                    <td class="loading" colspan="2" style="display:none;"><?php _e( 'Loading...', 'thank-you-page-for-woocommerce-nextmove' ); ?></td>
                                    <td class="add">
                                        <a href="#" class="xlwcty-add-rule button"><?php _e( 'AND', 'thank-you-page-for-woocommerce-nextmove' ); ?></a>
                                    </td>
                                    <td class="remove">
                                        <a href="#" class="xlwcty-remove-rule xlwcty-button-remove" title="<?php _e( 'Remove condition', 'thank-you-page-for-woocommerce-nextmove' ); ?>"></a>
                                    </td>
                                </tr>
							<?php endforeach; ?>
                            </tbody>
                        </table>
					<?php endif; ?>
                </div>
				<?php $group_counter ++; ?>
			<?php endforeach; ?>
        </div>

        <h4 class="rules_or" style="<?php echo( $group_counter > 1 ? 'display:block;' : 'display:none' ); ?>"><?php _e( 'or when these conditions are matched', 'thank-you-page-for-woocommerce-nextmove' ); ?></h4>
        <button class="button button-primary xlwcty-add-rule-group" title="<?php _e( 'Add a set of conditions', 'thank-you-page-for-woocommerce-nextmove' ); ?>"><?php _e( 'OR', 'thank-you-page-for-woocommerce-nextmove' ); ?></button>
		<?php endif; ?>
    </div>
</div>

<script type="text/template" id="xlwcty-rule-template">
	<?php include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'admin/views/metabox-rules-rule-template.php'; ?>
</script>
