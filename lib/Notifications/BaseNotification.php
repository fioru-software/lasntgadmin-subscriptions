<?php

namespace Lasntg\Admin\Subscriptions\Notifications;

use Lasntg\Admin\Subscriptions\OptionPages\Editors;

abstract class BaseNotification {

	protected static $option_name;
	protected static $user_role;
	/**
	 * Set Option Name.
	 *
	 * @return void
	 */
	protected static function set_option_name(): void {
		Editors::$option_name = static::$option_name;
	}

	/**
	 * New Course.
	 *
	 * @param  mixed $post_ID Post ID.
	 * @return void
	 */
	public static function new_course( $post_ID ): void {
		self::set_option_name();
		NotificationUtils::get_content( $post_ID, 'course_new_subject', 'course_new', static::$user_role );
	}
	/**
	 * Status Changed.
	 *
	 * @param  mixed $post_ID Post ID.
	 * @return void
	 */
	public static function status_changed( $post_ID ): void {
		self::set_option_name();
		NotificationUtils::get_content( $post_ID, 'status_change_subject', 'status_change', static::$user_role );
	}
	/**
	 * Course Updated.
	 *
	 * @param  mixed $post_ID Post ID.
	 * @return void
	 */
	public static function course_updated( $post_ID ): void {
		self::set_option_name();
		NotificationUtils::get_content( $post_ID, 'course_update_subject', 'course_update', static::$user_role );
	}

	/**
	 * Course Cancelled.
	 *
	 * @param  mixed $post_ID Post ID.
	 * @return void
	 */
	public static function course_cancelled( $post_ID ): void {
		static::set_option_name();
		NotificationUtils::get_content( $post_ID, 'course_cancellation_subject', 'course_cancellation', static::$user_role );
	}

	public static function open_for_enrollment( $post_ID ) {
		static::set_option_name();
		error_log('Open for enrollment' . static::$option_name);
		NotificationUtils::get_content( $post_ID, 'course_open_for_enrollment_subject', 'course_open_for_enrollment', static::$user_role );
	}

	public static function custom_canellation_with_message( $post_ID, $subject, $body ) {
		error_log('custom cancellation..'. static::$user_role);
		$users = NotificationUtils::get_users_in_group( $post_ID, static::$user_role );
		NotificationUtils::parse_emails_for_users( $users, $subject, $body );
	}
}
