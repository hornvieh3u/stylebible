<?php

class Klaviyo_EmailSignUp_Widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(false, $name = 'Klaviyo: Legacy Email Sign Up', $widget_options = array(
          'description' => 'Allow people to subscribe to a Klaviyo email list using a Legacy Signup Form.'
        ));
    }

    function widget($args, $instance)
    {

        extract($args);
        $klaviyo_settings = get_option('klaviyo_settings');
        $list_id = $instance['list_id'];

        if (empty($list_id)) {
            return;
        }

        // check if the form fields were submitted with any value
        // if they were, use the value submitted
        // else, use a default (empty string or 'Subscribe')
        $title = (!empty($instance['title'])) ? $instance['title'] : '';
        $description = (!empty($instance['description'])) ? $instance['description'] : '';
        $button_text = (!empty($instance['button_text'])) ? $instance['button_text'] : 'Subscribe';
        $styles = (!empty($instance['button_styles'])) ? $instance['button_styles'] : '';

        echo $before_widget;

        if (trim($title) != '') {
            echo $before_title . $title . $after_title;
        }

        echo '<form id="kla_embed_' . $this->id . '" class="" action="//manage.kmail-lists.com/subscriptions/subscribe" data-ajax-submit="//manage.kmail-lists.com/ajax/subscriptions/subscribe" method="GET" target="_blank" novalidate="novalidate">' . "\n";
        echo '  <input type="hidden" name="g" value="' . $list_id . '">' . "\n";

        if (!empty($description)) {
            echo '  <p>' . $description . '</p>' . "\n";
        }

        echo '  <div class="klaviyo_field_group">' . "\n";
        echo '    <label for="kla_email_' . $this->id . '" style="display:none;">' . $title . '</label>' . "\n";
        echo '    <input type="text" value="" name="email" id="kla_email_' . $this->id . '" placeholder="Your email" />' . "\n";
        echo '    <button type="submit" class="klaviyo_submit_button" style="' . $styles . '">' . $button_text . '</button>' . "\n";

        echo '  </div>' . "\n";
        echo '  <div class="klaviyo_messages">' . "\n";
        echo '    <div class="success_message" style="display:none;"></div>' . "\n";
        echo '    <div class="error_message" style="display:none;"></div>' . "\n";
        echo '  </div>' . "\n";
        echo '</form>' . "\n";
        echo '<script type="text/javascript" src="//www.klaviyo.com/media/js/public/klaviyo_subscribe.js"></script>' . "\n";
        echo '<script type="text/javascript">' . "\n";
        echo '  KlaviyoSubscribe.attachToForms("#kla_embed_' . $this->id . '", {' . "\n";
        echo '    hide_form_on_success: true' . "\n";
        echo '  });' . "\n";
        echo '</script>' . "\n";


        echo $after_widget;
    }

    function update($new_instance, $old_instance)
    {
        return array_merge($old_instance, $new_instance);
    }

    function form($instance)
    {
        $instance = wp_parse_args($instance, array(
          'title' => '',
          'list_id' => '',
          'description' => '',
          'button_text' => '',
          'button_styles' => ''
        ));
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" /></label></p>
        <label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('List Description:'); ?></label>
        <textarea class="widefat" rows="3" cols="20" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>"><?php echo $instance['description']; ?></textarea>
        <p><label for="<?php echo $this->get_field_id('button_text'); ?>"><?php _e('Button Text:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('button_text'); ?>" name="<?php echo $this->get_field_name('button_text'); ?>" type="text" value="<?php echo $instance['button_text']; ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('list_id'); ?>"><?php _e('List ID:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('list_id'); ?>" name="<?php echo $this->get_field_name('list_id'); ?>" type="text" value="<?php echo $instance['list_id']; ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('button_styles'); ?>"><?php _e('Button Styles:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('button_styles'); ?>" name="<?php echo $this->get_field_name('button_styles'); ?>" type="text" value="<?php echo $instance['button_styles']; ?>" /></label></p>
        <?php
    }
}


class Klaviyo_EmbedEmailSignUp_Widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(false, $name = 'Klaviyo: Email Sign Up', $widget_options = array(
          'description' => 'Allow people to subscribe to a Klaviyo email list designed using Klaviyo\'s built-in Signup Form Builder.'
        ));
    }

    function widget($args, $instance)
    {

        extract($args);
        $klaviyo_settings = get_option('klaviyo_settings');
        $form_id = $instance['form_id'];

        if (empty($form_id)) {
            return;
        }

        $title = $instance['title'];

        echo $before_widget;

        if (trim($title) != '') {
            echo $before_title . $title . $after_title;
        }

        echo '<div class="klaviyo-form-' . $form_id . '"></div>';

        echo $after_widget;
    }

    function update($new_instance, $old_instance)
    {
        return array_merge($old_instance, $new_instance);
    }

    function form($instance)
    {
        $instance = wp_parse_args($instance, array(
          'title' => '',
          'form_id' => ''
        ));
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('form_id'); ?>"><?php _e('Form ID:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('form_id'); ?>" name="<?php echo $this->get_field_name('form_id'); ?>" type="text" value="<?php echo $instance['form_id']; ?>" /></label></p>
        <?php
    }
}

?>