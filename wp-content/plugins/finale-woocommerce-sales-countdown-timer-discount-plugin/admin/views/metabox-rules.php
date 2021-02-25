<?php
global $post;

// vars
$groups = get_post_meta( $post->ID, 'wcct_rule', true );


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

<div class="wcct-rules-builder woocommerce_options_panel">

    <div class="label">
        <h4><?php _e( 'Rules', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></h4>
        <p class="description"><?php _e( 'Create a set of rules to determine when the campaign defined above will be displayed.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></p>
        <br/>
        <p class="description"><?php _e( "<i class='dashicons dashicons-editor-help'></i> Need Help with setting up Rules? <a href='https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/rules/?utm_source=finale-pro&amp;utm_campaign=doc&amp;utm_medium=text-click&amp;utm_term=rules' target='_blank'>Watch Video or Read Docs</a>", 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></p>
    </div>

    <div id="wcct-rules-groups">
        <div class="wcct-rule-group-target">
			<?php if ( is_array( $groups ) ) : ?>
			<?php
			$group_counter = 0;
			foreach ( $groups as $group_id => $group ) :
				if ( empty( $group_id ) ) {
					$group_id = 'group' . $group_id;
				}
				?>

                <div class="wcct-rule-group-container" data-groupid="<?php echo $group_id; ?>">
                    <div class="wcct-rule-group-header">
						<?php if ( $group_counter == 0 ) : ?>
                            <h4><?php _e( 'Apply this Campaign when these conditions are matched:', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></h4>
						<?php else : ?>
                            <h4><?php _e( 'or', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></h4>
						<?php endif; ?>
                        <a href="#" class="wcct-remove-rule-group button"></a>
                    </div>
					<?php if ( is_array( $group ) ) : ?>
                        <table class="wcct-rules" data-groupid="<?php echo $group_id; ?>">
                            <tbody>
							<?php
							foreach ( $group as $rule_id => $rule ) :
								if ( empty( $rule_id ) ) {
									$rule_id = 'rule' . $rule_id;
								}
								?>
                                <tr data-ruleid="<?php echo $rule_id; ?>" class="wcct-rule">
                                    <td class="rule-type">
										<?php
										// allow custom location rules
										$types = apply_filters( 'wcct_wcct_rule_get_rule_types', array() );

										// create field
										$args = array(
											'input'   => 'select',
											'name'    => 'wcct_rule[' . $group_id . '][' . $rule_id . '][rule_type]',
											'class'   => 'rule_type',
											'choices' => $types,
										);
										WCCT_Input_Builder::create_input_field( $args, $rule['rule_type'] );
										?>
                                    </td>
									<?php
									WCCT_Common::ajax_render_rule_choice( array(
										'group_id'  => $group_id,
										'rule_id'   => $rule_id,
										'rule_type' => $rule['rule_type'],
										'condition' => isset( $rule['condition'] ) ? $rule['condition'] : false,
										'operator'  => isset( $rule['operator'] ) ? $rule['operator'] : '==',
									) );
									?>
                                    <td class="loading" colspan="2" style="display:none;"><?php _e( 'Loading...', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></td>
                                    <td class="add">
                                        <a href="#" class="wcct-add-rule button"><?php _e( 'AND', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></a>
                                    </td>
                                    <td class="remove">
                                        <a href="#" class="wcct-remove-rule wcct-button-remove" title="<?php _e( 'Remove condition', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?>"></a>
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
        <h4 class="rules_or" style="<?php echo( $group_counter > 1 ? 'display:block;' : 'display:none' ); ?>"><?php _e( 'or when these conditions are matched', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></h4>
        <button class="button button-primary wcct-add-rule-group" title="<?php _e( 'Add a set of conditions', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?>"><?php _e( 'OR', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></button>
		<?php endif; ?>
    </div>
</div>

<script type="text/template" id="wcct-rule-template">
	<?php include plugin_dir_path( WCCT_PLUGIN_FILE ) . 'admin/views/metabox-rules-rule-template.php'; ?>
</script>
