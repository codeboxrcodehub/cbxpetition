<?php

namespace Cbx\Petition\Widgets\Classic;

// If this file is called directly, abort.
if ( ! defined('WPINC')) {
    die;
}

use WP_Widget;
use Cbx\Petition\CBXSetting;


/**
 * Class CBXPetition Summary Widget
 *
 * @since 1.0.2
 */
class CBXPetitionSignformWidget extends WP_Widget
{
    /**
     * CBXPetitionSignformWidget constructor.
     */
    public function __construct()
    {
        parent::__construct('cbxpetition_signform',
                esc_html__('CBX Petition Sign Form', 'cbxpetition'),
                [
                        'description' => esc_html__('Single Petition Sign Form Widget for CBX Petition', 'cbxpetition'),
                ]);
    }//end of constructor method

    /**
     * @param  array  $instance
     *
     * @return string|void
     */
    public function form($instance)
    {
        $title = isset($instance['title']) ? $instance['title'] : esc_html__('Petition Sign Form', 'cbxpetition');


        $petition_id = isset($instance['petition_id']) ? intval($instance['petition_id']) : 0;
        $use_current = isset($instance['use_current']) ? intval($instance['use_current']) : 0;


        ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')) ?>"><?php echo esc_html__('Title:', 'cbxpetition'); ?></label>
            <input type="text"  name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('petition_id')) ?>"><?php echo esc_html__('Petition ID:', 'cbxpetition'); ?></label>
            <input type="text"  name="<?php echo esc_attr($this->get_field_name('petition_id')); ?>"
                   id="<?php echo esc_attr($this->get_field_id('petition_id')); ?>"
                   value="<?php echo intval($petition_id); ?>"/><br/>
            <?php
            //$petition = get_post( $petition_id );
            if ($petition_id > 0 && get_post($petition_id) !== null) {
                /* translators: %1$s: Petition link , %2$s: Petition title  */
                echo sprintf(wp_kses(__('Showing petition sign form for <a target="_blank" href="%1$s"><strong>%2$s</strong></a>', 'cbxpetition'), ['a' => ['href' => [], 'target' => []], 'strong' => []]), esc_url(get_permalink($petition_id)), esc_attr(get_the_title($petition_id)));
            } else {
                echo wp_kses(__('Seems <strong>Petition ID</strong> doesn\'t belong to any valid Petition', 'cbxpetition'), ['strong' => []]);
            }
            ?>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('use_current')) ?>"><?php echo esc_html__('Display for Current Petition', 'cbxpetition') ?></label>
            <select name="<?php echo esc_attr($this->get_field_name('use_current')) ?>"
                    id="<?php echo esc_attr($this->get_field_id('use_current')) ?>">
                <option <?php selected($use_current, 0, true) ?>
                        value="0"><?php esc_attr_e('No', 'cbxpetition'); ?></option>
                <option <?php selected($use_current, 1, true) ?>
                        value="1"><?php esc_attr_e('Yes', 'cbxpetition'); ?></option>
            </select>
        </p>
        <p><?php esc_html__('In petition details page, ignore the petition id field and use the current/visiting petition.', 'cbxpetition') ?></p>
        <p><?php
            /* translators: %s: Petition Link  */
            echo sprintf(wp_kses(__('Click <a target="_blank" href="%s">here</a> to see all the petitions', 'cbxpetition'), ['a' => ['href' => [], 'target' => []]]), esc_url(admin_url('edit.php?post_type=cbxpetition')));
            ?></p>
        <?php
    }// end of form method

    /**
     * Update widget
     *
     * @param  array  $args
     * @param  array  $instance
     *
     * @throws Exception
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        $title = ( ! empty($instance['title'])) ? $instance['title'] : esc_html__('Petition Sign Form', 'cbxpetition');


        echo $args['before_title'].$title.$args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped


        $petition_id = isset($instance['petition_id']) ? absint($instance['petition_id']) : 0;
        $use_current = isset($instance['use_current']) ? absint($instance['use_current']) : 0;

        if ($use_current && is_singular('cbxpetition')) {
            $petition_id = absint(get_the_ID());
        }

        //$settings = new CBXSetting();

        $attr = [];

        $attr['title']       = '';
        $attr['petition_id'] = $petition_id;

        $instance['title'] = '';


        $attr = apply_filters('cbxpetition_elementor_shortcode_builder_attr', $attr, $instance, 'cbxpetition_signform');

        $attr_html = '';

        foreach ($attr as $key => $value) {
            $attr_html .= ' '.$key.'="'.$value.'" ';
        }

        echo '<div class="cbx-chota">'.do_shortcode('[cbxpetition_signform '.$attr_html.']').'</div>';

        echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }//end method widget

    /**
     * Update Widget
     *
     * @param  array  $new_instance
     * @param  array  $old_instance
     *
     * @return array
     */
    public function update($new_instance, $old_instance)
    {

        $instance = $old_instance;

        $instance['title']       = isset($new_instance['title']) ? sanitize_text_field(wp_unslash($new_instance['title'])) : '';
        $instance['use_current'] = isset($new_instance['use_current']) ? absint($new_instance['use_current']) : 0;//use current petition if in petition details page
        $instance['petition_id'] = isset($new_instance['petition_id']) ? absint($new_instance['petition_id']) : 0;


        return $instance;
    }// end of update method
}//end class CBXPetitionSignformWidget