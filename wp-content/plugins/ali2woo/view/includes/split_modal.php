<style>
.modal-split-product .modal-content{width: 660px;margin-left: -330px;}
.modal-split-product .modal-split-product-loader{display: none;}
.modal-split-product .modal-split-product-content{display: flex;flex-direction:column;}
.modal-split-product .load .modal-split-product-loader{display: block;}
.modal-split-product .load .modal-split-product-content{display: none;}
.modal-split-product .split-title{display: flex;justify-content: space-between;padding-bottom:24px}
.modal-split-product .split-attr{padding: 16px 24px;background: #f8fafb;border: 1px solid #e1e8f0;box-shadow: 0 2px 2px rgba(0,0,0,0.02);border-radius: 4px;margin-top: 16px;}
.modal-split-product .split-attr label{display: flex;align-items: center;margin:0;}
.modal-split-product .split-attr input{margin:0 8px 0 0;outline: none;}
</style>

<div class="modal-overlay modal-split-product">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><?php _e('Split Product', 'ali2woo'); ?></h3>
            <a class="modal-btn-close" href="#"><svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-cross"></use></svg></a>
        </div>
        <div class="modal-body">
            <div class="modal-split-product-loader a2w-load-container" style="padding:80px 0;"><div class="a2w-load-speeding-wheel"></div></div>
            <div class="modal-split-product-content">
                <div class="split-title">
                    <div>Select which option you want to use for splitting the product</div>
                    <!-- <div>...or <a href="#">Split manually</a></div> -->
                </div>
                <div>
                    <b>Split by</b>:
                    <div class="split-attributes"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default modal-close" type="button"><?php _e('Cancel', 'ali2woo'); ?></button>
            <button class="btn btn-success do-split-product" type="button">
                <?php _e('Split to <span class="btn-split-count">0</span> Products', 'ali2woo'); ?>
            </button>
        </div>
    </div>
</div>

