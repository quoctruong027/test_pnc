<?php
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'accounts';

$cpf_settings_url = 'admin.php?page=etsy-export-feed-configure';
if (!isset($_GET['site_id'])) {
    $style = 'display:block';
} else {
    $style = 'display:none';
}
?>

    <h2 class="nav-tab-wrapper" style="<?php echo $style; ?>">
        <a href="<?php echo $cpf_settings_url; ?>&tab=accounts"
           class="nav-tab <?php echo $active_tab == 'accounts' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Shops', 'etsy-export-feed-strings') ?></a>
        <a href="<?php echo $cpf_settings_url; ?>&tab=settings"
           class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Shippings', 'etsy-export-feed-strings') ?></a>
        <a href="admin.php?page=etsy-export-feed-admin&tab=createfeed"
           class="nav-tab <?php echo $active_tab == 'createfeed' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Create Feed', 'etsy-export-feed-strings') ?></a>
        <a href="<?php echo $cpf_settings_url; ?>&tab=etsymanagefeed"
           class="nav-tab <?php echo $active_tab == 'etsymanagefeed' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Manage Feed', 'etsy-export-feed-strings') ?></a>
        <a href="admin.php?page=etsy-export-feed-setting" class="nav-tab ">Configurations</a>
        <a href="<?php echo $cpf_settings_url; ?>&tab=manuals"
           class="nav-tab <?php echo $active_tab == 'manuals' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Tutorials', 'etsy-export-feed-strings') ?></a>
    </h2>
<?php
switch ($active_tab) {
    case 'accounts':
        $this->display();
        break;
    case 'settings';
        $this->listShippingTemplate();
        break;
    case 'createfeed';
        $this->get_createfeeds_page();
        break;
    case 'etsymanagefeed';
        $this->get_managefeed_page();
        break;
    case 'manuals':
        $this->manuals();
        break;
    case 'configuration':
        $this->configuration_tab();
        break;
    default:
        break;
}
?>