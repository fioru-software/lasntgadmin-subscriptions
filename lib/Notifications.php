<?php

namespace Lasntg\Admin\Subscriptions;

use Lasntg\Admin\Subscriptions\Notifications\ManagersNotifications;
use Lasntg\Admin\Subscriptions\Notifications\NotificationUtils;

class Notifications {

	public static function course_cancelled( $post_ID ) {
		// notify Managers.
		ManagersNotifications::notify_managers_course_cancellation( $post_ID );
		// orders should be cancelled already.
		// notify all users that had orders.
		$users = NotificationUtils::get_users_by_product_orders( $post_ID, [ 'wc-cancelled' ] );

		NotificationUtils::get_content_for_users( $users, $post_ID, 'course_cancellation_subject', 'course_cancellation' );
	}

	public static function course_updated( $post_ID ) {
		ManagersNotifications::notify_managers_course_updated( $post_ID );
	}
	public static function course_status_change( $post_ID, $post_after, $post_before ) {
	}
	public static function open_for_enrollment( $post_id ) {
	}

	public static function new_course( $post_ID ) {
		ManagersNotifications::notify_managers_new_course( $post_ID );
	}


}
