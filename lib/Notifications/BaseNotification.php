<?php

namespace Lasntg\Admin\Subscriptions\Notifications;

use Lasntg\Admin\Subscriptions\OptionPages\Editors;

abstract class BaseNotification {

	protected static $option_name;
	protected static $user_role;
	private static $delay_time = 5;
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
	public static function new_course( $post_ID, $page = 1 ): void {
		self::get_content( $post_ID, 'lasntgadmin_course_notifications', 'course_new_subject', 'course_new', 'new_course', $page );
	}
	/**
	 * Status Changed.
	 *
	 * @param  mixed $post_ID Post ID.
	 * @return void
	 */
	public static function status_changed( $post_ID, $page = 1 ): void {
		self::get_content( $post_ID, 'lasntgadmin_course_notifications', 'status_change_subject', 'status_change', 'status_changed', $page );
	}
	/**
	 * Course Updated.
	 *
	 * @param  mixed $post_ID Post ID.
	 * @return void
	 */
	public static function course_updated( $post_ID, $page = 1 ): void {
		self::get_content( $post_ID, 'lasntgadmin_course_notifications', 'course_update_subject', 'course_update', 'course_updated', $page );
	}

	private static function get_content( $post_ID, $action, $subject, $body, $method, $page = 1 ): void {
		self::set_option_name();
		$user_count = NotificationUtils::get_content( $post_ID, $subject, $body, static::$user_role, $page );
		self::do_notifications_actions($post_ID, $user_count, $action, $page);
	}
	/**
	 * Course Cancelled.
	 *
	 * @param  mixed $post_ID Post ID.
	 * @return void
	 */
	public static function course_cancelled( $post_ID, $page = 1 ): void {
		self::get_content( $post_ID, 'lasntgadmin_course_notifications', 'course_cancellation_subject', 'course_cancellation', 'course_cancelled', $page );
	}

	public static function open_for_enrollment( $post_ID, $page = 1 ) {
		self::get_content( $post_ID, 'lasntgadmin_course_notifications', 'course_open_for_enrollment_subject', 'course_open_for_enrollment', 'open_for_enrollment', $page );
	}

	public static function custom_cancelletaion( $post_ID, $subject, $body, $page = 1 ) {
		self::set_option_name();
		$user_count = NotificationUtils::get_users_in_group( $post_ID, static::$user_role, $page );
		NotificationUtils::parse_emails_for_users( $user_count, $subject, $body, $post_ID );
		$action = 'lasntgadmin_course_notifications';

		self::do_notifications_actions($post_ID, $user_count, $action, $page);
	}

	private static function do_notifications_actions($post_ID, $user_count, $action, $page): void
	{
		if ( is_int( $user_count ) && $user_count >= NotificationUtils::$per_page ) {
			if ( $user_count >= NotificationUtils::$per_page ) {
				$mins = self::$delay_time;
				switch(self::$option_name){
					case "lasntg_subscriptions_options":
					case "lasntg_subscriptions_private":
						$mins += 1;
					case "lasntg_subscriptions_regional_options":
						$mins += 3;
					case "lasntg_subscriptions_training_officers":
						$mins += 1;
				}
				
				as_schedule_single_action(
					time() + 60 * $mins * $page + 1,
					// Run after 5 mins.
					$action,
					array(
						'page'       => $page + 1,
						'product_id' => $post_ID,
						'class'      => get_called_class(),
						'method'     => 'custom_cancelletaion',
					)
				);
			}
		}
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
