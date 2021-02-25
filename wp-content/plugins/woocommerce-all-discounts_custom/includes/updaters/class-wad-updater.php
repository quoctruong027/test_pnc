<?php

class WAD_Updater {
    protected $version_url = 'https://static.discountsuiteforwp.com/wad-updater.xml';
    public $title = 'Conditional Discounts for WooCommerce - Pro';

    protected $auto_updater = false;
    protected $upgrade_manager = false;
    protected $iframe = false;

    public function init() {
        add_filter('upgrader_pre_download', array($this, 'upgradeFilter'), 10, 4);
        add_action('upgrader_process_complete', array($this, 'removeTemporaryDir'));
    }

    /**
     * Setter for manager updater.
     *
     * @param WAD_Updating_Manager $updater
     */
    public function setUpdateManager(WAD_Updating_Manager $updater) {
        $this->auto_updater = $updater;
    }
    /**
     * Getter for manager updater.
     *
     * @return WAD_Updating_Manager
     */
    public function updateManager() {
        return $this->auto_updater;
    }

    /**
     * Get url for version validation
     * @return string
     */
    public function versionUrl() {
        return $this->version_url;
    }
    /**
     * Downloads new VC from Envato marketplace and unzips into temporary directory.
     *
     * @param $reply
     * @param $package
     * @param $updater
     * @return mixed|string|WP_Error
     */
    public function upgradeFilter($reply, $package, $updater) {
            global $wp_filesystem;
            if ( (isset($updater->skin->plugin) && $updater->skin->plugin === WAD_MAIN_FILE) ||
                    (isset($updater->skin->plugin_info) && htmlspecialchars_decode($updater->skin->plugin_info['Name']) === $this->title)
            ) {
                $updater->strings['download_from_servers'] = __('Downloading package from ORION servers...', 'wad');
                $updater->skin->feedback('download_from_servers');
                $package_filename = 'woocommerce-all-discounts.zip';
                $res = $updater->fs_connect(array(WP_CONTENT_DIR));
                if (!$res) {
                    return new WP_Error('no_credentials', __("Error! Can't connect to filesystem", 'wad'));
                }
                $options = get_option('wad-options');
                if (isset($options['purchase-code']) && $options['purchase-code'] !== "") {
                    $license_key = $options['purchase-code'];
                } else {
                    return new WP_Error('no_credentials', __('To receive automatic updates, license activation is required. Please visit <a href="' . admin_url('edit.php?post_type=o-discount&page=wad-manage-settings') . '' . '" target="_blank">Settings</a> to activate your WooCommerce All Discounts.', 'wad'));
                }

                $args = array('timeout' => 600);
                $site_url = get_site_url();
                $plugin_name = WAD_PLUGIN_NAME;
                $url = "https://discountsuiteforwp.com/service/olicenses/v1/checking/?license-key=" . urlencode($license_key) . "&siteurl=" . urlencode($site_url) . "&name=" . urlencode($plugin_name);
                $response = wp_remote_get($url, $args);
                if (!is_wp_error($response) ) {
                    if (isset($response["body"]) && intval($response["body"]) == 200) {
                        $json = wp_remote_get($this->downloadUrl($this->title), $args);
                        if (is_wp_error($json)) {
                            return $json->get_error_message();
                        }
                        if (isset($json["body"])) {
                            $answer = $json["body"];
                        }

                        $result = array();

                        if (is_array(json_decode($answer, true))) {
                            $result = json_decode($answer, true);
                        } else {
                            return new WP_Error('no_file', __('Error! No file found. Please contact the plugin owners.', 'wad'));;
                        }

                        if (!isset($result['download_url'])) {
                            return new WP_Error('no_file', __('Error! No file found. Please contact the plugin owners.', 'wad'));
                        }

                        $download_file = download_url($result['download_url']);
                        if (is_wp_error($download_file)) {
                            return $download_file;
                        }
                        $uploads_dir_obj = wp_upload_dir();
                        $upgrade_folder = $uploads_dir_obj["basedir"] . '/wad_package';
                        if (!is_dir($upgrade_folder)) {
                            mkdir($upgrade_folder);
                        }
                        //We rename the tmp file to a zip file
                        $new_zipname = str_replace(".tmp", ".zip", $download_file);
                        rename($download_file, $new_zipname);
                        //The upgrade is in the unique directory inside the upgrade folder
                        $new_version = "$upgrade_folder/$package_filename";
                        $result = copy($new_zipname, $new_version);
                        if ($result && is_file($new_version)) {
                            return $new_version;
                        }
                        return new WP_Error('no_credentials', __('Error on unzipping package', 'wad'));
                    } else {
                        return new WP_Error('network_error', __('Wrong license key provided.', 'wad'));
                    }
                } else {
                    return $response->get_error_message();
                }
            }
            return $reply;
    }
        
    public function removeTemporaryDir() {
            global $wp_filesystem;
            if (is_dir($wp_filesystem->wp_content_dir() . 'uploads/wad_package')) {
                $wp_filesystem->delete($wp_filesystem->wp_content_dir() . 'uploads/wad_package', true);
            }
    }
    protected function downloadUrl( $title ) {
            global $wad_settings;
            $purchase_code = "";
            $site_url = get_site_url();
            if (isset( $wad_settings[ 'purchase-code' ] )  && $wad_settings['purchase-code'] != "" ) {
                    $purchase_code = $wad_settings[ 'purchase-code' ];
    }
            return "https://discountsuiteforwp.com/service/oupdater/v1/update/?name=". $title  . "&purchase-code=" . $purchase_code . "&siteurl=" . urlencode($site_url);
}
}