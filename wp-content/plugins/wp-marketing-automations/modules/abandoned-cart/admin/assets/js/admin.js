(function ($) {
    if (typeof bwfan_abandoned_cart_data !== 'undefined') {
        var chartColors = {
            "red": "rgb(255, 99, 132)",
            "orange": "rgb(255, 159, 64)",
            "yellow": "rgb(255, 205, 86)",
            "green": "rgb(75, 192, 192)",
            "blue": "rgb(54, 162, 235)",
            "purple": "rgb(153, 102, 255)",
            "grey": "rgb(201, 203, 207)",
            "black": "rgb(0, 0, 0)"
        };

        var cart_config = {
            type: 'line',
            data: {
                labels: bwfan_abandoned_cart_data.labels,
                datasets: [{
                    label: bwfan_abandoned_cart_data.line_label_1,
                    backgroundColor: chartColors.red,
                    borderColor: chartColors.red,
                    data: bwfan_abandoned_cart_data.data,
                    fill: false,
                }, {
                    label: bwfan_abandoned_cart_data.line_label_2,
                    fill: false,
                    backgroundColor: chartColors.blue,
                    borderColor: chartColors.blue,
                    data: bwfan_abandoned_cart_data.data_1,

                }, {
                    label: bwfan_abandoned_cart_data.line_label_3,
                    fill: false,
                    backgroundColor: chartColors.black,
                    borderColor: chartColors.black,
                    data: bwfan_abandoned_cart_data.data_2,

                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: false,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: false,
                            labelString: 'Value'
                        }
                    }]
                }
            }
        };
        var config_revenue = {
            type: 'line',
            data: {
                labels: bwfan_abandoned_cart_data.labels,
                datasets: [{
                    label: bwfan_abandoned_cart_data.line_revenue_1,
                    backgroundColor: chartColors.red,
                    borderColor: chartColors.red,
                    data: bwfan_abandoned_cart_data.revenue,
                    fill: false,
                }, {
                    label: bwfan_abandoned_cart_data.line_revenue_2,
                    fill: false,
                    backgroundColor: chartColors.blue,
                    borderColor: chartColors.blue,
                    data: bwfan_abandoned_cart_data.revenue_1,

                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: false,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: false,
                            labelString: 'Value'
                        }
                    }]
                }
            }
        };
    }
    $(window).on('load', function () {
        if ($('#bwfan_abandoned_chart_revenue').length > 0) {
            let element = document.getElementById('bwfan_abandoned_chart_revenue');
            let ctx = element.getContext('2d');
            element.height = 350;
            new Chart(ctx, config_revenue);
        }

        if ($('#bwfan_abandoned_chart').length > 0) {
            let element = document.getElementById('bwfan_abandoned_chart');
            let ctx = element.getContext('2d');
            element.height = 350;
            new Chart(ctx, cart_config);
        }

        let date_range = $(".wfacp_date_range");
        if (date_range.length > 0) {
            $('#date_range_first').datepicker({'dateFormat': 'yy-mm-dd', 'maxDate': 0});
            $('#date_range_second').datepicker({'dateFormat': 'yy-mm-dd', 'maxDate': 0});
        }
    });

    $(document).on('click', '.bwfanc_default_custom', function (e) {
        e.preventDefault();
        $(".wfacp_date_rage_container").toggleClass('bwfanc_hide_date_range_search_form');
    });

    $(document).on('click', '.bwfan_rerun_automations', function (e) {
        var cart_id = $(this).data('id');
        let ajax = new bwf_ajax();
        var ajax_data = {
            'cart_id': cart_id,
            '_wpnonce': bwfanParams.ajax_nonce,
        };

        swal({
            title: bwfan_ab_carts_data.rerun_automation_loading_label,
            text: bwfan_ab_carts_data.rerun_automation_loading_text,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
        });
        swal.showLoading();

        ajax.ajax('rerun_automations', ajax_data);
        ajax.success = function (result) {
            swal.close();
            swal({
                title: bwfan_ab_carts_data.rerun_automation_successful_label,
                text: '',
                type: "success",
            });
            setTimeout(function () {
                window.location.href = window.location.href;
            }, 2000);
        };

        return false;
    });

})(jQuery);
