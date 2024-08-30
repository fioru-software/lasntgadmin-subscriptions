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
		NotificationUtils::get_content( $post_ID, 'course_open_for_enrollment_subject', 'course_open_for_enrollment', static::$user_role );
	}

	public static function custom_canellation_with_message( $post_ID, $subject, $body ) {
		$users = NotificationUtils::get_users_in_group( $post_ID, static::$user_role );
		NotificationUtils::parse_emails_for_users( $users, $subject, $body, $post_ID );
	}

	protected static function process_payment_link( $user, $post_ID, $link ) {
		$email = NotificationUtils::get_email_subject_and_body( $post_ID, 'course_space_available_free_subject', 'course_space_available_free' );
		
		if ( $email && $email['subject'] && $email['body'] ) {
			$link    = "<a href='$link'>Click Here to view</a>";
			$subject = str_replace( '{%payment-link%}', $link, $email['subject'] );
			$body    = str_replace( '{%payment-link%}', $link, $email['body'] );
			$subject = ParseEmail::add_receiver_info( $user, $subject, $post_ID );
			$body    = ParseEmail::add_receiver_info( $user, $body, $post_ID );
			$users   = [
				$user,
			];
			NotificationUtils::parse_emails_for_users( $users, $subject, $body, $post_ID );
			return true;
		}
		return false;
	}

	public static function space_available( $post_ID, $user, $link ) {
		self::set_option_name();
		self::process_payment_link( $user, $post_ID, $link );
	}
}
