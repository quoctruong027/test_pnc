<?php

Class Profiling_model
{

    public $db;
    protected $data;
    protected $profileTable;
    protected $preparationTable;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->profileTable = $this->db->prefix . 'etcpf_profiles';
        $this->preparationTable = $this->db->prefix . 'etcpf_variationupload_preparation';
    }

    public function getProfiles()
    {
        $this->data = $this->db->get_results("SELECT * FROM {$this->profileTable}");
        if ($this->data) {
            return $this->data;
        }
        return null;
    }

    public function getAssociatedAttributes($pid){
        return $this->db->get_results("SELECT * FROM {$this->preparationTable} WHERE profile_id={$pid} ORDER BY position ASC", ARRAY_A);
    }

    public function getTheProfile($id)
    {
        $this->data = $this->db->get_results("SELECT pt.*, vpt.* FROM {$this->profileTable} pt LEFT JOIN {$this->preparationTable} vpt ON pt.id = vpt.profile_id WHERE pt.id={$id} ORDER BY vpt.position ASC");
        if ($this->data) {
            return $this->data;
        }
        return null;
    }

    public function checkAttribute($id, $attr){
        return $this->db->get_var("SELECT id FROM {$this->preparationTable} WHERE profile_id={$id} AND variation_attribute='{$attr}'");
    }
}
