<h1><?php _ex('Aliexpress Dropship Settings', 'Setting title', 'ali2woo-lite');?></h1>
<div class="a2wl-content">
    <?php include_once A2WL()->plugin_path() . '/view/chrome_notify.php'; ?>
    <div class="_a2wfo a2wl-info"><div>You are using Ai2Woo Lite. If you want to unlock all features and get premium support, purchase the full version of the plugin.</div><a href="https://codecanyon.net/item/aliexpress-dropship-for-woocommerce/19821022" target="_blank" class="btn">GET FULL VERSOIN</a></div>
    <ul class="nav nav-tabs">
      <?php foreach($modules as $module):?>
      <li role="presentation" <?php echo $current_module == $module['id'] ? 'class="active"' : ""; ?>><a href="<?php echo admin_url('admin.php?page=a2wl_setting&subpage='.$module['id']); ?>"><?php echo $module['name'] ?></a></li>
      <?php endforeach; ?>
    </ul>
