;(function ($) {
  'use strict';

  function cbxpetition_isEmptyOrUndefined(val) {
    return val === undefined || val === null || val === '';
  }//end method cbxpetition_isEmptyOrUndefined

  $(document).ready(function () {
    //Initiate Color Picker
    //$('.wp-color-picker-field').wpColorPicker();

    var cbxpetition_setting_awn_options = {
      labels: {
        tip          : cbxpetition_setting_js_var.awn_options.tip,
        info         : cbxpetition_setting_js_var.awn_options.info,
        success      : cbxpetition_setting_js_var.awn_options.success,
        warning      : cbxpetition_setting_js_var.awn_options.warning,
        alert        : cbxpetition_setting_js_var.awn_options.alert,
        async        : cbxpetition_setting_js_var.awn_options.async,
        confirm      : cbxpetition_setting_js_var.awn_options.confirm,
        confirmOk    : cbxpetition_setting_js_var.awn_options.confirmOk,
        confirmCancel: cbxpetition_setting_js_var.awn_options.confirmCancel
      }
    };

    $('.setting-color-picker-wrapper').each(function (index, element) {
      var $color_field_wrap = $(element);

      var $color_field      = $color_field_wrap.find('.setting-color-picker');
      var $color_field_fire = $color_field_wrap.find('.setting-color-picker-fire');

      var $current_color = $color_field_fire.data('current-color');
      //var $default_color = $color_field_fire.data('default-color');


      // Simple example, see optional options for more configuration.
      const pickr = Pickr.create({
        el     : $color_field_fire[0],
        theme  : 'classic', // or 'monolith', or 'nano'
        default: $current_color,

        swatches: [
          'rgba(244, 67, 54, 1)',
          'rgba(233, 30, 99, 0.95)',
          'rgba(156, 39, 176, 0.9)',
          'rgba(103, 58, 183, 0.85)',
          'rgba(63, 81, 181, 0.8)',
          'rgba(33, 150, 243, 0.75)',
          'rgba(3, 169, 244, 0.7)',
          'rgba(0, 188, 212, 0.7)',
          'rgba(0, 150, 136, 0.75)',
          'rgba(76, 175, 80, 0.8)',
          'rgba(139, 195, 74, 0.85)',
          'rgba(205, 220, 57, 0.9)',
          'rgba(255, 235, 59, 0.95)',
          'rgba(255, 193, 7, 1)'
        ],

        components: {

          // Main components
          preview: true,
          opacity: true,
          hue    : true,

          // Input / output Options
          interaction: {
            hex  : true,
            rgba : false,
            hsla : false,
            hsva : false,
            cmyk : false,
            input: true,
            clear: true,
            save : true
          }
        },
        i18n      : cbxpetition_setting_js_var.pickr_i18n
      });

      pickr.on('init', instance => {
        //console.log('Event: "init"', instance);
      }).on('hide', instance => {
        //console.log('Event: "hide"', instance);
      }).on('show', (color, instance) => {
        //console.log('Event: "show"', color, instance);
      }).on('save', (color, instance) => {
        //console.log(color.toHEXA().toString());
        //console.log(color);

        if (color !== null) {
          $color_field_fire.data('current-color', color.toHEXA().toString());
          $color_field.val(color.toHEXA().toString());
        } else {
          $color_field_fire.data('current-color', '');
          $color_field.val('');
        }


        //console.log(instance);
        //console.log(color.toHEXA());
        //console.log(color.toHEX);
      }).on('clear', instance => {
        //console.log('Event: "clear"', instance);


      }).on('change', (color, source, instance) => {
        //console.log('Event: "change"', color, source, instance);

      }).on('changestop', (source, instance) => {
        //console.log('Event: "changestop"', source, instance);
      }).on('cancel', instance => {
        //console.log('Event: "cancel"', instance);
      }).on('swatchselect', (color, instance) => {
        //console.log('Event: "swatchselect"', color, instance);
      });

    });


    //select2
    $('.selecttwo-select-wrapper').each(function (index, element) {
      var $element = $(element);

      var $placeholder = $element.data('placeholder');
      var $allow_clear = $element.data('allow-clear');

      if(cbxpetition_isEmptyOrUndefined($placeholder)) $placeholder = cbxpetition_setting_js_var.please_select;

      $element
          .find('.selecttwo-select')
          .select2({
            placeholder: $placeholder,
            allowClear: $allow_clear ? true : false,
            theme: 'default',
            dropdownParent: $element
          })
          .on('select2:open', function () {
            $('.select2-search__field').attr(
                'placeholder',
                cbxpetition_setting_js_var.placeholder.search
            );
          })
          .on('select2:close', function () {
            $('.select2-search__field').attr('placeholder', $placeholder);
          });

      $element
          .find('.select2-selection__rendered')
          .find('.select2-search--inline .select2-search__field')
          .attr('placeholder', $placeholder);
    });

    var $setting_page = $('#cbxpetition-setting');
    var $setting_nav = $setting_page.find('.setting-tabs-nav');
    var activetab = '';
    if (typeof (localStorage) !== 'undefined') {
      activetab = localStorage.getItem('cbxpetitionactivetab');
    }

    //if url has section id as hash then set it as active or override the current local storage value
    if (window.location.hash) {
      if ($(window.location.hash).hasClass('global_setting_group')) {
        activetab = window.location.hash;
        if (typeof (localStorage) !== 'undefined') {
          localStorage.setItem('cbxpetitionactivetab', activetab);
        }
      }
    }

    function setting_nav_change($tab_id) {
      if ($tab_id === null) {
        return;
      }

      $tab_id = $tab_id.replace('#', '');

      $setting_nav.find('a').removeClass('active');
      $('#' + $tab_id + '-tab').addClass('active');

      var clicked_group = '#' + $tab_id;

      $('.global_setting_group').hide();
      $(clicked_group).fadeIn();

      //load the reset items
      if (clicked_group === "#cbxpetition_tools") {
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: cbxpetition_setting_js_var.ajax.ajax_url,
          data: {
            action: "cbxpetition_settings_reset_load",
            security: cbxpetition_setting_js_var.ajax.ajax_nonce,
          },
          success: function (data, textStatus, XMLHttpRequest) {
            $("#setting_resetinfo").html(data.html);
          }, //end of success
        }); //end of ajax
      }

      //set in local storage
      if (typeof (localStorage) !== 'undefined') {
        localStorage.setItem('cbxpetitionactivetab', clicked_group);
      }
    }//end method setting_nav_change

    //click on inidividual nav
    $setting_nav.on('click', 'a', function (e) {
      e.preventDefault();

      var $this = $(this);
      var $tab_id = $this.data('tabid');

      $('.setting-select-nav').val($tab_id);
      $('.setting-select-nav').trigger('change');
    });

    $('.setting-select-nav').on('change', function (e) {
      var $this = $(this);
      var $tab_id = $this.val();

      setting_nav_change($tab_id);
    });

    if (activetab !== null) {
      var activetab_whash = activetab.replace('#', '');

      $('.setting-select-nav').val(activetab_whash);
      $('.setting-select-nav').trigger('change');
    }

    $("#setting_resetinfo").on('click',      ".cbxpetition_setting_options_check_action_call",
      function (e) {
        e.preventDefault();

        var $this = $(this);
        $("#setting_resetinfo").find(":checkbox").prop("checked", true);
      }
    );

    $("#setting_resetinfo").on(
      'click',
      ".cbxpetition_setting_options_check_action_ucall",
      function (e) {
        e.preventDefault();

        var $this = $(this);
        $("#setting_resetinfo").find(":checkbox").prop("checked", false);
      }
    );


    $('.wpsa-browse').on('click', function (event) {
      event.preventDefault();

      var self = $(this);

      // Create the media frame.
      var file_frame = wp.media.frames.file_frame = wp.media({
        title: self.data('uploader_title'),
        button: {
          text: self.data('uploader_button_text')
        },
        multiple: false
      });

      file_frame.on('select', function () {
        var attachment = file_frame.state().get('selection').first().toJSON();

        var picker_wrapper = self.closest('.cbxchota-setting_input_file_wrap');

        picker_wrapper.find('.wpsa-url').val(attachment.url);
        picker_wrapper.find('.cbxchota-setting_marker_preview').css({
          'background-image': 'url("' + attachment.url + '")'
        }).removeClass('cbxchota-setting_marker_hide');
        picker_wrapper.find('.cbxchota-setting_trash').removeClass('cbxchota-setting_trash_hide');
        picker_wrapper.find('.cbxchota-setting_filepicker_btn').addClass('cbxchota-setting_filepicked').removeClass('cbxchota-setting_left_space');
      });

      // Finally, open the modal
      file_frame.open();
    });

    // for icon delete functionality
    $(document).on('click', '.cbxchota-setting_input_file_wrap .dashicons', function () {
      var picker_wrapper = $(this).closest('.cbxchota-setting_input_file_wrap');
      picker_wrapper.find('.wpsa-url').val('');
      picker_wrapper.find('.cbxchota-setting_marker_preview').addClass('cbxchota-setting_marker_hide');
      picker_wrapper.find('.cbxchota-setting_filepicker_btn').removeClass('cbxchota-setting_filepicked').addClass('cbxchota-setting_left_space');
      $(this).addClass('cbxchota-setting_trash_hide');
    });


    //make the subheading single row
    $('.setting_heading').each(function (index, element) {
      var $element = $(element);
      var $element_parent = $element.parent('td');

      $element_parent.attr('colspan', 2);
      $element_parent.prev('th').remove();
      $element_parent.parent('tr').removeAttr('class');
      $element_parent.parent('tr').addClass('global_setting_heading_section');
    });


    $('.setting_subheading').each(function (index, element) {
      var $element = $(element);
      var $element_parent = $element.parent('td');

      $element_parent.attr('colspan', 2);
      $element_parent.prev('th').remove();
      $element_parent.parent('tr').removeAttr('class');
      $element_parent.parent('tr').addClass('global_setting_subheading_section');
    });


    $('.global_setting_group').each(function (index, element) {
      var $element = $(element);

      $element.find('.submit_setting').removeClass('button-primary').addClass('primary');

      var $form_table = $element.find('.form-table');
      $form_table.prev('h2').remove();

      var $i = 0;
      $form_table.find('tr').each(function (index2, element) {
        var $tr = $(element);

        if (!$tr.hasClass('global_setting_heading_section')) {
          $tr.addClass('global_setting_common_section');
          $tr.addClass('global_setting_common_section_' + $i);
        } else {
          $i++;
          $tr.addClass('global_setting_heading_section_' + $i);
          $tr.attr('data-counter', $i);
          $tr.attr('data-is-closed', 0);
        }
      });

      $('#global_setting_group_actions').show();
      $('#global_setting_group_actions').on('click', '.global_setting_group_action', function (event) {
        event.preventDefault();

        $form_table.find('.setting_heading').trigger('click');
      });


      $form_table.on('click', '.setting_heading', function (evt) {
        evt.preventDefault();

        var $this = $(this);
        var $parent = $this.closest('.global_setting_heading_section');
        var $counter = Number($parent.data('counter'));
        var $is_closed = Number($parent.data('is-closed'));

        if ($is_closed === 0) {
          $parent.data('is-closed', 1);
          $parent.addClass('global_setting_heading_section_closed');
          $('.global_setting_common_section_' + $counter).hide();
        } else {
          $parent.data('is-closed', 0);
          $parent.removeClass('global_setting_heading_section_closed');
          $('.global_setting_common_section_' + $counter).show();
        }
      });
    });

    $('.checkbox_fields_check_actions').on('click', '.checkbox_fields_check_action_call', function (e) {
      e.preventDefault();

      var $this = $(this);
      $this.parent().next('.checkbox_fields').find(':checkbox').prop("checked", true);
    });

    $('.checkbox_fields_check_actions').on('click', '.checkbox_fields_check_action_ucall', function (e) {
      e.preventDefault();

      var $this = $(this);
      $this.parent().next('.checkbox_fields').find(':checkbox').prop("checked", false);
    });

    //var adjustment_photo;
    $('.checkbox_fields_sortable').sortable({
      vertical: true,
      handle: '.checkbox_field_handle',
      containerSelector: '.checkbox_fields',
      itemSelector: '.checkbox_field',
      placeholder: 'checkbox_field_placeholder'
    });

    //one click save setting for the current tab
    $('#save_settings').on('click', function (e) {
      e.preventDefault();

      var $setting_nav = $('.setting-tabs-nav');

      var $current_tab = $setting_nav.find('.active');
      var $tab_id = $current_tab.data('tabid');
      $('#' + $tab_id).find('.submit_setting').trigger('click');
    });

    $('#setting_info_trig').on('click', function (e) {
      e.preventDefault();

      $('#setting_resetinfo').toggle();
    });

    //reset click
    $('#reset_data_trigger').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);

      var notifier = new AWN(cbxpetition_setting_awn_options);

      var onCancel = () => {};

      var onOk = () => {
        $this.hide();
        let $reset_tables = $('.reset_tables:checkbox:checked')
        .map(function () {
          return this.value;
        })
        .get();

      let $reset_options = $('.reset_options:checkbox:checked')
        .map(function () {
          return this.value;
        })
        .get();

      //send ajax request to reset plugin
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: cbxpetition_setting_js_var.ajax.ajax_url,
        data: {
          action: 'cbxpetition_settings_reset',
          security: cbxpetition_setting_js_var.ajax.ajax_nonce,
          reset_tables: $reset_tables,
          reset_options: $reset_options,
        },
        success: function (data, textStatus, XMLHttpRequest) {
          window.location.href = data.url;
        }, //end of success
      }); //end of ajax
      };

      notifier.confirm(
          cbxpetition_setting_js_var.delete_dialog.are_you_sure_delete_desc,
          onOk,
          onCancel,
          {
            labels: {
              confirm: cbxpetition_setting_js_var.delete_dialog.are_you_sure_global
            }
          }
      );
    });//end click #reset_data_trigger


    //clear permalink cache
    $('.clear-permalink').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      $this.prop('disabled', true);
      $this.addClass('running');

      $.ajax({
        type: 'post',
        dataType: 'json',
        url: cbxpetition_setting_js_var.ajax.ajax_url,
        data: {
          action: 'cbxpetition_permalink_cache_clear',
          security: cbxpetition_setting_js_var.ajax.ajax_nonce,
        },
        success: function (response, textStatus, XMLHttpRequest) {
          let notifier = new AWN(cbxpetition_setting_awn_options);
          if (response.success) {
            notifier.success(response.message);
          } else {
            notifier.error(response.message);
          }

          $this.prop('disabled', false);
          $this.removeClass('running');
        }, //end of success
      }); //end of ajax
    });


  });

})(jQuery);
//settings