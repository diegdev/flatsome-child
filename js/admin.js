/**
 * Project pack admin javascript
 *
 * @subpackage greensociety
 * @author Beplus
 */

;
(function(w, $) {
    'use strict';

    var update_status_order = function() {
      // order row click
        jQuery('body').on('click', 'tr.type-shop_order', function(e) {
            //e.preventDefault();
            //e.stopPropagation();
        })
        jQuery('body').on('click', '.complete_order', function(e) {
            e.preventDefault();
            var _this = $(this);
            _this.text('Processing...');
            var order_id = $(this).data('order_id');
            var data = {
                action: "completed_order",
                order_id: order_id,
            };
            $.ajax({
                type: "POST",
                url: pp_php_admin_data.ajax_url,
                data: data,
                success: function(code) {
                    _this.text(code);
                    jQuery('#tmpl-popup_template').removeClass('__active');
                }
            });
        })
    }


    var update_barcode_tracking_number = function() {
        jQuery('body').on('click', '.save_tracking', function(e) {
            var _this = $(this);
            _this.text('Processing...');
            var order_id = $(this).data('order_id');
            var tracking_number = $('input[name="tracking_number"]').val();
            if (tracking_number == '') {
                alert('Please enter tracking number');
                return;
            }
            var data = {
                action: "save_tracking_order",
                order_id: order_id,
                tracking_number: tracking_number,
            };
            $.ajax({
                type: "POST",
                url: pp_php_admin_data.ajax_url,
                data: data,
                success: function(code) {
                    _this.text('Successfully');
                }
            });
        })
    }

    var barcode_print_labels = function() {
        // remove any previous print contents first!
        $('#wclabels-print-content').remove();
        // create iframe for print content
        var iframe = '<iframe id="wclabels-print-content" name="wclabels-print-content" style="position:absolute;top:-9999px;left:-9999px;border:0px;overfow:none; z-index:-1"></iframe>';
        $('body').append(iframe);

        // open the link in a new tab when preview enabled or iframe printing is not supported
        var unsupported_browser = ($.browser.opera || ($.browser.msie && parseInt($.browser.version) > 9));
        var preview = wclabels.preview;
        if (unsupported_browser || preview == 'true') {
            var target = '_blank';
        } else {
            var target = 'wclabels-print-content';
        }
        var target = 'wclabels-print-content';
        // create form to send order_ids via POST
        $('#wclabels-post-form').remove();
        var request_prefix = (wc_order_barcodes.ajaxurl.indexOf("?") != -1) ? '&' : '?';
        var url = wc_order_barcodes.ajaxurl + request_prefix + '&action=wpo_wclabels_print&post_type=' + wclabels.post_type + '&_wpnonce=' + wclabels.nonce;
        $('body').append('<form action="' + url + '" method="post" target="' + target + '" id="wclabels-post-form"></form>');
        $('#wclabels-post-form').append('<input type="hidden" name="order_ids" class="order_ids"/>');
        $('#wclabels-post-form input.order_ids').val(window.order_ids);
        $('#wclabels-post-form').append('<input type="hidden" name="offset" class="offset" value="' + window.offset + '"/>');

        // submit order_ids to preview or iframe
        $('#wclabels-post-form').submit();
    }

    var print_address_lable = function() {
        jQuery('body').on('click', '.print_label_order', function(e) {
            var _this = $(this);
            var order_id = $(this).data('order_id');
            window.order_ids = [order_id];
            window.offset = '';
            barcode_print_labels();
        })
    }

    /**
     * DOM ready
     */
    $(function() {

    })

    /**
     * Browser load completed
     */
    $(w).on('load', function() {
        update_status_order();
        update_barcode_tracking_number();
        print_address_lable();

    })
})(window, jQuery)
