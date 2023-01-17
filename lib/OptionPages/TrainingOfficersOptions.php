<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

use Lasntg\Admin\Subscriptions\Editors;

class TrainingOfficersOptions extends OptionPage
{
    use Editors;
    public static function init(): void
    {
        parent::$tabName = 'training_officers';
        parent::init();
        if (is_admin() && static::$activeTab == static::$tabName) {
            parent::$optionName = 'lasntg_subscriptions_training_officers';

            add_action('admin_init', [static::class, 'page_init']);
        }
        add_action('admin_menu', [static::class, 'add_plugin_page']);
    }
    public static function load_page_content(): void
    {
    }
    public static function page_init(): void
    {
        parent::register_setting();


        add_settings_field(
            'course_cancelled',                                    // id
            __('Course Cancelled Subject', 'lasntgadmin'),          // title
            [self::class, 'course_cancelled_subject'],                  // callback
            self::$optionName,                   // page
            'message_settings'                              // section
        );

        add_settings_field(
            'course_cancelled',                                    // id
            __('Course Cancelled', 'lasntgadmin'),          // title
            [self::class, 'course_cancelled'],                  // callback
            self::$optionName,                   // page
            'message_settings'                              // section
        );


        add_settings_field(
            'course_cancelled',                                    // id
            __('Course Cancelled Subject', 'lasntgadmin'),          // title
            [self::class, 'course_cancelled_subject'],                  // callback
            self::$optionName,                   // page
            'message_settings'                              // section
        );

        add_settings_field(
            'course_cancelled',                                    // id
            __('Course Cancelled', 'lasntgadmin'),          // title
            [self::class, 'training_course_cancelled'],                  // callback
            self::$optionName,                   // page
            'message_settings'                              // section
        );
    }


    public static function section_info()
    {

?>
        <p>
            <?= __('Messages for training officers...', 'lasntgadmin') ?>
        </p>
<?php
    }

    public static function sanitize($input): array
    {
        return [];
    }
}
