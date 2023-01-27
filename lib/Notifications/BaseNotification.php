<?php

namespace Lasntg\Admin\Subscriptions\Notifications;

use Lasntg\Admin\Subscriptions\OptionPages\Editors;

abstract class BaseNotification{
    protected static $option_name;
	protected static function set_option_name()
	{
		Editors::$option_name = static::$option_name;
	}

	public static function new_course( $post_ID ) {
		self::set_option_name();
		NotificationUtils::get_content( $post_ID, 'course_new_subject', 'course_new', 'training_officer' );
	}
	public static function status_changed( $post_ID ) {
		self::set_option_name();
		NotificationUtils::get_content( $post_ID, 'status_change_subject', 'status_change' );
	}
	public static function course_updated( $post_ID ) {
		self::set_option_name();
		NotificationUtils::get_content( $post_ID, 'course_update_subject', 'course_update' );
	}

	public static function course_cancellation( $post_ID ) {
		self::set_option_name();
		NotificationUtils::get_content( $post_ID, 'course_cancellation_subject', 'course_cancellation' );
	}
}