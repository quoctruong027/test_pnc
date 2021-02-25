<?php 
$write_info_log = a2wl_get_setting('write_info_log');
?>

<form method="post">
    <input type="hidden" name="setting_form" value="1"/>
    <div class="system_info">
        <div class="panel panel-primary mt20">
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label for="a2wl_write_info_log">
                            <strong><?php _e('Write ali2woo-lite logs', 'ali2woo-lite'); ?></strong>
                        </label>
                        <div class="info-box" data-toggle="tooltip" title="<?php _e('Write ali2woo-lite logs', 'ali2woo-lite'); ?>"></div>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin">
                            <input type="checkbox" class="form-control" id="a2wl_write_info_log" name="a2wl_write_info_log" value="yes" <?php if ($write_info_log): ?>checked<?php endif; ?>/>
                            <?php if ($write_info_log): ?>
                                <div><?php if (file_exists(A2WL_Logs::getInstance()->log_path())): ?><a target="_blank" href="<?php echo A2WL_Logs::getInstance()->log_url();?>">Open log file</a> | <?php endif; ?>
                                <a class="a2wl-clear-log" href="#">Сlear log file</a></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label>
                            <strong><?php _e('Server address', 'ali2woo-lite'); ?></strong>
                        </label>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php echo $server_ip;?>
                        </div>                                                                     
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label>
                            <strong><?php _e('Php version', 'ali2woo-lite'); ?></strong>
                        </label>
                        <div class="info-box" data-toggle="tooltip" title="<?php _ex('Php version', 'setting description', 'ali2woo-lite'); ?>"></div>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php
                            $result = A2WL_SystemInfo::php_check();
                            echo ($result['state']!=='ok'?'<span class="error">ERROR</span>':'<span class="ok">OK</span>');
                            if($result['state']!=='ok'){
                                echo '<div class="info-box" data-toggle="tooltip" title="'.$result['message'].'"></div>';
                            }
                            ?>
                        </div>                                                                     
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label>
                            <strong><?php _e('Php config', 'ali2woo-lite'); ?></strong>
                        </label>
                    </div>
                    
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="php_ini_check_row">
                            <span>allow_url_fopen :</span>
                            <?php if(ini_get('allow_url_fopen')):?>
                                <span class="ok">On</span>
                            <?php else: ?>
                                <span class="error">Off</span><div class="info-box" data-toggle="tooltip" title="<?php _e('There may be problems with the image editor', 'ali2woo-lite');?>"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label>
                            <strong><?php _e('Site ping', 'ali2woo-lite'); ?></strong>
                        </label>
                        <div class="info-box" data-toggle="tooltip" title="<?php _ex('Site ping', 'setting description', 'ali2woo-lite'); ?>"></div>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php
                            $result = A2WL_SystemInfo::ping();
                            echo ($result['state']!=='ok'?'<span class="error">ERROR</span>':'<span class="ok">OK</span>');
                            if(!empty($result['message'])){
                                echo '<div class="info-box" data-toggle="tooltip" title="'.$result['message'].'"></div>';
                            }
                            ?>
                        </div>                                                                     
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label>
                            <strong><?php _e('Server ping', 'ali2woo-lite'); ?></strong>
                        </label>
                        <div class="info-box" data-toggle="tooltip" title="<?php _ex('Server ping', 'setting description', 'ali2woo-lite'); ?>"></div>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php
                            $result = A2WL_SystemInfo::server_ping();
                            echo ($result['state']!=='ok'?'<span class="error">ERROR</span>':'<span class="ok">OK</span>');
                            if(!empty($result['message'])){
                                if ($result['state']!=='ok') {
                                    echo '<div class="row-comments">The error message is: <b>'.$result['message'].'</b>'; 
                                    if(strpos(strtolower($result['message']) , 'curl') !== false) {
                                        echo '<br/>Please contact your server/hosting support and ask why it happens and how to fix the issue';
                                    }
                                    echo '</div>';
                                }else{
                                    echo '<div class="info-box" data-toggle="tooltip" title="'.$result['message'].'"></div>';
                                }
                            }
                            ?>
                        </div>                                                                     
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label>
                            <strong><?php _e('DISABLE_WP_CRON', 'ali2woo-lite'); ?></strong>
                        </label>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php echo (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON)?"Yes":"No";?>
                            <div class="info-box" data-toggle="tooltip" title="<?php _ex('We recommend to disable WP Cron and setup the cron on your server/hosting instead.', 'setting description', 'ali2woo-lite'); ?>"></div>                            
                        </div>                                                                     
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label>
                            <strong><?php _e('PHP DOM', 'ali2woo-lite'); ?></strong>
                        </label>
                        <div class="info-box" data-toggle="tooltip" title="<?php _ex('is there a DOM library', 'setting description', 'ali2woo-lite'); ?>"></div>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php
                            $result = A2WL_SystemInfo::php_dom_check();
                            echo ($result['state']!=='ok'?'<span class="error">ERROR</span>':'<span class="ok">OK</span>');
                            if(!empty($result['message'])){
                                echo '<div class="info-box" data-toggle="tooltip" title="'.$result['message'].'"></div>';
                            }
                            ?>
                        </div>                                                                     
                    </div>
                </div>
            </div>       
        </div>  

        <div class="container-fluid">
            <div class="row pt20 border-top">
                <div class="col-sm-12">
                    <input class="btn btn-success js-main-submit" type="submit" value="<?php _e('Save settings', 'ali2woo-lite'); ?>"/>
                </div>
            </div>
        </div>

    </div>
</form>

<script>
    (function ($) {
        $(function () {
            $('.a2wl-clear-log').click(function () {
                $.post(ajaxurl, {action: 'a2wl_clear_log_file'}).done(function (response) {
                    let json = $.parseJSON(response);
                    if (json.state !== 'ok') { console.log(json); }
                }).fail(function (xhr, status, error) {
                    console.log(error);
                });
                return false;
            });
        });
    })(jQuery);
</script>



