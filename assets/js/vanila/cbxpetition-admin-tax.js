(function ($) {
    'use strict';

    jQuery(document).ready(function ($) {
        //tag add page with listing
        $('.wrap').addClass('cbx-chota cbxpetition-page-wrapper cbxpetition-tax-wrapper');
        $('#col-container').addClass('container');
        $('#col-left').addClass('col-12');
        $('#col-right').addClass('col-12');

        $('#col-container').wrapInner('<div class="row"/>');

        $('#col-left').wrapInner('<div class="inside"/>');
        $('#col-left').wrapInner('<div class="postbox"/>');
        $('#col-right').wrapInner('<div class="inside"/>');
        $('#col-right').wrapInner('<div class="postbox"/>');

        $('#col-left h2').addClass('button primary outline');
        $('#col-left').on('click', 'h2', function (event){
           event.preventDefault();

            $('#addtag').toggle();
        });


        $('.search-form').insertBefore('#posts-filter');



        var $tax_heading = $('h1.wp-heading-inline');
        var $tax_heading_title = $tax_heading.text();
        $tax_heading.insertBefore('.search-form');
        $tax_heading.text(cbxpetition_tax.tax_title_prefix+$tax_heading_title);
        $tax_heading.wrap('<div class="wp-heading-wrap mb-20"><div class="wp-heading-wrap-left pull-left"></div></div>');
        var $tax_wrapper = $tax_heading.closest('.wp-heading-wrap');
        $tax_wrapper.append(cbxpetition_tax.tax_new_setting);

        //tag edit page
        $('#edittag').wrap('<div class="container"/>');
        $('#edittag').wrap('<div class="row"/>');
        $('#edittag').wrap('<div class="col-12"/>');
        $('#edittag').wrap('<div class="postbox cbxpostbox"/>');
        $('#edittag').wrap('<div class="inside"/>');
        $('h1').insertBefore('#edittag');

        $('.button-primary').addClass('primary').removeClass('button-primary');
        $('#delete-link').addClass('button outline primary');

        $('#ajax-response').wrap('<div class="container"/>');
        $('#ajax-response').wrap('<div class="row"/>');
        $('#ajax-response').wrap('<div class="col-12"/>');

        $('#message').wrap('<div class="container"/>');
        $('#message').wrap('<div class="row"/>');
        $('#message').wrap('<div class="col-12"/>');

        $('#screen-meta').addClass('cbx-chota cbxpetition-page-wrapper cbxpetition-tax-wrapper');
        $('#tag-search-input').attr('placeholder', cbxpetition_tax.placeholder.search);

        //trying to add form validation using jquery but not working as it's managed from the core js in admin side
        /*var $addtag_form = $('#addtag');
        if($addtag_form.length){
            console.log($addtag_form);

            var $addtag_form_validation = $addtag_form.validate({
                ignore        : '',
                errorElement  : 'p',
                rules: {
                    'tag-name' : {
                        required: true
                    }
                },
                messages      : {}

            });

            console.log($addtag_form_validation);

            $addtag_form.on('submit', function (e) {
                console.log('hi there');

                /!*if (!$addtag_form_validation.valid()) {
                    e.preventDefault();
                }*!/

                e.preventDefault();
            });
        }*/

    });//end dom ready

})(jQuery);