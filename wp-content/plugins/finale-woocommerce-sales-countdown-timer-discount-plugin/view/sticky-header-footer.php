<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wcct_style, $post;
$sticky_header     = WCCT_Core()->public->sticky_header;
$sticky_footer     = WCCT_Core()->public->sticky_footer;
$close_btn_display = apply_filters( 'wcct_sticky_close_btn_display', true );

// sticky header
if ( is_array( $sticky_header ) && count( $sticky_header ) > 0 ) {
	foreach ( $sticky_header as $instance_key => $instance_data ) {
		$is_timer_on = true;

		// visibility checking
		if ( ( true === WCCT_Core()->is_mobile ) && ( 'on' === $instance_data['hide_mobile'] ) ) {
			break;
		}
		if ( ( true === WCCT_Core()->is_tablet ) && ( 'on' === $instance_data['hide_tablet'] ) ) {
			break;
		}
		if ( ( true === WCCT_Core()->is_desktop ) && ( 'on' === $instance_data['hide_desktop'] ) ) {
			break;
		}
		if ( 'on' === $instance_data['timer_hide'] ) {
			$is_timer_on = false;
		}

		if ( ! empty( $instance_data['headline'] ) || ! empty( $instance_data['desc'] ) ) {

			$is_button_on      = false;
			$content_class     = ' wcct_col_md_8 wcct_col_sm_7 wcct_col_xs_12';
			$count_timer_class = ' wcct_col_md_4 wcct_col_sm_5 wcct_col_xs_12';
			$btn_wrap_class    = '';
			$btn_class         = 'wcct_default_style';
			if ( isset( $instance_data['button_skins'] ) && '' !== $instance_data['button_skins'] ) {
				$btn_class = $this->wcct_button_skin_class( $instance_data['button_skins'] );
			}

			// check if button enable
			if ( isset( $instance_data['button_enable'] ) && 'on' === $instance_data['button_enable'] && isset( $instance_data['button_text'] ) && '' !== $instance_data['button_text'] ) {
				$is_button_on      = true;
				$content_class     = ' wcct_col_md_5 wcct_col_sm_4 wcct_col_xs_12';
				$count_timer_class = ' wcct_col_md_4 wcct_col_sm_4 wcct_col_xs_12';
				$btn_wrap_class    = ' wcct_col_md_3 wcct_col_sm_4 wcct_col_xs_12';
			}

			// sticky header css
			ob_start();
			echo '.wcct_header_area_' . $instance_key . ' { ' . ( isset( $instance_data['wrap_bg'] ) ? ( 'background-color: ' . $instance_data['wrap_bg'] . '; ' ) : '' ) . ' }';
			echo '.wcct_header_area_' . $instance_key . ' .wcct_left_text .wcct_h3 { ' . ( isset( $instance_data['headline_color'] ) ? ( 'color: ' . $instance_data['headline_color'] . '; ' ) : '' ) . ( isset( $instance_data['headline_font_size'] ) ? 'font-size: ' . $instance_data['headline_font_size'] . 'px;line-height: ' . ( (int) $instance_data['headline_font_size'] + 6 ) . 'px;' : '' ) . ' }';
			echo '.wcct_header_area_' . $instance_key . ' .wcct_left_text p,.wcct_header_area_' . $instance_key . ' .wcct_left_text,.wcct_header_area_' . $instance_key . ' .wcct_left_text div, .wcct_header_area_' . $instance_key . ' .wcct_left_text .wcct_countdown_timer { ' . ( isset( $instance_data['desc_color'] ) ? ( 'color: ' . $instance_data['desc_color'] . '; ' ) : '' ) . ( isset( $instance_data['desc_font_size'] ) ? 'font-size: ' . $instance_data['desc_font_size'] . 'px;line-height: ' . ( (int) $instance_data['desc_font_size'] + 6 ) . 'px;' : '' ) . ' }';

			// below 767 resolution
			echo '@media(max-width: 767px) {';
			echo '.wcct_header_area_' . $instance_key . ' .wcct_left_text .wcct_h3 { ' . ( isset( $instance_data['headline_color'] ) ? ( 'color: ' . $instance_data['headline_color'] . '; ' ) : '' ) . ( isset( $instance_data['headline_font_size'] ) ? 'font-size: ' . ( (int) $instance_data['headline_font_size'] - 2 ) . 'px;line-height: ' . ( (int) $instance_data['headline_font_size'] + 3 ) . 'px;' : '' ) . ' }';
			echo '.wcct_header_area_' . $instance_key . ' .wcct_left_text p,.wcct_header_area_' . $instance_key . ' .wcct_left_text,.wcct_header_area_' . $instance_key . ' .wcct_left_text div, .wcct_header_area_' . $instance_key . ' .wcct_left_text .wcct_countdown_timer { ' . ( isset( $instance_data['desc_color'] ) ? ( 'color: ' . $instance_data['desc_color'] . '; ' ) : '' ) . ( isset( $instance_data['desc_font_size'] ) ? 'font-size: ' . ( (int) $instance_data['desc_font_size'] - 1 ) . 'px;line-height: ' . ( (int) $instance_data['desc_font_size'] + 3 ) . 'px;' : '' ) . ' }';
			echo '}';

			if ( $is_button_on ) {
				if ( 'wcct_default_style' === $btn_class ) {
					echo '.wcct_header_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'background: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
				} elseif ( 'wcct_rounded_button' === $btn_class ) {
					echo '.wcct_header_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'background: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
				} elseif ( 'wcct_ghost_button' === $btn_class ) {
					echo '.wcct_header_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'border-color: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
				} elseif ( 'wcct_shadow_button' === $btn_class ) {
					echo '.wcct_header_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'background: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
					echo '.wcct_header_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'box-shadow: ' . '0 3px 0 1px ' . WCCT_Common::wcct_apply_opacity( $instance_data['button_bg_color'], 0.5 ) . '; ' ) : '' ) . ' }';
				} elseif ( 'wcct_default_style_2' === $btn_class ) {
					echo '.wcct_header_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'background: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
				} elseif ( 'wcct_arrow_button' === $btn_class ) {
					echo '.wcct_header_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' span { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'background: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
					echo '.wcct_header_area_' . $instance_key . ' .wcct_arrow_button .wcct_left_icon:before, .wcct_header_area_' . $instance_key . ' .wcct_arrow_button .wcct_left_icon:after { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'border-color: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ' }';
					echo '.wcct_header_area_' . $instance_key . ' .wcct_arrow_button .wcct_right_icon:before, .wcct_header_area_' . $instance_key . ' .wcct_arrow_button .wcct_right_icon:after { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'border-color: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ' }';
				}
			}
			echo '.wcct_header_area_' . $instance_key . ' .wcct_close { ' . ( isset( $instance_data['headline_color'] ) ? ( 'color: ' . $instance_data['headline_color'] . '; border: 1px solid ' . $instance_data['headline_color'] . '; ' ) : '' ) . ' }';

			$wcct_sticky_head_css = ob_get_clean();
			$wcct_style           .= $wcct_sticky_head_css;
			$out_put_content      = apply_filters( 'wcct_header_display_content_before_data', '', $instance_data, $instance_key );

			if ( $out_put_content !== '' ) {
				echo apply_filters( 'wcct_modify_sticky_header_content', $out_put_content, $instance_data );
				break;
			}

			ob_start();
			?>
            <div class="wcct_header_area wcct_header_area_<?php echo $instance_key; ?>"
                 data-id="<?php echo $instance_key; ?>" data-delay="<?php echo $instance_data['delay']; ?>">
                <div class="wcct_container">
                    <div class="wcct_row">
                        <div class="wcct_innerDiv">
                            <div class="wcct_table">
                                <div class="wcct_table_cell wcct_left_text <?php echo $content_class; ?>">
                                    <div class="wcct_content_Div">
										<?php
										$headline_align = 'wcct_text_left';
										if ( ! empty( $instance_data['headline_align'] ) ) {
											$headline_align = 'wcct_text_' . $instance_data['headline_align'];
										}
										$desc_align = 'wcct_text_left';
										if ( ! empty( $instance_data['desc_align'] ) ) {
											$desc_align = 'wcct_text_' . $instance_data['desc_align'];
										}
										echo isset( $instance_data['headline'] ) ? '<div class="wcct_h3 ' . $headline_align . '">' . do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( $instance_data['headline'] ) ) . '</div>' : '';
										if ( ( true == WCCT_Core()->is_mobile ) && ( 'on' == $instance_data['desc_hide_mobile'] ) ) {
											// do nothing
										} elseif ( isset( $instance_data['desc'] ) && ( '' != $instance_data['desc'] ) ) {
											echo '<div class="' . $desc_align . '">';
											if ( strpos( $instance_data['desc'], '{{countdown_timer}}' ) !== false ) {
												$timer_data            = array(
													'label_color'     => '#dd3333',
													'label_font'      => '13',
													'end_timestamp'   => $instance_data['end_timestamp'],
													'start_timestamp' => $instance_data['start_timestamp'],
													'full_instance'   => $instance_data,

												);
												$get_timer             = WCCT_Core()->appearance->wcct_maybe_parse_timer( $instance_key, $timer_data, 'sticky_header' );
												$instance_data['desc'] = str_replace( '{{countdown_timer}}', $get_timer, $instance_data['desc'] );
											}

											$instance_data['desc'] = $this->wcct_maybe_decode_campaign_time_merge_tags( $instance_data['desc'], $instance_data );

											echo $this->wcct_content_without_p( $instance_data['desc'] );
											echo '</div>';
										}
										?>
                                    </div>
                                </div>
								<?php if ( true === $is_timer_on ) { ?>
                                    <div class="wcct_table_cell wcct_middle_countdown wcct_text_<?php echo( isset( $instance_data['timer_position'] ) ? $instance_data['timer_position'] : 'right' ); ?> <?php echo $count_timer_class; ?>">
										<?php echo $this->wcct_trigger_countdown_timer( $instance_key, $instance_data, 'sticky_header' ); ?>
                                    </div>
								<?php } ?>
								<?php if ( true === $is_button_on ) { ?>
                                    <div class="wcct_table_cell wcct_right_button wcct_text_right <?php echo $btn_wrap_class; ?>" data-btn-skin="<?php echo isset( $instance_data['button_skins'] ) ? $instance_data['button_skins'] : ''; ?>">
                                        <div class="wcct_button_area">
                                            <a href="<?php echo ( isset( $instance_data['button_url'] ) && $instance_data['button_url'] != '' ) ? $instance_data['button_url'] : 'javascript:void(0)'; ?>"
                                               class="<?php echo $btn_class; ?>" data-id="<?php echo $instance_key; ?>">
												<span class="wcct_sticky_btn_span">
													<?php
													if ( $btn_class == 'wcct_arrow_button' ) {
														echo '<i class="wcct_left_icon"></i><span class="wcct_button_text">' . $instance_data['button_text'] . '</span><i class="wcct_right_icon"></i>';
													} else {

														echo $instance_data['button_text'];
													}
													?>
												</span>
                                            </a>
                                        </div>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php if ( $close_btn_display ) { ?>
                    <div class="wcct_close" data-ref="header" data-expire="<?php echo $instance_data['expire_time']; ?>">x</div>
				<?php } ?>
            </div>
			<?php
			$sticky_headeer_html = ob_get_clean();
			echo apply_filters( 'wcct_modify_sticky_header_content', $sticky_headeer_html, $instance_data );

			break;
		}
	}
}

// sticky footer
if ( is_array( $sticky_footer ) && count( $sticky_footer ) > 0 ) {
	foreach ( $sticky_footer as $instance_key => $instance_data ) {
		$is_timer_on = true;

		// visibility checking
		if ( ( true === WCCT_Core()->is_mobile ) && ( 'on' === $instance_data['hide_mobile'] ) ) {
			break;
		}
		if ( ( true === WCCT_Core()->is_tablet ) && ( 'on' === $instance_data['hide_tablet'] ) ) {
			break;
		}
		if ( ( true === WCCT_Core()->is_desktop ) && ( 'on' === $instance_data['hide_desktop'] ) ) {
			break;
		}
		if ( 'on' === $instance_data['timer_hide'] ) {
			$is_timer_on = false;
		}

		if ( ! empty( $instance_data['headline'] ) || ! empty( $instance_data['desc'] ) ) {


			$is_button_on      = false;
			$content_class     = ' wcct_col_md_8 wcct_col_sm_7';
			$count_timer_class = ' wcct_col_md_4 wcct_col_sm_5';
			$btn_class         = 'wcct_default_style';
			if ( isset( $instance_data['button_skins'] ) && '' !== $instance_data['button_skins'] ) {
				$btn_class = $this->wcct_button_skin_class( $instance_data['button_skins'] );
			}
			$btn_wrap_class = '';
			// check if button enable
			if ( isset( $instance_data['button_enable'] ) && 'on' === $instance_data['button_enable'] && isset( $instance_data['button_text'] ) && '' !== $instance_data['button_text'] ) {
				$is_button_on      = true;
				$content_class     = ' wcct_col_md_5 wcct_col_sm_4';
				$count_timer_class = ' wcct_col_md_4 wcct_col_sm_4';
				$btn_wrap_class    = ' wcct_col_md_3 wcct_col_sm_4';
			}

			$out_put_content = apply_filters( 'wcct_footer_display_content_before_data', '', $instance_data, $instance_key );

			if ( $out_put_content !== '' ) {
				echo apply_filters( 'wcct_modify_sticky_footer_content', $out_put_content, $instance_data );
				echo $out_put_content;
				break;
			}

			// sticky footer css
			ob_start();
			echo '.wcct_footer_area_' . $instance_key . ' { ' . ( isset( $instance_data['wrap_bg'] ) ? ( 'background-color: ' . $instance_data['wrap_bg'] . '; ' ) : '' ) . ' }';
			echo '.wcct_footer_area_' . $instance_key . ' .wcct_left_text .wcct_h3 { ' . ( isset( $instance_data['headline_color'] ) ? ( 'color: ' . $instance_data['headline_color'] . '; ' ) : '' ) . ( isset( $instance_data['headline_font_size'] ) ? 'font-size: ' . $instance_data['headline_font_size'] . 'px;line-height: ' . ( (int) $instance_data['headline_font_size'] + 6 ) . 'px;' : '' ) . ' }';
			echo '.wcct_footer_area_' . $instance_key . ' .wcct_left_text p,.wcct_footer_area_' . $instance_key . ' .wcct_left_text,.wcct_footer_area_' . $instance_key . ' .wcct_left_text div, .wcct_footer_area_' . $instance_key . ' .wcct_left_text .wcct_countdown_timer { ' . ( isset( $instance_data['desc_color'] ) ? ( 'color: ' . $instance_data['desc_color'] . '; ' ) : '' ) . ( isset( $instance_data['desc_font_size'] ) ? 'font-size: ' . $instance_data['desc_font_size'] . 'px;line-height: ' . ( (int) $instance_data['desc_font_size'] + 6 ) . 'px;' : '' ) . ' }';

			// below 767 resolution
			echo '@media(max-width: 767px) {';
			echo '.wcct_footer_area_' . $instance_key . ' .wcct_left_text .wcct_h3 { ' . ( isset( $instance_data['headline_color'] ) ? ( 'color: ' . $instance_data['headline_color'] . '; ' ) : '' ) . ( isset( $instance_data['headline_font_size'] ) ? 'font-size: ' . ( (int) $instance_data['headline_font_size'] - 2 ) . 'px;line-height: ' . ( (int) $instance_data['headline_font_size'] + 3 ) . 'px;' : '' ) . ' }';
			echo '.wcct_footer_area_' . $instance_key . ' .wcct_left_text p,.wcct_footer_area_' . $instance_key . ' .wcct_left_text,.wcct_footer_area_' . $instance_key . ' .wcct_left_text div, .wcct_footer_area_' . $instance_key . ' .wcct_left_text .wcct_countdown_timer { ' . ( isset( $instance_data['desc_color'] ) ? ( 'color: ' . $instance_data['desc_color'] . '; ' ) : '' ) . ( isset( $instance_data['desc_font_size'] ) ? 'font-size: ' . ( (int) $instance_data['desc_font_size'] - 1 ) . 'px;line-height: ' . ( (int) $instance_data['desc_font_size'] + 3 ) . 'px;' : '' ) . ' }';
			echo '}';

			if ( $is_button_on ) {
				if ( 'wcct_default_style' === $btn_class ) {
					echo '.wcct_footer_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'background: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
				} elseif ( 'wcct_rounded_button' === $btn_class ) {
					echo '.wcct_footer_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'background: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
				} elseif ( 'wcct_ghost_button' === $btn_class ) {
					echo '.wcct_footer_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'border-color: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
				} elseif ( 'wcct_shadow_button' === $btn_class ) {
					echo '.wcct_footer_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'background: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
					echo '.wcct_footer_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'box-shadow: ' . '0 3px 0 1px ' . WCCT_Common::wcct_apply_opacity( $instance_data['button_bg_color'], 0.5 ) . '; ' ) : '' ) . ' }';
				} elseif ( 'wcct_default_style_2' === $btn_class ) {
					echo '.wcct_footer_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'background: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
				} elseif ( 'wcct_arrow_button' === $btn_class ) {
					echo '.wcct_footer_area_' . $instance_key . ' .wcct_button_area a.' . $btn_class . ' span { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'background: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ( isset( $instance_data['button_text_color'] ) ? 'color: ' . $instance_data['button_text_color'] . '; ' : '' ) . ' }';
					echo '.wcct_footer_area_' . $instance_key . ' .wcct_arrow_button .wcct_left_icon:before,.wcct_footer_area_' . $instance_key . ' .wcct_arrow_button .wcct_left_icon:after { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'border-color: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ' }';
					echo '.wcct_footer_area_' . $instance_key . ' .wcct_arrow_button .wcct_right_icon:before,.wcct_footer_area_' . $instance_key . ' .wcct_arrow_button .wcct_right_icon:after { ' . ( isset( $instance_data['button_bg_color'] ) ? ( 'border-color: ' . $instance_data['button_bg_color'] . '; ' ) : '' ) . ' }';
				}
			}
			echo '.wcct_footer_area_' . $instance_key . ' .wcct_close { ' . ( isset( $instance_data['headline_color'] ) ? ( 'color: ' . $instance_data['headline_color'] . '; border: 1px solid ' . $instance_data['headline_color'] . '; ' ) : '' ) . ' }';

			$wcct_sticky_head_css = ob_get_clean();
			$wcct_style           .= $wcct_sticky_head_css;

			ob_start();
			?>
            <div class="wcct_footer_area wcct_footer_area_<?php echo $instance_key; ?>" data-id="<?php echo $instance_key; ?>" data-delay="<?php echo $instance_data['delay']; ?>">
                <div class="wcct_container">
                    <div class="wcct_row">
                        <div class="wcct_innerDiv">
                            <div class="wcct_table">
                                <div class="wcct_table_cell wcct_left_text <?php echo $content_class; ?>">
                                    <div class="wcct_content_Div">
										<?php
										$headline_align = 'wcct_text_left';
										if ( ! empty( $instance_data['headline_align'] ) ) {
											$headline_align = 'wcct_text_' . $instance_data['headline_align'];
										}
										$desc_align = 'wcct_text_left';
										if ( ! empty( $instance_data['desc_align'] ) ) {
											$desc_align = 'wcct_text_' . $instance_data['desc_align'];
										}
										echo isset( $instance_data['headline'] ) ? '<div class="wcct_h3 ' . $headline_align . '">' . do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( $instance_data['headline'] ) ) . '</div>' : '';
										if ( ( true == WCCT_Core()->is_mobile ) && ( 'on' == $instance_data['desc_hide_mobile'] ) ) {
											// do nothing
										} elseif ( isset( $instance_data['desc'] ) && $instance_data['desc'] != '' ) {
											echo '<div class="' . $desc_align . '">';
											if ( strpos( $instance_data['desc'], '{{countdown_timer}}' ) !== false ) {
												$timer_data            = array(
													'label_color'     => '#dd3333',
													'label_font'      => '13',
													'end_timestamp'   => $instance_data['end_timestamp'],
													'start_timestamp' => $instance_data['start_timestamp'],
													'full_instance'   => $instance_data,
												);
												$get_timer             = WCCT_Core()->appearance->wcct_maybe_parse_timer( $instance_key, $timer_data, 'sticky_footer' );
												$instance_data['desc'] = str_replace( '{{countdown_timer}}', $get_timer, $instance_data['desc'] );
											}
											$instance_data['desc'] = $this->wcct_maybe_decode_campaign_time_merge_tags( $instance_data['desc'], $instance_data );
											echo $this->wcct_content_without_p( $instance_data['desc'] );
											echo '</div>';
										}
										?>
                                    </div>
                                </div>
								<?php if ( true === $is_timer_on ) { ?>
                                    <div class="wcct_table_cell wcct_middle_countdown wcct_text_<?php echo( isset( $instance_data['timer_position'] ) ? $instance_data['timer_position'] : 'right' ); ?> <?php echo $count_timer_class; ?>">
										<?php echo $this->wcct_trigger_countdown_timer( $instance_key, $instance_data, 'sticky_footer' ); ?>
                                    </div>
								<?php } ?>
								<?php if ( $is_button_on ) { ?>
                                    <div class="wcct_table_cell wcct_right_button wcct_text_right <?php echo $btn_wrap_class; ?>" data-btn-skin="<?php echo isset( $instance_data['button_skins'] ) ? $instance_data['button_skins'] : ''; ?>">
                                        <div class="wcct_button_area">
                                            <a href="<?php echo ( isset( $instance_data['button_url'] ) && $instance_data['button_url'] != '' ) ? $instance_data['button_url'] : 'javascript:void(0)'; ?>"
                                               class="<?php echo $btn_class; ?>" data-id="<?php echo $instance_key; ?>">
												<?php
												if ( $btn_class == 'wcct_arrow_button' ) {
													echo '<i class="wcct_left_icon"></i><span class="wcct_button_text">' . $instance_data['button_text'] . '</span><i class="wcct_right_icon"></i>';
												} else {
													echo $instance_data['button_text'];
												}
												?>
                                            </a>
                                        </div>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php if ( $close_btn_display ) { ?>
                    <div class="wcct_close" data-ref="footer" data-expire="<?php echo $instance_data['expire_time']; ?>">x</div>
				<?php } ?>
            </div>
			<?php
			$sticky_footer_html = ob_get_clean();
			echo apply_filters( 'wcct_modify_sticky_footer_content', $sticky_footer_html, $instance_data );

			break;
		}
	}
}
