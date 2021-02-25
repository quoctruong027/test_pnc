<?php
/**
 * @var $booking object
 */
?>
<div class="bookings-details">
    <table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
        <tbody>
        <tr>
            <th scope="row"
                style="text-align:left; border: 1px solid #eee;"><?php esc_html_e('Booked Product', 'woocommerce-bookings'); ?></th>
            <td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html($booking->get_product()->get_title()); ?></td>
        </tr>
        <tr>
            <th style="text-align:left; border: 1px solid #eee;"
                scope="row"><?php esc_html_e('Booking ID', 'woocommerce-bookings'); ?></th>
            <td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html($booking->get_id()); ?></td>
        </tr>
        <?php
        $resource = $booking->get_resource();
        $resource_label = $booking->get_product()->get_resource_label();
        if ($booking->has_resources() && $resource) :
            ?>
            <tr>
                <th style="text-align:left; border: 1px solid #eee;"
                    scope="row"><?php echo ('' !== $resource_label) ? esc_html($resource_label) : esc_html__('Booking Type', 'woocommerce-bookings'); ?></th>
                <td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html($resource->post_title); ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <th style="text-align:left; border: 1px solid #eee;"
                scope="row"><?php esc_html_e('Booking Start Date', 'woocommerce-bookings'); ?></th>
            <td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html($booking->get_start_date(null, null, wc_should_convert_timezone($booking))); ?></td>
        </tr>
        <tr>
            <th style="text-align:left; border: 1px solid #eee;"
                scope="row"><?php esc_html_e('Booking End Date', 'woocommerce-bookings'); ?></th>
            <td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html($booking->get_end_date(null, null, wc_should_convert_timezone($booking))); ?></td>
        </tr>
        <?php if (wc_should_convert_timezone($booking)) : ?>
            <tr>
                <th style="text-align:left; border: 1px solid #eee;"
                    scope="row"><?php esc_html_e('Time Zone', 'woocommerce-bookings'); ?></th>
                <td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html(str_replace('_', ' ', $booking->get_local_timezone())); ?></td>
            </tr>
        <?php endif; ?>
        <?php if ($booking->has_persons()) : ?>
            <?php
            foreach ($booking->get_persons() as $id => $qty) :
                if (0 === $qty) {
                    continue;
                }
                $person_type = (0 < $id) ? get_the_title($id) : __('Person(s)', 'woocommerce-bookings');
                ?>
                <tr>
                    <th style="text-align:left; border: 1px solid #eee;"
                        scope="row"><?php echo esc_html($person_type); ?></th>
                    <td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html($qty); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>