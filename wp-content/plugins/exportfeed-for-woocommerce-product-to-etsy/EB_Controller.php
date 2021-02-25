<?php
if (!defined('ABSPATH')) die("Unauthorized");

Class EB_Controller
{

    public $view_path;

    public $model;

    protected $data = array();

    public $db;

    public function __construct()
    {
        $this->view_path = ETCPF_PATH . '/core/etsy-views/';
    }

    public function load($component, $componentName)
    {
        switch ($component) {
            case 'model':
                $file = ETCPF_PATH . '/core/models/' . strtolower($componentName) . '/' . ucfirst($componentName) . '_model.php';
                if (file_exists($file)) {
                    include_once $file;
                    $tobeLoaded = ucfirst($componentName . '_' . $component);
                    return $this->$component = new $tobeLoaded();
                }else{
                    echo "<pre>";
                    print_r($component.' '.$componentName." could not be found in ".'core/models/' . strtolower($componentName) . '/' . ucfirst($componentName));
                    echo "</pre>";
                    exit;
                }
                break;

            default:

        }
    }

    public function view($insView, $inaData = array(), $etcpf_return = false)
    {
        $sFile = $this->view_path . $insView . '.php';
        if (!is_file($sFile)) {
            echo "View not found: " . $sFile;
            return false;
        }

        $inaData = $this->_etcpf_prepare_view_vars($inaData);
        extract($inaData);

        ob_start();
        include_once $sFile;
        $sContents = ob_get_contents();
        if ($etcpf_return == true) return $sContents;
        ob_end_clean();

        echo $sContents;
        return true;
    }

    protected function _etcpf_prepare_view_vars($vars)
    {
        if (!is_array($vars)) {
            $vars = is_object($vars)
                ? get_object_vars($vars)
                : array();
        }

        foreach (array_keys($vars) as $key) {
            if (strncmp($key, '_ci_', 4) === 0) {
                unset($vars[$key]);
            }
        }

        return $vars;
    }

}
