<?php
$check_account = $this->mate->count;
$there_is_account = ($check_account > 0) ? true : false;
?>
<div class="wrap cpf-page">
    <div id="poststuff">
        <div id="postbox-container-2" class="postbox-container">
            <div class="meta-box-sortables ui-sortable">

                <?php
                if ($there_is_account)
                    $this->view('account-list', [
                        'shops' => $cpf_shops,
                    ]);
                else
                    $this->view('setup-account');
                ?>
            </div>
        </div>
    </div>
</div>