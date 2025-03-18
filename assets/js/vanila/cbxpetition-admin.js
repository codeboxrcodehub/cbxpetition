(function ($) {
    'use strict';

    function cbxpetition_copyStringToClipboard (str) {
        // Create new element
        var el = document.createElement('textarea');
        // Set value (string to be copied)
        el.value = str;
        // Set non-editable to avoid focus and move outside of view
        el.setAttribute('readonly', '');
        el.style = {position: 'absolute', left: '-9999px'};
        document.body.appendChild(el);
        // Select text inside element
        el.select();
        // Copy text to clipboard
        document.execCommand('copy');
        // Remove temporary element
        document.body.removeChild(el);
    }//end function cbxpetition_copyStringToClipboard

    jQuery(document).ready(function ($) {
        $('#title-prompt-text').removeClass('screen-reader-text');
        $('#title-prompt-text').text(cbxpetition_admin_js_vars.petition_title_label);
        $('#title').attr('placeholder', cbxpetition_admin_js_vars.petition_title_placeholder);

        var cbxpetition_awn_options = {
            labels: {
                tip          : cbxpetition_admin_js_vars.awn_options.tip,
                info         : cbxpetition_admin_js_vars.awn_options.info,
                success      : cbxpetition_admin_js_vars.awn_options.success,
                warning      : cbxpetition_admin_js_vars.awn_options.warning,
                alert        : cbxpetition_admin_js_vars.awn_options.alert,
                async        : cbxpetition_admin_js_vars.awn_options.async,
                confirm      : cbxpetition_admin_js_vars.awn_options.confirm,
                confirmOk    : cbxpetition_admin_js_vars.awn_options.confirmOk,
                confirmCancel: cbxpetition_admin_js_vars.awn_options.confirmCancel
            }
        };

        var cbxpetition_dashboard_tab = '';
        var $cbxpetition_petition_id  = 0;
        var $cbxpetition_tab_wrap     = $('#cbxpetition_dashboard_tab_wrap');
        var $cbxpetition_tabs         = $('#cbxpetition_dashboard_tabs');
        var $cbxpetition_tab_contents = $('#cbxpetition_dashboard_tab_contents');
        //var cbxpetition_dashboard_action_buttons = $('#cbxpetition_dashboard_action_buttons');
        $cbxpetition_petition_id      = Number($cbxpetition_tabs.data('petition-id'));

        //log edit with attachment related
        $cbxpetition_tabs.on('click', 'a', function (e) {
            e.preventDefault();

            var $this    = $(this);
            var $this_id = $this.attr('href');

            $cbxpetition_tabs.find('a').removeClass('active');
            $cbxpetition_tab_contents.find('.cbxpetition_dashboard_content_tab').removeClass('active');

            $this.addClass('active');
            $cbxpetition_tab_contents.find($this_id).addClass('active');
        });

        //petition edit mode
        if (Number(cbxpetition_admin_js_vars.petition_edit_mode)) {
            $('#cbxpetition_dashboard_edit_expire_date').flatpickr({
                disableMobile: 'true',
                // minDate      : new Date(),
                enableTime   : true,
                dateFormat   : 'Y-m-d H:i',
                time_24hr    : true,
                defaultHour  : 0,
                defaultMinute: 0
            });

            var $form_meta_media         = $('#cbxpetition_meta_media_photo');
            var $cbxpetition_petition_id = Number($form_meta_media.data('petition_id'));

            //photos and banner
            var $photos_wrapper   = $('#petition_photos_wrapper');
            var $photos_max_files = parseInt(cbxpetition_admin_js_vars.photo.max_files);
            var $petition_photos  = $('#petition_photos');
            var $photos_uploaded  = $petition_photos.data('file_count');

            if ($photos_uploaded >= $photos_max_files) {
                $('#petition_photo_uploader').hide();
            }


            $('#petition_photo_uploader').dmUploader({
                url             : cbxpetition_admin_js_vars.ajax.ajax_url,
                maxFileSize     : cbxpetition_admin_js_vars.photo.max_filesize,
                allowedTypes    : cbxpetition_admin_js_vars.photo.file_types,
                extFilter       : cbxpetition_admin_js_vars.photo.file_exts,
                queue           : true,
                auto            : true,
                multiple        : true,
                dnd             : true,
                fieldName       : 'images',
                extraData       : {
                    'action'     : 'petition_admin_photo_upload',
                    'security'   : cbxpetition_admin_js_vars.ajax.ajax_nonce,
                    'petition_id': $cbxpetition_petition_id,
                },
                onInit          : function () {
                    //console.log('Callback: Plugin initialized');
                },
                onDragEnter     : function () {
                    // Happens when dragging something over the DnD area
                    this.addClass('active');
                },
                onDragLeave     : function () {
                    // Happens when dragging something OUT of the DnD area
                    this.removeClass('active');
                },
                onComplete      : function () {
                    // All files in the queue are processed (success or error)
                },
                onNewFile       : function (id, file) {
                    // When a new file is added using the file selector or the DnD area

                    if (typeof FileReader !== 'undefined') {
                        var reader    = new FileReader();
                        var photo_div = $('<div class="petition_photo petition_photo_preview" />');

                        reader.onload = function (e) {
                            photo_div.css({'background-image': 'url("' + e.target.result + '")'});
                            $petition_photos.append(photo_div);

                        }
                        /* ToDo: do something with the img! */
                        reader.readAsDataURL(file);
                    }
                },
                onBeforeUpload  : function (id) {
                    // about tho start uploading a file
                },
                onUploadCanceled: function (id) {
                    // Happens when a file is directly canceled by the user.
                },
                onUploadProgress: function (id, percent) {
                    // Updating file progress

                },
                onUploadSuccess : function (id, data) {
                    // A file was successfully uploaded

                    if (data.error) {
                        $petition_photos.find('.petition_photo:last-child').remove();
                        new AWN().alert(data.msg);
                    } else {
                        $photos_uploaded++;
                        if ($photos_uploaded >= $photos_max_files) {
                            $('#petition_photo_uploader').hide();
                        }

                        $petition_photos.find('.petition_photo.petition_photo_preview:last-child').removeClass('petition_photo_preview').css(
                            {'background-image': 'url("' + data.thumb_url + '")'}
                        ).append('<span class="petition_photo_delete" style="text-align: center;"><a data-file="' + data.name + '" class="button primary petition_photo_delete_button" href="#">' + cbxpetition_admin_js_vars.delete_dialog.delete + '</a></span>');

                        new AWN().success(data.msg);
                    }
                },
                onUploadError   : function (id, xhr, status, message) {
                    //console.log(message);
                    $petition_photos.last().remove();
                },
                onFallbackMode  : function () {
                    // When the browser doesn't support this plugin :(
                    //ui_add_log('Plugin cant be used here, running Fallback callback', 'danger');
                },
                onFileSizeError : function (file) {
                    new AWN().alert(cbxpetition_admin_js_vars.photo.error_wrong_file_size);
                },
                onFileExtError  : function (file) {
                    new AWN().alert(cbxpetition_admin_js_vars.photo.error_wrong_file_ext);
                },
                onFileTypeError : function (file) {
                    new AWN().alert(cbxpetition_admin_js_vars.photo.error_wrong_file_type);
                }
            });


            //delete single photo
            $petition_photos.on('click', '.petition_photo_delete_button', function (e) {
                e.preventDefault();

                var $this      = $(this);
                var $file_name = $this.data('file');


                var notifier = new AWN(cbxpetition_awn_options);
                var onCancel = () => {
                };

                var onOk = () => {
                    var formData = new FormData();
                    formData.append('petition_id', $cbxpetition_petition_id); // This will get the first image
                    formData.append('security', cbxpetition_admin_js_vars.ajax.ajax_nonce); // This will get the first image
                    formData.append('action', 'petition_admin_photo_delete'); // This will get the first image
                    formData.append('filename', $file_name); // This will get the first image

                    $.ajax({
                        type       : 'POST',
                        url        : cbxpetition_admin_js_vars.ajax.ajax_url,
                        data       : formData,
                        processData: false,
                        contentType: false,
                        cache      : false,
                        //contentType: 'multipart/form-data',
                        //enctype   : 'multipart/form-data',
                        /*beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', cbxpetition_admin_js_vars.ajax.ajax_nonce);
                        },*/
                        success: function (response) {
                            //console.log(response);

                            if (response.error) {
                                new AWN().alert(response.msg);
                            } else {
                                $photos_uploaded--;
                                //$photos_uploaded = 0;
                                //$photos_uploader.data('jqueryUploader').clean();

                                //$petition_photos.empty();
                                $this.closest('.petition_photo').remove();
                                if ($photos_uploaded < $photos_max_files) {
                                    $('#petition_photo_uploader').show();
                                }

                                new AWN().success(response.msg);
                            }

                        }
                    });
                };

                notifier.confirm(
                    cbxpetition_admin_js_vars.delete_dialog.are_you_sure_delete_desc,
                    onOk,
                    onCancel,
                    {
                        labels: {
                            confirm: cbxpetition_admin_js_vars.delete_dialog.are_you_sure_global
                        }
                    }
                );
            });

            //delete all photos at once
            $photos_wrapper.on('click', '.petition_photos_delete', function (e) {
                e.preventDefault();

                var $this = $(this);


                var notifier = new AWN(cbxpetition_awn_options);
                var onCancel = () => {
                };

                var onOk = () => {
                    var formData = new FormData();
                    formData.append('petition_id', $cbxpetition_petition_id); // This will get the first image
                    formData.append('security', cbxpetition_admin_js_vars.ajax.ajax_nonce); // This will get the first image
                    formData.append('action', 'petition_admin_photos_delete'); // This will get the first image

                    $.ajax({
                        type       : 'POST',
                        url        : cbxpetition_admin_js_vars.ajax.ajax_url,
                        data       : formData,
                        processData: false,
                        contentType: false,
                        cache      : false,
                        //contentType: 'multipart/form-data',
                        //enctype   : 'multipart/form-data',
                        /*beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', cbxpetition_admin_js_vars.ajax.ajax_nonce);
                        },*/
                        success: function (response) {

                            if (response.error) {
                                new AWN().alert(response.msg);
                            } else {
                                $photos_uploaded = 0;
                                //$photos_uploader.data('jqueryUploader').clean();


                                $('#petition_photo_uploader').show();

                                $petition_photos.empty();
                                new AWN().success(response.msg);
                            }

                        }
                    });
                };

                notifier.confirm(
                    cbxpetition_admin_js_vars.delete_dialog.are_you_sure_delete_desc,
                    onOk,
                    onCancel,
                    {
                        labels: {
                            confirm: cbxpetition_admin_js_vars.delete_dialog.are_you_sure_global
                        }
                    }
                );
            });


            var $banner_uploader = $('#petition_banner_uploader');

            $banner_uploader.dmUploader({
                url             : cbxpetition_admin_js_vars.ajax.ajax_url,
                maxFileSize     : cbxpetition_admin_js_vars.banner.max_filesize,
                allowedTypes    : cbxpetition_admin_js_vars.banner.file_types,
                extFilter       : cbxpetition_admin_js_vars.banner.file_exts,
                queue           : true,
                auto            : true,
                multiple        : false,
                dnd             : true,
                fieldName       : 'images',
                extraData       : {
                    'action'     : 'petition_admin_banner_upload',
                    'security'   : cbxpetition_admin_js_vars.ajax.ajax_nonce,
                    'petition_id': $cbxpetition_petition_id,
                },
                onInit          : function () {
                    //console.log('Callback: Plugin initialized');
                },
                onDragEnter     : function () {
                    // Happens when dragging something over the DnD area
                    this.addClass('active');
                },
                onDragLeave     : function () {
                    // Happens when dragging something OUT of the DnD area
                    this.removeClass('active');
                },
                onComplete      : function () {
                    // All files in the queue are processed (success or error)
                    //ui_add_log('All pending tranfers finished');
                },
                onNewFile       : function (id, file) {
                    if (typeof FileReader !== 'undefined') {
                        var reader = new FileReader();

                        reader.onload = function (e) {
                            $banner_uploader.css({'background-image': 'url("' + e.target.result + '")'});
                            $banner_uploader.addClass('petition_banner_preview');
                            $banner_uploader.removeClass('petition_banner_exists');
                        }

                        reader.readAsDataURL(file);
                    }
                },
                onBeforeUpload  : function (id) {
                    // about tho start uploading a file
                },
                onUploadCanceled: function (id) {
                    // Happens when a file is directly canceled by the user.
                },
                onUploadProgress: function (id, percent) {
                    // Updating file progress
                    //ui_multi_update_file_progress(id, percent);
                },
                onUploadSuccess : function (id, data) {
                    // A file was successfully uploaded

                    if (data.error) {
                        $banner_uploader.removeAttr('style');
                        $banner_uploader.removeClass('petition_banner_preview');
                        $banner_uploader.removeClass('petition_banner_exists');

                        new AWN().alert(data.msg);
                    } else {
                        $banner_uploader.removeClass('petition_banner_preview');
                        $banner_uploader.css({'background-image': 'url("' + data.url + '")'});
                        $banner_uploader.addClass('petition_banner_exists');

                        new AWN().success(data.msg);
                    }
                },
                onUploadError   : function (id, xhr, status, message) {
                    //console.log(message);
                },
                onFallbackMode  : function () {
                    // When the browser doesn't support this plugin
                },
                onFileSizeError : function (file) {
                    new AWN().alert(cbxpetition_admin_js_vars.banner.error_wrong_file_size);
                },
                onFileExtError  : function (file) {
                    new AWN().alert(cbxpetition_admin_js_vars.banner.error_wrong_file_ext);
                },
                onFileTypeError : function (file) {
                    new AWN().alert(cbxpetition_admin_js_vars.banner.error_wrong_file_type);
                }
            });

            //delete single photo
            $banner_uploader.on('click', '.petition_banner_delete', function (e) {
                e.preventDefault();

                var $this = $(this);

                var notifier = new AWN(cbxpetition_awn_options);
                var onCancel = () => {
                };

                var onOk = () => {
                    var formData = new FormData();
                    formData.append('petition_id', $cbxpetition_petition_id); // This will get the first image
                    formData.append('security', cbxpetition_admin_js_vars.ajax.ajax_nonce); // This will get the first image
                    formData.append('action', 'petition_admin_banner_delete'); // This will get the first image
                    //formData.append('filename', $file_name); // This will get the first image

                    $.ajax({
                        type       : 'POST',
                        url        : cbxpetition_admin_js_vars.ajax.ajax_url,
                        data       : formData,
                        processData: false,
                        contentType: false,
                        cache      : false,
                        //contentType: 'multipart/form-data',
                        //enctype   : 'multipart/form-data',
                        /*beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', cbxpetition_admin_js_vars.ajax.ajax_nonce);
                        },*/
                        success: function (response) {
                            if (response.error) {
                                new AWN().alert(response.msg);
                            } else {

                                $banner_uploader.removeAttr('style');
                                $banner_uploader.removeClass('petition_banner_preview');
                                $banner_uploader.removeClass('petition_banner_exists');

                                new AWN().success(response.msg);
                            }

                        }
                    });
                };

                notifier.confirm(
                    cbxpetition_admin_js_vars.delete_dialog.are_you_sure_delete_desc,
                    onOk,
                    onCancel,
                    {
                        labels: {
                            confirm: cbxpetition_admin_js_vars.delete_dialog.are_you_sure_global
                        }
                    }
                );
            });
            //photos and banner end

            //recipients
            var recipientlists_template = $('#cbx_recipientlists_template').html();
            Mustache.parse(recipientlists_template);   // optional, speeds up future uses


            //add new recipient row
            var $cbxpetition_letter_section = $('.cbxpetition_letter_section');

            var $cbxpetition_repeat_fields_recipient = $('#cbxpetition_repeat_fields_recipient');

            $cbxpetition_letter_section.on('click', '.cbxpetition_add_recipient', function (e) {
                e.preventDefault();

                var $this           = $(this);
                var $recipientcount = Number($this.data('recipientcount'));

                //add a new blank recipient row
                var recipientlists_template_rendered = Mustache.render(recipientlists_template, {index: $recipientcount});
                $cbxpetition_repeat_fields_recipient.append(recipientlists_template_rendered);

                //now increase the count
                $recipientcount++;
                $this.data('recipientcount', $recipientcount);
            });

            //delete recipient row
            $cbxpetition_letter_section.on('click', '.recipient_delete_icon', function (e) {
                e.preventDefault();

                var $this = $(this);

                var notifier = new AWN(cbxpetition_awn_options);
                var onCancel = () => {
                };

                var onOk = () => {
                    $this.parents('.recipientlist_wrap').remove();

                    $this.parents('.recipientlist_wrap').fadeOut('slow', function () {
                        $(this).remove();
                    });
                };

                notifier.confirm(
                    cbxpetition_admin_js_vars.delete_dialog.are_you_sure_delete_desc,
                    onOk,
                    onCancel,
                    {
                        labels: {
                            confirm: cbxpetition_admin_js_vars.delete_dialog.are_you_sure_global
                        }
                    }
                );
            });

            //sort recipient and photos
            var adjustment_recipient;
            $('#cbxpetition_repeat_fields_recipient').sortable({
                vertical: true,
                handle  : '.move-recipient',
                //containerSelector: '#cbxpetition_repeat_fields_recipient',
                containerSelector: 'ul',
                itemSelector     : 'li',
                //placeholder      : '<li class="cbxpetition_repeat_field_recipient_placeholder"/>',
                placeholder: 'cbxpetition_repeat_field_recipient_placeholder',
                // animation on drop
                onDrop: function ($item, container, _super) {
                    var $clonedItem = $('<li />').css({height: 0});
                    $item.before($clonedItem);
                    $clonedItem.animate({'height': $item.height()});

                    $item.animate($clonedItem.position(), function () {
                        $clonedItem.detach();
                        _super($item, container);
                    });
                },

                // set $item relative to cursor position
                onDragStart: function ($item, container, _super) {
                    var offset  = $item.offset(),
                        pointer = container.rootGroup.pointer;

                    adjustment_recipient = {
                        left: pointer.left - offset.left,
                        top : pointer.top - offset.top
                    };

                    _super($item, container);
                },
                onDrag     : function ($item, position) {
                    $item.css({
                        left: position.left - adjustment_recipient.left,
                        top : position.top - adjustment_recipient.top
                    });
                }

            });
            //end recipients
        }//end petition edit
        else {
            //petition listing mode
        }

        //click to copy shortcode
        $('.cbxballon_ctp').on('click', function (e) {
            e.preventDefault();

            var $this = $(this);
            cbxpetition_copyStringToClipboard($this.prev('.cbxshortcode').text());

            $this.attr('aria-label', cbxpetition_admin_js_vars.copycmds.copied_tip);

            window.setTimeout(function () {
                $this.attr('aria-label', cbxpetition_admin_js_vars.copycmds.copy_tip);
            }, 1000);
        });


        $('.wrap').addClass('cbx-chota cbxpetition-page-wrapper cbxpetition-addedit-wrapper');
        $('#search-submit').addClass('button primary');
        $('#post-query-submit').addClass('button primary');
        //$('.button.action').addClass('button outline primary');
        $('.button.action').addClass('button primary');



        $('.page-title-action').addClass('button primary');
        $('#save-post').addClass('button primary');
        //$('#doaction').addClass('button primary');
        $('#publish').addClass('button primary');

        $(cbxpetition_admin_js_vars.global_setting_link_html).insertAfter('.page-title-action');
        $('#screen-meta').addClass('cbx-chota cbxpetition-page-wrapper cbxpetition-petitions-wrapper');
        $('#screen-options-apply').addClass('primary');
        $('#post-search-input').attr('placeholder', cbxpetition_admin_js_vars.placeholder.search);

        $('.button.button-primary.save').addClass('primary');
        $('.button.cancel').addClass('outline primary');

        //edit
        $('.preview.button').addClass('button outline secondary');
        $('.button.tagadd').addClass('button outline secondary');
        $('.edit-slug.button.button-small').addClass('button outline secondary small');
        $('#remove-post-thumbnail').addClass('button outline error');
    });//end dom ready

})(jQuery);