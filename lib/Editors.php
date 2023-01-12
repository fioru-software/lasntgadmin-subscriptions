<?php

namespace Lasntg\Admin\Subscriptions;

trait Editors{
    protected static $optionName = 'lasntg_subscriptions_options';
    protected static $options;
    public static function course_update()
    {
        self::wp_editor('course_update');
    }
    public static function course_cancellation()
    {
        self::wp_editor('course_cancellation');
    }

    public static function status_set_to_enrolling()
    {
        self::wp_editor('status_set_to_enrolling');
    }
    
    public static function order_cancellation()
    {
        self::wp_editor('order_cancellation');
    }

    public static function course_creation()
    {
        self::wp_editor('course_creation');
    }

    public static function cancel_waiting_order()
    {
        self::wp_editor('cancel_waiting_order');
    }
    
    public static function training_centre_confirms_order()
    {
        self::wp_editor('training_centre_confirms_order');
    }


    public static function status_change()
    {
        self::wp_editor('status_change');
    }


    private static function wp_editor($name)
    {
        $input_name = self::$optionName . "[$name]";
        $settings = array(
            'textarea_name' => $input_name,
            'media_buttons' => false,
            'textarea_rows' => 5,
        );
        echo wp_editor(
            self::get_options($name),
            $name,
            $settings
        );
    }

    public static function get_options($name)
    {
        if(!self::$options){
            self::$options = get_option(self::$optionName);
        }
        
        return self::$options && isset(self::$options[$name]) ? self::$options[$name] : '';
    }
}