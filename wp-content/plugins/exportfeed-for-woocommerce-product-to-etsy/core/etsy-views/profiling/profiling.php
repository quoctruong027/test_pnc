<div class="etcpf-upload-block">
    <form id="regForm" action="javascript:void(0);">
        <?php
        if (isset($_SESSION['etcpf_profile_message'])) { ?>
            <p style="padding: 10px 24px; background-color: #f0f8ff;"><?php
                echo $_SESSION['etcpf_profile_message'];
                unset($_SESSION['etcpf_profile_message']);
                ?></p>
        <?php } ?>
        <div class="profilling_header">
            <div class="profilling_title">
                <h2>Etsy Variation Profile lists</h2>
            </div>
            <div class="profilling_addnew">
                <button id="profiling-add-new-btn" class="button button-primary"><a href="<?php echo admin_url('admin.php?page=etsy-export-feed-profiling&action=add_new') ?>">Add New</a></button>
            </div>
        </div>
        <div class="draggable_section">
            <table class="table table-bordered table-striped" id="tablelist" style="height: auto;">
                <thead style="width: calc( 100% - 1em )">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Variation Instance</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody class="ui-sortable" style="max-height: 720px;">

                <?php if (isset($profiles) && is_array($profiles) && count($profiles) > 0) {
                    foreach ($profiles as $key => $profile) {
                        ?>
                        <tr id="<?php echo $profile->id; ?>" class="ui-sortable-handle">
                            <td><?php echo $profile->id; ?></td>
                            <td><?php echo $profile->profile_name; ?></td>
                            <td>
                                <?php
                                $html = '<br>';
                                echo 'Variation Attributes: ' . str_replace(',', $profile->attribute_seperator, implode(',', $profile->variations));
                                foreach ($profile->variations as $k => $variation) {
                                    $html .= $profile->prefix[$k] . $variation .' value' . $profile->suffix[$k];
                                    if(count($profile->variations)-1 > $k){
                                        $html .= $profile->attribute_seperator;
                                    }
                                }
                                echo '<br>Variation value formed: ' . $html;
                                ?>
                            </td>
                            <td>
                                <span><a class="button"
                                         href="<?php echo admin_url('admin.php?page=etsy-export-feed-profiling&action=edit&id=' . $profile->id) ?>">Edit</a></span>

                                <span><a id="profile-deletion-button" class="button"
                                         href="<?php echo admin_url('admin.php?page=etsy-export-feed-profiling&action=delete&id=' . $profile->id) ?>">Delete</a></span>
                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td style="text-align: center;" colspan="4"> No Profiles Found</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
    (function ($) {
        $(document).on('click', '#profile-deletion-button', function (event) {
            event.preventDefault();
            r = confirm("Are you sure you want to delete this profile ?");
            if (r === true) {
                location.replace($(this).attr('href'));
            }
        })

        jQuery(document).on('click', '#profiling-add-new-btn', function(event){
            event.preventDefault();
            location.replace(window.location.protocol +'//'+ window.location.host + '/wp-admin/admin.php?page=etsy-export-feed-profiling&action=add_new');
        })
    })(jQuery)
</script>
