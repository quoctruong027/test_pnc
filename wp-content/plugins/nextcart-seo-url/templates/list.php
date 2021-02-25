<?php
/** 
 * @package Next-Cart
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$list_table->prepare_items();
$title = $list_table->get_title();
?>
<div class="wrap">
    <h1 class="wp-heading-inline">URL Redirects</h1>
    <?php
    $search = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';
    if (strlen($search)) {
        printf('<span class="subtitle">Search results for &#8220;%s&#8221;</span>', esc_html($search));
    }
    ?>
    <hr class="wp-header-end">
    
    <?php if (!empty($errors)) : ?>
        <div class="error">
            <ul>
                <?php
                foreach ($errors as $err)
                    echo "<li>$err</li>\n";
                ?>
            </ul>
        </div>
    <?php
    endif;

    if (!empty($messages)) {
        foreach ($messages as $msg)
            echo '<div id="message" class="updated notice is-dismissible"><p>' . $msg . '</p></div>';
    }
    ?>
    
    <?php $list_table->views(); ?>
    <form method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <?php $list_table->search_box('Search ' . $title['plural'], strtolower($title['singular'])); ?>
        <?php $list_table->display(); ?>
    </form>

    <br class="clear" />
    <script type="text/javascript">
                        /* <![CDATA[ */

                                jQuery( 'a.delete' ).click( function() {
                                        if ( window.confirm( '<?php esc_html_e('Are you sure you want to delete this '.strtolower($title['singular']).'?'); ?>' ) ) {
                                                return true;
                                        }
                                        return false;
                                });

                        /* ]]> */
    </script>
</div>

