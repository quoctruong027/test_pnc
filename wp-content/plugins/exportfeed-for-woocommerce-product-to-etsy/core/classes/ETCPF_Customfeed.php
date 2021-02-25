<?php
$file = dirname(__FILE__).'/../data/ETCPF_Productsw.php';
if(file_exists(realpath($file))){
    include_once $file;
}else{
    die($file." doesn't exists");
}

include_once ETCPF_PATH.'/core/classes/dialogbasefeed.php';
include_once ETCPF_PATH.'/etsy-export-feed-wpincludes.php';

Class ETCPF_Customfeed extends ETCPF_Products_Store{
    public $feedtype;
    public $data = array();

    public function __construct($feedtype)
    {
        parent::__construct();
        $this->feedtype = $feedtype;
    }

    public function index(){
        $args = array();
        $data = array();
        $limit = 20;
        $offset = 0;
        $reg = new stdClass();
        $reg->valid = true;
        $data['registrationData'] = $reg;
        $data['product_cat_data'] = $this->getAllCategories();
        $args['showOutofStock'] = '1';
        $data['products_data'] = $this->getProducts($args,$limit,$offset);
        $data['previousData'] = null;
        $data['filename'] = '';
        $this->view('main-dialogue',$data);
        return true;
    }

    public function editFeed($feedid){
        $args = array();
        $data = array();
        $limit = 20;
        $offset = 0;
        $reg = new stdClass();
        $reg->valid = true;
        $data['registrationData'] = $reg;
        $data['product_cat_data'] = $this->getAllCategories();
        $args['showOutofStock'] = '1';
        $data['products_data'] = $this->getProducts($args,$limit,$offset);
        $data['previousData'] = $this->getProductsOfParticularFeed($feedid);
        $data['selectedCategory'] = $this->getMappedCategoryOfParticularFeed($feedid);
        $feedDetails = $this->getFilenameByFeedID($feedid);
        $data['filename'] = $feedDetails->filename;
        $this->view('main-dialogue',$data);
        return true;
    }

    public function getAllCategories(){
        $args = array(
            'number'     => false,
            'orderby'    => 'term_id',
            'order'      => 'ASC',
            'hide_empty' => true,
        );
        $product_categories = get_terms( 'product_cat', $args );
        $tree = $this->buildTree($product_categories);
        $productCatWithCount = $this->countProducts($tree);
        return $productCatWithCount;
    }

    public function countProducts(array $elements){
       foreach ($elements as $element){
           $element->count = $element->count;
           if(isset($element->children)){
               foreach ($element->children as $child){
                   $element->count += $child->count;
                   $child->count = $child->count;
                   if(isset($child->children)){
                       foreach ($child->children as $childsofchild){
                           $element->count += $childsofchild->count;
                           $child->count += $childsofchild->count;
                           $childsofchild->count = $childsofchild->count;
                           if(isset($childsofchild->children)){
                               foreach ($childsofchild->children as $lastlevelchildren){
                                   $element->count += $lastlevelchildren->count;
                                   $child->count += $lastlevelchildren->count;
                                   $childsofchild->count += $lastlevelchildren->count;
                               }
                           }
                       }
                   }
               }
           }
       }
       return $elements;
    }

    public function buildTree(array $elements, $parentId = 0) {
        $branch = array();

        foreach ($elements as $element) {
            if ($element->parent == $parentId) {
                $children = $this->buildTree($elements, $element->term_id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    public function view($viewfile,$data=array()){
        $dir = dirname(__FILE__);
        $viewDir = $dir.'/../etsy-views/custom-views/'.$viewfile.'.php';
        if(file_exists($viewDir)){
            $realFile = realpath($viewDir);
            include_once $realFile;
        }else{
            echo "<pre>";
            print_r($viewfile . ' could not be found.');
            echo "</pre>";
            exit();
        }
        echo "<pre>";
        print_r($dir);
        echo "</pre>";
        exit();
    }

    public function attributeMappings(){
        if(class_exists('ETCPF_PBaseFeedDialog')){
            $object = new ETCPF_PBaseFeedDialog();
            $object->initializeProvider();
            return $object->attributeMappings();
        }
        return null;
    }
}