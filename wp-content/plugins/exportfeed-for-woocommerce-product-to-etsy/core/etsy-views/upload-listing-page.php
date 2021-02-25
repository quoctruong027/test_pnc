<?php
//Create an instance of our package class...
$testListTable = new ETCPF_Product_Uploaded();
//Fetch, prepare, sort, and filter our data...
$testListTable->prepare_items();
$msg = 'Your uploaded listings are shown below';
if (isset($_REQUEST['msg'])) {
    $msg = $_REQUEST['msg'];
    switch ($_REQUEST['msg']) {
        case 'a':
            $msg = 'Products are uploaded. Please check you shop in Etsy.';
            break;
        case 'b':
            $msg = 'Products failed to upload. Please check the listing below to see the errors.';
            break;
        case 'd':
            $msg = 'Product are deleted successfully but not from Etsy Store.';
            break;
        case 'de':
            $msg = 'Product are deleted successfully as well from Etsy Store.';
            break;
        case 'e':
            $msg = $_REQUEST['title'] . ' is now active in your Etsy shop';
            break;
        case 'f':
            $msg = $_REQUEST['title'] . ' is now inactive in your Etsy shop';
            break;
        case 'g':
            $msg = 'Listings are now active in Etsy shop.';
            break;
        case 'u':
            $msg = 'Selected Variation uploaded successfully.';
            break;
        case 'l':
            $msg = 'Product Upload limit Exceeded. Please update you license.';
        case 'np':
            $msg="No Product Selected, for update.";
            break;

        default:
            $msg = $msg;
    }
}
?>
<br>
<div class="update-nag" id="report_msg" style="display:block; "><p><?=$msg?></p></div>
<div class="wrap">

    <h2>Etsy Listings Detail</h2>

    <form id="etcpf-uploaded-product-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php
        $testListTable->search_box('search', 'search_id');
        $testListTable->display()
        ?>
    </form>

</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('a.activate_in_etsy').click(function (e) {
            var activation = confirm('Do you want this item to be active in you Etsy Shop?');
            console.log(activation);
            if (!activation) {
                e.preventDefault();
                return;
            }

        });
    });
</script>