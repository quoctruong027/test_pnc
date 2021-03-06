<?php
if ( ! defined( 'WFACP_TEMPLATE_DIR' ) ) {
	return '';
}
/**
 * @var $this WFACP_Template_Common
 */

//$this->pre($data);

if ( ( is_array( $data ) && count( $data ) <= 0 ) || is_null( $data ) ) {
	return;
}

$rbox_border_type = '';
if ( isset( $data['advance_setting']['rbox_border_type'] ) && $data['advance_setting']['rbox_border_type'] != '' ) {
	$rbox_border_type = $data['advance_setting']['rbox_border_type'];
}

$supporter_name            = $data['customer_support']['supporter_name'];
$supporter_designation     = $data['customer_support']['supporter_designation'];
$supporter_image           = $data['customer_support']['supporter_image'];
$supporter_signature_image = $data['customer_support']['supporter_signature_image'];

$wfacp_display = '';


if ( $supporter_name == '' && $supporter_designation == '' && $supporter_image == '' && $supporter_signature_image == '' ) {
	$wfacp_display = 'wfacp_display_none';
}
?>

<div class="<?php echo $section_key . ' ' . $rbox_border_type; ?> div_wrap_sec customer_support_wrap" data-scrollto="<?php echo $section_key; ?>_section">
    <!--   Customer Support -->
    <div class="wfacp-customber-view clearfix">
		<?php
		if ( isset( $data['heading_section']['heading'] ) && $data['heading_section']['heading'] != '' && isset( $data['heading_section']['enable_heading'] ) && $data['heading_section']['enable_heading'] == 1 ) {
			$align_text         = $data['heading_section']['heading_talign'];
			$font_weight        = $data['heading_section']['heading_font_weight'];
			$heading_fs_desktop = $data['heading_section']['heading_fs']['desktop'];
			$heading_fs_tablet  = $data['heading_section']['heading_fs']['tablet'];
			$heading_fs_mobile  = $data['heading_section']['heading_fs']['mobile'];

			?>
            <h2 class="wfacp-list-title wfacp_section_title <?php echo $align_text . ' ' . $font_weight; ?>">
				<?php echo $data['heading_section']['heading']; ?>
            </h2>


			<?php
		}
		?>

		<?php
		if ( isset( $data['sub_heading_section']['enable_heading'] ) && isset( $data['sub_heading_section']['heading'] ) && $data['sub_heading_section']['heading'] != '' && $data['sub_heading_section']['enable_heading'] == 1 ) {

			$align_text         = $data['sub_heading_section']['heading_talign'];
			$font_weight        = $data['sub_heading_section']['heading_font_weight'];
			$heading_fs_desktop = $data['sub_heading_section']['heading_fs']['desktop'];
			$heading_fs_tablet  = $data['sub_heading_section']['heading_fs']['tablet'];
			$heading_fs_mobile  = $data['sub_heading_section']['heading_fs']['mobile'];

			?>

            <h6 class="wfacp-subtitle wfacp-subtitle <?php echo $align_text . ' ' . $font_weight; ?>">
				<?php echo $data['sub_heading_section']['heading']; ?>
            </h6>

			<?php
		}
		?>


        <div class="wfacp-support-profile wfacp-customer-support-profile-wrap <?php echo $wfacp_display; ?> clearfix">

			<?php

			if ( isset( $data['customer_support']['supporter_image'] ) && $data['customer_support']['supporter_image'] != '' ) {
				?>
                <div class="wfacp-support-img">
                    <img src="<?php echo $data['customer_support']['supporter_image']; ?>" alt="">
                </div>
				<?php
			}


			?>


            <div class="wfacp-support-desc">
                <h6 class="wfacp-title-name wfacp-title-name wfacp-customer-support-title <?php echo $wfacp_display; ?>">
					<?php echo $data['customer_support']['supporter_name']; ?></h6>
                <span class="wfacp-customber-sub-tit wfacp-customer-support-desc <?php echo $wfacp_display; ?>"><?php echo $data['customer_support']['supporter_designation']; ?></span>
				<?php
				if ( isset( $data['customer_support']['supporter_signature_image'] ) && $data['customer_support']['supporter_signature_image'] != '' ) {
					?>
                    <img src="<?php echo $data['customer_support']['supporter_signature_image']; ?>" class="wfacp_sign_support" alt="">
					<?php
				}
				?>
            </div>
        </div>


        <div class="wfacp-support_col-right">
            <ul class="wfacp-support-details wfacp-support-details-wrap">

				<?php


				$contact_heading = $contact_description = $contact_chat = $contact_timing = '';

				$contact_heading     = $data['customer_support']['contact_heading'];
				$contact_description = $data['customer_support']['contact_description'];

				$contact_chat   = $data['customer_support']['contact_chat'];
				$contact_timing = $data['customer_support']['contact_timing'];

				$none_class_name = '';


				$none_class_name1 = '';
				$none_class_name2 = '';

				if ( $contact_heading == '' && $contact_description == '' ) {

					$none_class_name1 = 'wfacp_display_none';
				}

				if ( $contact_chat == '' && $contact_timing == '' ) {
					$none_class_name2 = 'wfacp_display_none';
				}


				?>

                <li class="wfacp-email <?php echo $none_class_name1; ?>">
					<?php


					echo "<span class='wfacp-contact-head'>" . $data['customer_support']['contact_heading'] . '</span>';


					echo "<div class='wfacp_email_description_wrap wfacp_contact_support_wrap'>";

					$contact_des = apply_filters( 'wfacp_the_content', $data['customer_support']['contact_description'] );
					echo '<p>' . $contact_des . '</p>';
					echo '</div>';

					?>

                </li>

                <li class="wfacp-chat <?php echo $none_class_name2; ?>">
					<?php
					echo "<span class='wfacp-contact-head'>" . $contact_chat . '</span>';

					echo "<div class='wfacp_chat_description_wrap wfacp_contact_support_wrap'>";
					$contact_timing = apply_filters( 'wfacp_the_content', $contact_timing );
					echo '<p>' . $contact_timing . '</p>';
					echo '</div>';
					?>
                </li>
            </ul>
        </div>


    </div>
</div>


<!--   Customer Support Closed -->

