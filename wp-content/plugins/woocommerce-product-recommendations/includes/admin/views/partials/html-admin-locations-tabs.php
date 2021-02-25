<?php
// Parse GET for selecting current tab.
$section     = isset( $_GET[ 'section' ] ) ? wc_clean( $_GET[ 'section' ] ) : 'locations_overview';
$location_id = isset( $_GET[ 'location' ] ) ? wc_clean( $_GET[ 'location' ] ) : false;
?>
<ul class="subsubsub">

	<li><a href="<?php echo admin_url( 'admin.php?page=prl_locations' ); ?>" <?php echo $section === 'locations_overview' ? 'class="current"' : '' ?>>Overview</a> | </li>

	<?php foreach ( $locations as $id => $location ) {

		$selected = $section === 'hooks' && $location->get_location_id() === $location_id ? 'class="current"' : ''; ?>

		<li><a href="<?php echo admin_url( 'admin.php?page=prl_locations&section=hooks&location=' . $location->get_location_id() ) ?>" <?php echo $selected ?>><?php echo $location->get_title() ?></a> <span>|</span> </li>

	<?php } ?>

</ul>
