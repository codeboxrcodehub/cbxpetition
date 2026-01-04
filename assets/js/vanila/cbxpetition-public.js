(function ($) {
    'use strict';

    function htmlspecialchars_decode(string, quoteStyle) { // eslint-disable-line camelcase
        //       discuss at: https://locutus.io/php/htmlspecialchars_decode/
        //      original by: Mirek Slugen
        //      improved by: Kevin van Zonneveld (https://kvz.io)
        //      bugfixed by: Mateusz "loonquawl" Zalega
        //      bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
        //      bugfixed by: Brett Zamir (https://brett-zamir.me)
        //      bugfixed by: Brett Zamir (https://brett-zamir.me)
        //         input by: ReverseSyntax
        //         input by: Slawomir Kaniecki
        //         input by: Scott Cariss
        //         input by: Francois
        //         input by: Ratheous
        //         input by: Mailfaker (https://www.weedem.fr/)
        //       revised by: Kevin van Zonneveld (https://kvz.io)
        // reimplemented by: Brett Zamir (https://brett-zamir.me)
        //        example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES')
        //        returns 1: '<p>this -> &quot;</p>'
        //        example 2: htmlspecialchars_decode("&amp;quot;")
        //        returns 2: '&quot;'

        var optTemp  = 0;
        var i        = 0;
        var noquotes = false;

        if (typeof quoteStyle === 'undefined') {
            quoteStyle = 2;
        }
        string = string.toString()
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>');

        var OPTS = {
            'ENT_NOQUOTES': 0,
            'ENT_HTML_QUOTE_SINGLE': 1,
            'ENT_HTML_QUOTE_DOUBLE': 2,
            'ENT_COMPAT': 2,
            'ENT_QUOTES': 3,
            'ENT_IGNORE': 4
        };

        if (quoteStyle === 0) {
            noquotes = true;
        }
        if (typeof quoteStyle !== 'number') {
            // Allow for a single string or an array of string flags
            quoteStyle = [].concat(quoteStyle);

            for (i = 0; i < quoteStyle.length; i++) {
                // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
                if (OPTS[quoteStyle[i]] === 0) {
                    noquotes = true;
                } else if (OPTS[quoteStyle[i]]) {
                    optTemp = optTemp | OPTS[quoteStyle[i]];
                }
            }
            quoteStyle = optTemp;
        }
        if (quoteStyle & OPTS.ENT_HTML_QUOTE_SINGLE) {
            // PHP doesn't currently escape if more than one 0, but it should:
            string = string.replace(/&#0*39;/g, "'");
            // This would also be useful here, but not a part of PHP:
            // string = string.replace(/&apos;|&#x0*27;/g, "'");
        }
        if (!noquotes) {
            string = string.replace(/&quot;/g, '"');
        }
        // Put this in last place to avoid escape being double-decoded
        string = string.replace(/&amp;/g, '&');

        return string;
    }

    jQuery(document).ready(function ($) {
        var cbxpetition_awn_options = {
            labels: {
                tip: cbxpetition_public_js_vars.awn_options.tip,
                info: cbxpetition_public_js_vars.awn_options.info,
                success: cbxpetition_public_js_vars.awn_options.success,
                warning: cbxpetition_public_js_vars.awn_options.warning,
                alert: cbxpetition_public_js_vars.awn_options.alert,
                async: cbxpetition_public_js_vars.awn_options.async,
                confirm: cbxpetition_public_js_vars.awn_options.confirm,
                confirmOk: cbxpetition_public_js_vars.awn_options.confirmOk,
                confirmCancel: cbxpetition_public_js_vars.awn_options.confirmCancel
            }
        };


        $.extend($.validator.messages, {
            required: cbxpetition_public_js_vars.validation.required,
            remote: cbxpetition_public_js_vars.validation.remote,
            email: cbxpetition_public_js_vars.validation.email,
            url: cbxpetition_public_js_vars.validation.url,
            date: cbxpetition_public_js_vars.validation.date,
            dateISO: cbxpetition_public_js_vars.validation.dateISO,
            number: cbxpetition_public_js_vars.validation.number,
            digits: cbxpetition_public_js_vars.validation.digits,
            creditcard: cbxpetition_public_js_vars.validation.creditcard,
            equalTo: cbxpetition_public_js_vars.validation.equalTo,
            maxlength: $.validator.format(cbxpetition_public_js_vars.validation.maxlength),
            minlength: $.validator.format(cbxpetition_public_js_vars.validation.minlength),
            rangelength: $.validator.format(cbxpetition_public_js_vars.validation.rangelength),
            range: $.validator.format(cbxpetition_public_js_vars.validation.range),
            max: $.validator.format(cbxpetition_public_js_vars.validation.max),
            min: $.validator.format(cbxpetition_public_js_vars.validation.min)
        });

        //for each petition sign form
        $('.cbxpetition_signform_wrapper').each(function (index, elem) {

            var $form_wrapper  = $(elem);
            var $element       = $form_wrapper.find('.cbxpetition-signform');
            //var $ajax         = Number($element.data('ajax'));
            var $busy          = Number($element.data('busy'));
            var $submit_button = '';

            var $formvalidator = $element.validate({
                ignore: [], // This ensures hidden fields are validated
                errorPlacement: function (error, element) {
                    error.appendTo(element.closest('.cbxpetition-signform-field'));
                },
                errorElement: 'p',
                rules: {
                    'cbxpetition-privacy': {
                        required: true
                    }
                },
                messages: {}
            });



            // prevent double click form submission

            $element.on('submit', function (e) {

                var $form = $(this);

                if ($formvalidator.valid()) {
                    if (!$busy) {
                        e.preventDefault();

                        $element.data('busy', 1);
                        $submit_button = $element.find('.cbxpetition-sign-submit');
                        $submit_button.prop('disabled', true);
                        $submit_button.addClass('running');


                        var form_data = $form.serialize();

                        // process the form
                        var request = $.ajax({
                            type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                            url: cbxpetition_public_js_vars.ajaxurl, // the url where we want to POST
                            data: form_data, // our data object
                            //security: cbxpetition_public_js_vars.nonce,
                            dataType: 'json' // what type of data do we expect back from the server
                        });

                        request.done(function (data) {
                            if (typeof data.security !== 'undefined' && Number(data.security) === 1) {
                                new AWN().alert(data.message);
                            } else if ($.isEmptyObject(data.error)) {
                                var $success_messages = data.success_arr.messages;
                                var $error_messages   = data.error_arr.messages;

                                $.each($success_messages, function (key, $message) {
                                    new AWN().success($message.text);
                                });

                                $.each($error_messages, function (key, $message) {
                                    new AWN().alert($message.text);
                                });

                                $element.remove();
                            } else {
                                //validation error
                                var visible_errors = [];
                                $.each(data.error, function (key, valueObj) {
                                    if (key === 'top_errors') {
                                        $.each(valueObj, function (msg_key, message) {
                                            //awn notification
                                            new AWN().alert(message);
                                        });
                                    } else {
                                        if ($element.find('#' + key).attr('type') === 'hidden') {
                                            //for hidden field show at top

                                            //awn notification
                                            new AWN().alert(valueObj);
                                        } else {
                                            //for regular field show after field
                                            /*$form.find('#' + key).addClass('error');
                                            $form.find('#' + key).remove('valid');
                                            var $field_parent = $form.find('#' + key).closest('.cbxpetition-signform-field');
                                            if ($field_parent.find('p.error').length > 0) {
                                                $field_parent.find('p.error').html(valueObj).show();
                                            } else {
                                                $('<p for="' + key + '" class="error">' + valueObj + '</p>').appendTo($field_parent);
                                            }*/

                                            visible_errors[key] = valueObj;
                                        }
                                    }

                                });
                                $formvalidator.showErrors(visible_errors);
                            }

                            $element.data('busy', 0);
                            $submit_button.prop('disabled', false);
                            $submit_button.removeClass('running');
                        });

                        request.fail(function (jqXHR, textStatus) {
                            $element.data('busy', 0);
                            $submit_button.prop('disabled', false);
                            $submit_button.removeClass('running');

                            //awn notification
                            new AWN().alert(cbxpetition_public_js_vars.ajax.ajax_fail);
                        });
                    }//end if ajax and not busy
                }
            });
        }); //end each form

        //add read more to signature text
        new Readmore('.signature-message-readmore', {
            speed: 75,
            moreLink: cbxpetition_public_js_vars.readmore.moreLink,
            lessLink: cbxpetition_public_js_vars.readmore.lessLink,
            collapsedHeight: 100,
            blockProcessed: function (element, yeah) {
                $(element).attr('data-processed', 1);
            }
        });

        $('.cbxpetition_signature_wrapper').on('click', '.cbxpetition_load_more_signs', function (e) {
            e.preventDefault();

            var $this = $(this);

            var $wrapper         = $this.closest('.cbxpetition_signature_wrapper');
            var $listing_wrapper = $wrapper.find('.cbxpetition_signature_items');

            var $petition_id = Number($this.data('petition-id'));
            var $maxpage     = Number($this.data('maxpage'));
            var $page        = Number($this.data('page'));
            var $perpage     = Number($this.data('perpage'));
            var $order       = $this.data('order');

            var $order_by = $this.data('orderby');
            $page++;



            var $busy = Number($this.data('busy'));
            $this.addClass('running');

            if (!$busy) {
                $this.data('busy', 1);

                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: cbxpetition_public_js_vars.ajaxurl,
                    data: {
                        action: 'cbxpetition_load_more_signs',
                        security: cbxpetition_public_js_vars.nonce,
                        petition_id: $petition_id,
                        page: $page,
                        perpage: $perpage,
                        order: $order,
                        orderby: $order_by
                    },
                    success: function (data) {
                        $listing_wrapper.append(data.listing);
                        $this.data('busy', 0);
                        $this.removeClass('running');

                        if ($maxpage === $page) {
                            $this.closest('.cbxpetition_load_more_signs_wrap').remove();
                        } else {
                            $this.data('page', $page);
                        }

                        //apply read more on newly loaded content
                        new Readmore($listing_wrapper.find('.signature-message-readmore[data-processed!="1"]'), {
                            speed: 75,
                            moreLink: cbxpetition_public_js_vars.readmore.moreLink,
                            lessLink: cbxpetition_public_js_vars.readmore.lessLink,
                            collapsedHeight: 100,
                            blockProcessed: function (element, yeah) {
                                $(element).attr('data-processed', 1);
                            }
                        });
                    }
                });
            }


        });


        // frontend signature delete from petition details page (logged-in owner)
        $('.cbxpetition_signature_wrapper').on('click', '.cbxpetition-sign-delete-btn', function (e) {
            e.preventDefault();

            var $this = $(this);

            if (!Number(cbxpetition_public_js_vars.is_user_logged_in)) {
                new AWN().alert(cbxpetition_public_js_vars.logout.confirm_desc);
                return;
            }

            var busy = Number($this.data('busy'));
            if (busy) {
                return;
            }

            var notifier = new AWN(cbxpetition_awn_options);
            var onCancel = () => {};

            var onOk = () => {
                var signatureId = Number($this.data('signature-id'));

                if (!signatureId) {
                    new AWN().alert(cbxpetition_public_js_vars.are_you_sure_delete_desc);
                    return;
                }

                $this.data('busy', 1);
                $this.prop('disabled', true);

                var data = {
                    signature_id: signatureId,
                    action: 'cbxpetition_front_sign_delete',
                    security: cbxpetition_public_js_vars.nonce
                };

                var request = $.ajax({
                    type: 'POST',
                    url: cbxpetition_public_js_vars.ajaxurl,
                    data: data,
                    dataType: 'json'
                });

                request.done(function (data) {
                    if (typeof data.security !== 'undefined' && Number(data.security) === 1) {
                        new AWN().alert(data.message);
                    } else if (data.errors) {
                        data.errors.forEach(function (error) {
                            new AWN().alert(error);
                        });
                    } else if (data.error) {
                        new AWN().alert(data.message);
                    } else {
                        new AWN().success(data.message);

                        var $item = $this.closest('.cbxpetition_signature_item');
                        $item.slideUp(300, function () {
                            $(this).remove();
                        });
                    }

                    $this.data('busy', 0);
                    $this.prop('disabled', false);
                });

                request.fail(function () {
                    $this.data('busy', 0);
                    $this.prop('disabled', false);

                    new AWN().alert(cbxpetition_public_js_vars.ajax.fail);
                });
            };

            notifier.confirm(
                cbxpetition_public_js_vars.are_you_sure_delete_desc,
                onOk,
                onCancel,
                {
                    labels: {
                        confirm: cbxpetition_public_js_vars.are_you_sure_global
                    }
                }
            );
        });


        //add gallery feature to
        $('.cbxpetition_photo_background').venobox({});

        $('.cbxpetition-guest-wrap').on('click', '.cbxpetition-title-login a', function (e) {
            e.preventDefault();

            var $this   = $(this);
            var $parent = $this.closest('.cbxpetition-guest-wrap');
            $parent.find('.cbxpetition-guest-login-wrap').toggle();
        });

        $('.cbxpetition-sign-logout-confirm').on('click', function (e) {
            e.preventDefault();

            var $this = $(this);

            var notifier = new AWN(cbxpetition_awn_options);
            var onCancel = () => {};

            var onOk = () => {
                window.location = $this.attr('href');
            };

            notifier.confirm(
                cbxpetition_public_js_vars.logout.confirm_desc,
                onOk,
                onCancel,
                {
                    labels: {
                        confirm: cbxpetition_public_js_vars.logout.confirm_title
                    }
                }
            );
        });
    });//End dom ready

    //for elementor widget render
    /* $(window).on('elementor/frontend/init', function () {
         elementorFrontend.hooks.addAction('frontend/element_ready/cbxpetition_latest_display.default', function ($scope, $) {
             var $element = $scope.find('.cbxpetition-slider-wrapper');
             if (Number($element.length) > 0) {
                 var $general_config        = $element.data('general-config');
                 var $responsive_config     = $element.data('responsive-config');
                 $general_config.responsive = [$responsive_config];
                 $element.slick($general_config);
             }
         });
     });//end for elementor widget render*/


})(jQuery);
