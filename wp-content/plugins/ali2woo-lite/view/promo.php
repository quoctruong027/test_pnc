<div class="panel panel-default small-padding margin-top">   
    <div class="panel-body">
        <div class="container-flex flex-between"> 
            <div class="container-flex">
                <img class="display-block margin-right" height="24" src="<?php echo A2WL()->plugin_url() . '/assets/img/logo_ali2woo.png'; ?>" alt="chrome extension">
                <span class="display-block"><strong><?php _e('To use all the powerful import functions, get the full version of Ali2Woo plugin! ', 'ali2woo-lite'); ?></strong>
                <a href="https://ali2woo.com/?utm_source=ali2woo_lite">Learn more.</a>
                </span>
            </div>
            <div class="container-flex">
                <a class="btn btn-primary btn-sm chrome-install mr10" target="_blank" href="https://ali2woo.com/?utm_source=ali2woo_lite"><?php _e('Get full version of Ali2Woo', 'ali2woo-lite'); ?></a>
                <a href="#" class="btn-link small promo-close" alt="<?php _e('Close'); ?>">
                    <svg class="icon-small-cross"> 
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-small-cross"></use>
                    </svg>
                </a>
            </div>
        </div>
    </div>
    <script>(function ($) {
            $('.promo-close').click(function () {$(this).closest('.panel').remove();return false;});
        })(jQuery);</script>
</div>
