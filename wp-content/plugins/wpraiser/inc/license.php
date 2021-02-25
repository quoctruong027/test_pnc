<?php

# Exit if accessed directly				
if (!defined('ABSPATH')){ exit(); }	

# initialize update checks
add_action('admin_init', 'wpraiser_check_updates');
add_action('in_plugin_update_message-' . $wpraiser_var_basename, 'wpraiser_update_message', 10, 2 );


# get license value
function wpraiser_get_license_string($query) {
	$wpraiser_license = get_option('wpraiser_license');
	if(isset($wpraiser_license) && $wpraiser_license != false) {
		$lic = json_decode($wpraiser_license, true);
		if(is_array($lic) && isset($lic[$query])) {
			return $lic[$query];
		}
	}
	return '';
}


# activate license
function wpraiser_license_activate($license) {
		
	global $wpraiser_urls;
		
	# validate license format
	if(!is_array($license) || !isset($license['serial']) || !isset($license['identifier'])) { return false; }
		
	# add root domain
	$license = array_merge($license, array('domain'=>str_ireplace('www.', '', $wpraiser_urls['wp_domain'])));
	
	# strip hyphen
	$license['serial'] = str_replace('-', '', $license['serial']); 
	
	# generate token and endpoint url
	$token = base64_encode(json_encode($license));
	$api = 'https://updates.raisercdn.com/api/wpraiser/update.php?action=activate&token='.$token;
	
	# make request
    $request = wp_remote_get($api, array('timeout' => 5, 'headers' => array('Accept' => 'application/json')));
    if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200 || wp_remote_retrieve_response_code($request) === 402) {
        $data = json_decode((string) $request['body']);
		
		# update expiration time
		if(isset($data->expires)) {
			update_option('wpraiser_license_expires', $data->expires);
			return true;
		}
		
		# delete license if unauthorized
		if(isset($data->error)) {
			return $data->error;
		}
		
    }
	
	# fallback
    return false;
}






# define messages on the support page
function wpraiser_support_license_info($src = null) {
	$wpraiser_license = get_option('wpraiser_license');
	$lic = json_decode($wpraiser_license, true);
	
	# no license available
	if($wpraiser_license == false || !is_array($lic) || !isset($lic['serial']) || !isset($lic['identifier'])) {
		if($src == 'support' || $src == 'dashboard') {
			return sprintf( __('To enable updates, please insert your <code>license key</code> and <code>unique identifier</code> or <a target="_blank" href="%s">contact us</a> to obtain a license code for your domain.', 'wpraiser'), 'https://www.upwork.com/fl/raulpeixoto');
		} else {
			return sprintf( __('To enable updates, please <a target="_blank" href="%s">update</a> your license key or contact us to obtain a license code for your domain.', 'wpraiser'), admin_url('options-general.php?page=wpraiser#support'));
		}
	}	
	
	# must be in a valid format
	if(strlen(str_replace('-', '', $lic['serial'])) != 40 || !ctype_xdigit(str_replace('-', '', $lic['serial'])) || !filter_var($lic['identifier'], FILTER_VALIDATE_EMAIL)) {
		return sprintf( __('Your license was saved in the wrong format. Please insert your license key correctly or <a target="_blank" href="%s">contact us</a> to obtain a working license code for your domain.', 'wpraiser'), 'https://www.upwork.com/fl/raulpeixoto');
	}
	
	# check validity expiration
	$expires = get_option('wpraiser_license_expires', 0);
	if($expires != false && is_numeric($expires)) {
		
		# expired?
		if($expires < time() && $expires > 0) {
			return sprintf( __('Your license has expired on <code>'.date('D, d M Y H:i e', intval($expires)).'</code>. Please update your license key or <a target="_blank" href="%s">contact us</a> to renew the license for your domain.', 'wpraiser'), 'https://www.upwork.com/fl/raulpeixoto');
		}
		
		# show expires date
		if($expires > time() && $src == 'dashboard') {
			return sprintf( __('---<br /><br />Your license expires on <code>'.date('D, d M Y H:i e', intval($expires)).'</code><br /><br />You can <a target="_blank" href="%s">contact us</a> anytime to renew the license for your domain.<br />In the future, this will be also possible through our portal.<br />Thank you very much for supporting our service!', 'wpraiser'), 'https://www.upwork.com/fl/raulpeixoto');
		}
		
	}
	
	# default
	return false;
	
}


# check for updates
add_action('admin_init', 'wpraiser_check_updates');
function wpraiser_check_updates() {
	if(current_user_can('manage_options')) {
		global $wpraiser_var_basename, $wpraiser_var_plugin_version; 
		$wpraiser_license = get_option('wpraiser_license');
		$endpoint = 'https://updates.raisercdn.com/api/wpraiser/update.php';
		new wpraiser_plugin_update($wpraiser_var_plugin_version, $endpoint, $wpraiser_var_basename, $wpraiser_license);
	}	
}


# append a message to the plugins update page
function wpraiser_update_message() {
	$lic = wpraiser_support_license_info();	
	if($lic != false) { echo '<br />' . $lic; }
	return false;
}


# self hosted updater
class wpraiser_plugin_update {
	
    # The plugin current version, remote update path, path slug, file slug, license
    public $version;
    public $endpoint;
    public $plugin_slug;
    public $slug;
	public $license;
 
    # Initialize a new instance of the WordPress Auto-Update class
    function __construct($version, $endpoint, $plugin_slug, $license){
        $this->version = $version;
        $this->endpoint = $endpoint;
        $this->plugin_slug = $plugin_slug;
		$this->license = str_replace('-', '', $license);
        list ($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);
 
        # define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'), 10, 1);
 
        # Define the alternative response for information checking
        add_filter('plugins_api', array($this, 'check_info'), 10, 3);
    }
 
    # Add our self-hosted autoupdate plugin to the filter transient
    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
		
        # Get the remote version
        $latest = $this->get_version();
 
        # If a newer version is available, add the update
        if (version_compare($this->version, $latest, '<')) {
 			$obj = $this->get_package();
            $obj->slug = $this->slug;
            $obj->new_version = $latest;
            $transient->response[$this->plugin_slug] = (object) $obj;
        }
        return $transient;
    }
 
    # Add our self-hosted description to the filter
    public function check_info($result, $action = null, $args = null) {
		
		// vars
		$plugin = false;
		
		// only for 'plugin_information' action
		if( $action !== 'plugin_information' ) {
			return $result;
		}
		
		// this plugin only
        if ($args->slug === $this->slug) {
            $information = $this->get_info();
            return $information;
        }
		
        return $result;
    }
 
 
    # Return the remote version, limit to once every 1 hour
    public function get_version() {
		$wpraiser_update = get_transient('wpraiser_update');
		if($wpraiser_update === false) {
			$request = wp_remote_get($this->endpoint.'?action=version', array('timeout' => 5, 'headers' => array('Accept' => 'application/json')));
			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				$data = json_decode((string) $request['body']);
				if(isset($data->version)) {
					set_transient('wpraiser_update', $data->version, HOUR_IN_SECONDS);
					return $data->version;
				}
			}
		} else {
			return $wpraiser_update;
		}
		
        return false;
    }

    # Get information about the remote version
    public function get_info() {
        $request = wp_remote_get($this->endpoint.'?action=info', array('timeout' => 5, 'headers' => array('Accept' => 'application/json')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            $data = json_decode((string) $request['body']);
			if(isset($data->sections)) {
				$res = new stdClass();
				foreach ($data as $k=>$v) {
					if(is_object($v)) {
						$res->$k = (array) $data->$k;
					} else {
						$res->$k = $data->$k;
					}					
				}
				return $res;
			}
        }
        return false;
    }
	
    # Get the updated file or fail
    public function get_package() {
		
		global $wpraiser_urls;
		
		# license to array
		$license = json_decode($this->license, true);
				
		# validate license format
		if(!is_array($license) || !isset($license['serial']) || !isset($license['identifier'])) { return false; }
		
		# add root domain
		$license = array_merge($license, array('domain'=>str_ireplace('www.', '', $wpraiser_urls['wp_domain'])));
		
		# generate token and endpoint url
		$token = base64_encode(json_encode($license));
		$api = $this->endpoint.'?action=update&token='.$token;
				
		# make request
        $request = wp_remote_get($api, array('timeout' => 5, 'headers' => array('Accept' => 'application/json')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            $data = json_decode((string) $request['body']);
			
			# update expire date
			if(isset($data->expires)) {
				update_option('wpraiser_license_expires', $data->expires);
			}
			
			# process download
			if(isset($data->package)) {
				$res = new stdClass();
				foreach ($data as $k=>$v) {
					if(is_object($v)) {
						$res->$k = (array) $data->$k;
					} else {
						$res->$k = $data->$k;
					}					
				}
				return $res;
			}
			
        }
        return false;
    }

}

