<?php

namespace Lasntg\Admin\Subscriptions\Traits;

trait ManagersNotifications{
 public static function notify_managers_new_course($post_ID)
    {
        self::get_content($post_ID, 'course_update_subject', 'course_update');
    }
    public static function notify_managers_course_status_changed($post_ID)
    {
        self::get_content($post_ID, 'course_cancellation_subject', 'course_cancellation');
    }
    public static function notify_managers_course_updated($post_ID)
    {
        self::get_content($post_ID, 'course_update_subject', 'course_update');
    }

    public static function notify_managers_course_cancellation($post_ID)
    {
        self::get_content($post_ID, 'course_cancellation_subject', 'course_cancellation');
    }

}