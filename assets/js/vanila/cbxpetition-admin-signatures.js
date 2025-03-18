//import foreach from "../../../../elementor/assets/js/editor";

(function ($) {
    'use strict';
    jQuery(document).ready(function ($) {
        var cbxpetition_awn_options = {
            labels: {
                tip          : cbxpetition_signatures_js_vars.awn_options.tip,
                info         : cbxpetition_signatures_js_vars.awn_options.info,
                success      : cbxpetition_signatures_js_vars.awn_options.success,
                warning      : cbxpetition_signatures_js_vars.awn_options.warning,
                alert        : cbxpetition_signatures_js_vars.awn_options.alert,
                async        : cbxpetition_signatures_js_vars.awn_options.async,
                confirm      : cbxpetition_signatures_js_vars.awn_options.confirm,
                confirmOk    : cbxpetition_signatures_js_vars.awn_options.confirmOk,
                confirmCancel: cbxpetition_signatures_js_vars.awn_options.confirmCancel
            }
        };
        
        //sign edit form submit
        var $sign_form      = $('#cbxpetition_sign_edit_form');
        var $sign_form_busy = Number($sign_form.data('busy'));

        var $sign_form_formvalidator = $sign_form.validate({
            errorPlacement: function (error, element) {
                error.appendTo(element.closest('.cbxpetition-signform-field'));
            },
            errorElement: 'p',
            rules: {},
            messages: {}
        });


        $sign_form.submit(function (e) {

            if ($sign_form_formvalidator.valid()) {
                if (!$sign_form_busy) {
                    e.preventDefault();

                    $sign_form.data('busy', 1);
                    $sign_form.find('.cbxpetition-submit').prop('disabled', true);
                    $sign_form.find('.cbxpetition-submit').addClass('running');


                    var form_data = $sign_form.serialize();

                    // process the form
                    var request = $.ajax({
                        type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                        url: cbxpetition_signatures_js_vars.ajax.ajax_url, // the url where we want to POST
                        data: form_data, // our data object
                        //security: cbxpetition_signatures_js_vars.ajax.ajax_nonce,
                        dataType: 'json' // what type of data do we expect back from the server
                    });

                    request.done(function (data) {
                        if (typeof data.security !== 'undefined' && Number(data.security) === 1) {
                            new AWN().alert(data.message);
                        } else if ($.isEmptyObject(data.error)) {
                            var $success_messages = data.success_arr.messages;
                            var $error_messages   = data.error_arr.messages;

                            $.each($success_messages, function (key, $message) {
                                //awn notification
                                new AWN().success($message.text);
                            });

                            $.each($error_messages, function (key, $message) {
                                //awn notification
                                new AWN().alert($message.text);
                            });

                        } else {
                            // validation error
                            var visible_errors = [];
                            $.each(data.error, function (key, valueObj) {
                                if (key === 'top_errors') {
                                    $.each(valueObj, function (msg_key, message) {
                                        //awn notification
                                        new AWN().alert(message);
                                    });
                                } else {
                                    if ($sign_form.find('#' + key).attr('type') === 'hidden') {
                                        //awn notification
                                        new AWN().alert(valueObj);
                                    } else {
                                        //for regular field show after field
                                        visible_errors[key] = valueObj;
                                    }
                                }

                            });

                            $sign_form_formvalidator.showErrors(visible_errors);
                        }

                        $sign_form.data('busy', 0);
                        $sign_form.find('.cbxpetition-submit').prop('disabled', false);
                        $sign_form.find('.cbxpetition-submit').removeClass('running');
                    });

                    request.fail(function (jqXHR, textStatus) {
                        $sign_form.data('busy', 0);
                        $sign_form.find('.cbxpetition-submit').prop('disabled', false);
                        $sign_form.find('.cbxpetition-submit').removeClass('running');

                        //awn notification
                        new AWN().alert(cbxpetition_signatures_js_vars.ajax.ajax_fail);
                    });
                }//end if ajax and not busy
            }
        });
        //end sign edit form submit

        $('#screen-meta').addClass('cbx-chota cbxpetition-page-wrapper cbxpetition-singatures-wrapper');
        $('.button.action').addClass('button primary');
        $('#search-submit').addClass('button primary');

        var details = [...document.querySelectorAll('details')];
        document.addEventListener('click', function (e) {
            if (details.some(f => f.contains(e.target)).length != 0) {
                details.forEach(f => f.removeAttribute('open'));
            }
        });

        $('#cbxpetition_signs').on('click', '.petition-signature-delete',function (e) {
            e.preventDefault();

            var $this         = $(this);

            var notifier = new AWN(cbxpetition_awn_options);

            var onCancel = () => {
            };

            var onOk = () => {
                var $parent       = $this.closest('.cbxpetition_sign_row');
                var $petition_id  = Number($this.data('petition-id'));
                var $signature_id = Number($this.data('signature-id'));



                $this.data('busy', 1);
                $this.prop('disabled', true);

                var data = {
                    signature_id : $signature_id,
                    action : 'cbxpetition_sign_delete',
                    security : cbxpetition_signatures_js_vars.ajax.ajax_nonce
                }

                var request2 = $.ajax({
                    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                    url: cbxpetition_signatures_js_vars.ajax.ajax_url, // the url where we want to POST
                    data: data, // our data object
                    dataType: 'json' // what type of data do we expect back from the server
                });

                request2.done(function (data) {
                    if (typeof data.security !== 'undefined' && Number(data.security) === 1) {
                        new AWN().alert(data.message);
                        $this.data('busy', 0);
                        $this.prop('disabled', false);
                    } else if(data.errors){
                        data.errors.forEach(function (error, index){
                            new AWN().alert(error);
                        });
                    }
                    else if(data.error){
                        new AWN().alert(data.message);
                    }
                    else{
                        new AWN().success(data.message);
                        // Refresh the page
                        location.reload();
                    }

                    $this.data('busy', 0);
                    $this.prop('disabled', false);
                });

                request2.fail(function (jqXHR, textStatus) {
                    $this.data('busy', 0);
                    $this.prop('disabled', false);

                    //awn notification
                    new AWN().alert(cbxpetition_signatures_js_vars.ajax.ajax_fail);
                });
            };

            notifier.confirm(
                cbxpetition_signatures_js_vars.delete_dialog.are_you_sure_delete_desc,
                onOk,
                onCancel,
                {
                    labels: {
                        confirm: cbxpetition_signatures_js_vars.delete_dialog.are_you_sure_global
                    }
                }
            );
            

        });

        $('#petition_sign_status').on('change', function (e){
                //var $value = this.value;
                $('#cbxpetition_signs').trigger('submit');
        });

        $('#screen-options-apply').addClass('primary').removeClass('button-primary');
    });

})(jQuery);