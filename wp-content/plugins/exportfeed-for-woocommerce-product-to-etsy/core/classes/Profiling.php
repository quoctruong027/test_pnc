<?php
include_once ETCPF_PATH . '/EB_Controller.php';

Class Profiling extends EB_Controller
{
    public $db;
    protected $data;
    protected $profileTable;
    protected $preparationTable;
    protected $metaTable;

    public function __construct()
    {
        parent::__construct();
        global $wpdb;
        $this->db = $wpdb;
        $this->profileTable = $this->db->prefix . 'etcpf_profiles';
        $this->preparationTable = $this->db->prefix . 'etcpf_variationupload_preparation';
        $this->metaTable = $this->db->prefix . 'postmeta';
        $this->load('model', 'profiling');
    }

    public function list_profiles()
    {
        $this->data['profiles'] = $this->model->getProfiles();
        if (is_array($this->data['profiles']) && count($this->data['profiles']) > 0) {
            foreach ($this->data['profiles'] as $key => $profile) {
                $profileAssociates = $this->model->getAssociatedAttributes($profile->id);
                $variationAttributes = array_column($profileAssociates, 'variation_attribute');
                $this->data['profiles'][$key]->variations = $variationAttributes;
                $this->data['profiles'][$key]->prefix = array_column($profileAssociates, 'prefix');
                $this->data['profiles'][$key]->suffix = array_column($profileAssociates, 'suffix');
            }
        }
        $this->view('profiling/profiling', $this->data, false);
    }

    public function add_new()
    {
        if ($_POST) {
            $data = $this->get_post_data($_POST);
            $positions = explode(',', $data['sorted_attributes']);
            if (strlen($data['sorted_attributes']) > 0 && count($positions) > 0) {
                $profiles = array(
                    'profile_name' => $data['profile_name'],
                    'profile_description' => $data['profile_description'],
                    'attribute_seperator' => $data['attribute-seperator']
                );
                if ($profile_id = $this->save($profiles, $this->profileTable)) {
                    foreach ($positions as $key => $attr) {
                        $attr = str_replace('_space_', ' ', $attr);
                        $positionData = array();
                        $positionData['profile_id'] = $profile_id;
                        $positionData['variation_attribute'] = $attr;
                        $positionData['prefix'] = $data['prefix_' . $attr];
                        $positionData['suffix'] = $data['suffix_' . $attr];
                        $positionData['position'] = $key;
                        $this->save($positionData, $this->preparationTable);
                        unset($positionData);
                    }
                    if (!isset($_SESSION)) {
                        session_start();
                    }
                    if (!$this->db->last_error) {
                        $_SESSION['etcpf_profile_message'] = "Profile Added successfully";
                        wp_redirect(admin_url('admin.php?page=etsy-export-feed-profiling'));
                        exit;
                    } else {
                        $_SESSION['etcpf_profile_message'] = "Profile could not be added, please try again.";
                        wp_redirect(admin_url('admin.php?page=etsy-export-feed-profiling'));
                        exit;
                    }
                }
            } else {
                if (!isset($_SESSION)) {
                    session_start();
                }
                $_SESSION['etcpf_profile_message'] = "No variation attributes selected for profiling. Please select atleast on variation attributes. Thanks.";
                wp_redirect(admin_url('admin.php?page=etsy-export-feed-profiling'));
                exit;
            }
        }
        $this->data['product_attributes'] = $this->getAttributes();
        $this->view('profiling/add_new', $this->data, false);
    }

    public function edit_profiles($id)
    {
        $this->data['profile'] = $this->model->getTheProfile($id);
        if ($_POST) {
            $data = $this->get_post_data($_POST);
            $positions = explode(',', $data['sorted_attributes']);
            if (strlen($data['sorted_attributes']) > 0 && count($positions) > 0) {
                $profiles = array(
                    'profile_name' => $data['profile_name'],
                    'profile_description' => $data['profile_description'],
                    'attribute_seperator' => $data['attribute-seperator']
                );
                if ($this->update($profiles, $this->profileTable, array('id' => $id))) {
                    foreach ($positions as $key => $attr) {
                        $attr = str_replace('_space_', ' ', $attr);
                        $positionData = array();
                        $positionData['profile_id'] = $id;
                        $positionData['variation_attribute'] = $attr;
                        $positionData['prefix'] = $data['prefix_' . $attr];
                        $positionData['suffix'] = $data['suffix_' . $attr];
                        $positionData['position'] = $key;

                        if (!$this->model->checkAttribute($id, $attr)) {
                            $this->save($positionData, $this->preparationTable);
                        } else {
                            $this->update($positionData, $this->preparationTable, array('profile_id' => $id, 'variation_attribute' => $attr));
                        }

                        unset($positionData);
                    }
                    if (!isset($_SESSION)) {
                        session_start();
                    }
                    $_SESSION['etcpf_profile_message'] = "Profile Edited successfully";
                    wp_redirect(admin_url('admin.php?page=etsy-export-feed-profiling'));
                    exit;
                }
            } else {
                if (!isset($_SESSION)) {
                    session_start();
                }
                $_SESSION['etcpf_profile_message'] = "No variation attributes selected for profiling. Please select atleast on variation attributes. Thanks.";
                wp_redirect(admin_url('admin.php?page=etsy-export-feed-profiling'));
                exit;
            }
        }

        $this->data['product_attributes'] = is_array($this->data['profile']) ? array_column($this->data['profile'], 'variation_attribute')
            : array();
        $this->data['all_product_attributes'] = $this->getAttributes();
        $this->data['prefix'] = is_array($this->data['profile']) ? array_column($this->data['profile'], 'prefix')
            : array();
        $this->data['suffix'] = is_array($this->data['profile']) ? array_column($this->data['profile'], 'suffix')
            : array();

        /**foreach ($this->getAttributes() as $key => $value) {
         * if (array_search($value, $this->data['product_attributes'], true) === false) {
         * $this->data['product_attributes'][] = $value;
         * } else {
         * continue;
         * }
         * }*/


        $this->view('profiling/edit', $this->data);
    }

    public function delete($id)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $this->db->delete($this->preparationTable, array('profile_id' => $id));
        $this->db->delete($this->profileTable, array('id' => $id));
        if (!$this->db->last_error) {
            $_SESSION['etcpf_profile_message'] = "Profile deleted successfully";
            wp_redirect(admin_url('admin.php?page=etsy-export-feed-profiling'));
            exit;
        } else {
            $_SESSION['etcpf_profile_message'] = "Profile could not be deleted. Please try again later";
            wp_redirect(admin_url('admin.php?page=etsy-export-feed-profiling'));
            exit;
        }
    }

    public function getAttributes()
    {
        $data = $this->db->get_results("SELECT * FROM $this->metaTable WHERE meta_key LIKE '%_product_attribute%'");
        $product_attributes = array();
        foreach ($data as $key => $datum) {
            $attributes = maybe_unserialize($datum->meta_value);
            if(!is_array($attributes))
                continue;
            foreach ($attributes as $pa => $av) {
                if ($av['is_variation']) {
                    $product_attributes[str_replace(array('attribute_pa_', 'attribute_', 'pa_'), '', $av['name'])] = str_replace(array('attribute_pa_', 'attribute_', 'pa_'), '', $av['name']);
                }
            }
        }
        return $product_attributes;
    }

    public function get_post_data($postData)
    {
        $temp = array();
        foreach ($postData as $key => $value) {
            $temp[$key] = sanitize_post($value);
        }
        return $temp;
    }

    public function save($data, $table)
    {
        $this->db->insert($table, $data);
        if (!$this->db->last_error) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function update($data, $table, $where)
    {
        $this->db->update($table, $data, $where);
        return $this->db->last_error ? fasle : true;
    }

    public function validate($field, $value)
    {
        if ($value) {
            switch ($field) {
                case 'profile_name':
                    if ($this->checkExistance($field, $value)){
                        return $this->getValidationMessage($field);
                    }else{
                        return array('success'=>true);
                    }
            }
        }
    }

    public function checkExistance($field, $value)
    {
        return $this->db->get_row($this->db->prepare("SELECT id FROM $this->profileTable WHERE {$field}=%s",array($value))) ? true : false;
    }

    public function getValidationMessage($field)
    {
        $validationMessages = array(
            'profile_name' => array(
                'success' => false,
                'message' => 'Profile name already exists, please change the profile name.'
            )
        );

        return $validationMessages[$field];
    }

}
