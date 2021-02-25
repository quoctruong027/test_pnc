<form method="post">
    <div class="system_info">
        <div class="panel panel-primary mt20">
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label>
                            <strong><?php _e('Server address', 'ali2woo'); ?></strong>
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
                            <strong><?php _e('Php version', 'ali2woo'); ?></strong>
                        </label>
                        <div class="info-box" data-toggle="tooltip" title="<?php _ex('Php version', 'setting description', 'ali2woo'); ?>"></div>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php
                            $result = A2W_SystemInfo::php_check();
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
                            <strong><?php _e('Php config', 'ali2woo'); ?></strong>
                        </label>
                    </div>
                    
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="php_ini_check_row">
                            <span>allow_url_fopen :</span>
                            <?php if(ini_get('allow_url_fopen')):?>
                                <span class="ok">On</span>
                            <?php else: ?>
                                <span class="error">Off</span><div class="info-box" data-toggle="tooltip" title="<?php _e('There may be problems with the image editor', 'ali2woo');?>"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label>
                            <strong><?php _e('Site ping', 'ali2woo'); ?></strong>
                        </label>
                        <div class="info-box" data-toggle="tooltip" title="<?php _ex('Site ping', 'setting description', 'ali2woo'); ?>"></div>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php
                            $result = A2W_SystemInfo::ping();
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
                            <strong><?php _e('Server ping', 'ali2woo'); ?></strong>
                        </label>
                        <div class="info-box" data-toggle="tooltip" title="<?php _ex('Server ping', 'setting description', 'ali2woo'); ?>"></div>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php
                            $result = A2W_SystemInfo::server_ping();
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
                            <strong><?php _e('DISABLE_WP_CRON', 'ali2woo'); ?></strong>
                        </label>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php echo (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON)?"Yes":"No";?>
                            <div class="info-box" data-toggle="tooltip" title="<?php _ex('We recommend to disable WP Cron and setup the cron on your server/hosting instead.', 'setting description', 'ali2woo'); ?>"></div>                            
                        </div>                                                                     
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-4 col-lg-2">
                        <label>
                            <strong><?php _e('PHP DOM', 'ali2woo'); ?></strong>
                        </label>
                        <div class="info-box" data-toggle="tooltip" title="<?php _ex('is there a DOM library', 'setting description', 'ali2woo'); ?>"></div>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-lg-10">
                        <div class="form-group input-block no-margin clearfix">
                            <?php
                            $result = A2W_SystemInfo::php_dom_check();
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
    </div>
</form>

<script>
    (function ($) {
        $(function () {
            
        });
    })(jQuery);
</script>



