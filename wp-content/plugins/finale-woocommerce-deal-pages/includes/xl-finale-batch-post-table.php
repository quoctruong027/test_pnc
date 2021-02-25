<?php
require_once( __DIR__ . '/wcct-index-campaigns-table.php' );
do_action( 'wcct-deal-before-page' );

?>
<h1 class="wp-heading-inline"><?php _e( 'Index Campaigns', 'finale-woocommerce-deal-pages' ); ?></h1>
<a style="margin-top: 10px;" class="page-title-action wcct_deal_back_to_link" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ); ?>"
><?php _e( 'Finale Campaigns', 'finale-woocommerce-deal-pages' ); ?></a>
<a style="margin-top: 10px;" class="page-title-action xlwcty-a-blue" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=deal_pages&sub_page=shortcode' ); ?>"
><?php _e( 'Deal Pages', 'finale-woocommerce-deal-pages' ); ?></a>
<style> th#xlwcctbatch_check_column {
        width: 31px;
    }

    .batch-picker {
        display: none;
    }

    #wcct-deal-batch-main h1 {
        display: none;
    }

    .batch-overlay__inner h2 {
        display: none;
    }</style>
<div id="poststuff">

    <div class="inside">
        <div id="wcct-deal-batch-main" class="locomotive-form"></div>
        <div class="wcct_options_page_col2_wrap">
            <input type="hidden" name="wcct_deal_batch_process_action" class="wcct_deal_batch_process_action" value="yes">
            <input type="hidden" id="wcct_selected_action_type" class="wcct_selected_action_type" value="">
            <input type="hidden" id="wcct_selected_cp_ids" class="wcct_selected_cp_ids" value="">
            <div class="wcct_options_page_left_wrap">
				<?php
				$table       = new WCCT_Batch_Post_Table();
				$table->data = $this->data;
				$table->prepare_items();
				$table->display();
				?>
            </div>
            <div class="wcct_options_page_right_wrap">
				<?php do_action( 'wcct_deal_page_batch_page_right_content' ); ?>
            </div>

            <script>
                (function ($) {

                    $(window).load(function () {
                        var current_step = 0;
                        var is_bulk_run =<?php echo $this->is_bulk_run ?>;
                        $("#bulk-action-selector-top").on("change", function (e) {
                            var bcl = $(this).val();
                            if (bcl != "") {
                                $(".wcct_deal_batch_process_action").val(bcl);
                            }
                        });
                        $(".cb-select-xlwcctbatch").on("change", function (e) {
                            if ($(this).is(":checked")) {
                                $(".xlwcctbatch_columns").prop("checked", true);
                                $(".cb-select-xlwcctbatch").prop("checked", true);
                            } else {
                                $(".xlwcctbatch_columns").prop("checked", false);
                                $(".cb-select-xlwcctbatch").prop("checked", false);
                            }
                        });


                        if ($("#wcct-deal-batch-main").length > 0) {
                            var all_ready_running = 0;
                            $(".wcct_deal_run_index").on("click", function (e) {
                                e.preventDefault();
                                $(this).siblings("img").eq(0).show();
                                var ac = $(this).attr("data-action");
                                var type = $(this).attr("data-type");
                                var cpid = $(this).attr("data-id");

                                $("#wcct_selected_cp_ids").val(cpid);
                                $("#wcct_selected_action_type").val(type);
                                $(".wcct_deal_add_index").prop("disabled", true);
                                $(".bulkactions input[type='submit']").prop("disabled", true);
                                $.ajax({
                                    url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
                                    method: "post",
                                    data: {
                                        action: "wcct_deal_deindex_batch",
                                        cp_id: cpid
                                    },
                                    success: function (resp) {
                                        //console.log(ac);
                                        if (ac != "" && (type == 3 || type == 1)) {

                                            $("#" + ac).prop("checked", true).siblings("label").trigger("click");
                                            $("#wcct-deal-batch-main").find(".xl_batch_locomotive").trigger("click");
                                        }
                                    }
                                });

                            });
                            if (is_bulk_run == 1) {
                                $(".wcct_deal_add_index").prop("disabled", true);
                                $(".bulkactions input[type='submit']").prop("disabled", true);
                                $("#wcct-index-all-campaign").prop("checked", true).siblings("label").trigger("click");
                                $("#wcct-deal-batch-main").find(".xl_batch_locomotive").trigger("click");
                            }

                            $(".wcct_deal_run_deindex").on("click", function (e) {
                                var nthis = $(this);
                                nthis.prop("disabled", true);
                                $(this).siblings("img").eq(0).show();
                                var old_val = nthis.val();
                                nthis.val("Processing..");
                                var cpid = nthis.attr("data-id");
                                $.ajax({
                                    url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
                                    method: "post",
                                    data: {
                                        action: "wcct_deal_deindex_batch",
                                        cp_id: cpid
                                    },
                                    success: function (resp) {
                                        // nthis.prop("disabled", false);
                                        window.location.reload(true);
                                        nthis.val(old_val);
                                    }
                                })
                            });
                            $(document).on("wcct_deal_batch_processing_data", function (e, v) {
                                if (v.hasOwnProperty("progress")) {
                                    if (v.progress == 100) {
                                        // $(".wcct_deal_add_index").prop("disabled", false);
                                        //  $(".bulkactions input[type='submit']").prop("disabled", false);
                                        window.location.reload(true);
                                    }


                                    if (current_step < v.progress) {
                                        current_step = v.progress;
                                        $(".finale_deal_page_batch_status").html("Status: Running");

                                        if ($(".finale_deal_page_batch_status").next(".after_text").length === 0) {


                                            $('<p class="after_text"></p>').insertAfter($(".finale_deal_page_batch_status"));
                                        }

                                        var step_passed = 0;
                                        if ((v.current_step * wcct_deal_batch.posts_per_page) > v.total_num_results) {
                                            step_passed = v.total_num_results;
                                        } else {
                                            step_passed = (v.current_step * wcct_deal_batch.posts_per_page);
                                        }
                                        $('.after_text').text('Products Processed: ' + (step_passed) + '/' + v.total_num_results);

                                        $(".finale_deal_page_batch_status").removeClass("finale_deal_batch_process_halted");
                                    }
                                }
                            });

                            $(document).on("wcct_deal_batch_processing_failed", function (e, v) {
                                if (v.current_step > 0) {
                                    var loco = v.obj;
                                    $(".finale_deal_page_batch_status").html("Try to reinitiate from previous state")
                                    $(".finale_deal_page_batch_status").addClass("finale_deal_batch_process_halted");
                                    loco.runBatch(v.current_step);
                                }
                            });
                        }
                    });
                })(jQuery);
            </script>
        </div>
    </div>

</div>