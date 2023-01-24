<?php

namespace Lasntg\Admin\Subscriptions\Notifications;

class ManagersNotifications {

	public static function notify_managers_new_course( $post_ID ) {
		NotificationUtils::get_content( $post_ID, 'course_new_subject', 'course_new' );
	}
	public static function notify_managers_course_status_changed( $post_ID ) {
		NotificationUtils::get_content( $post_ID, 'status_change_subject', 'status_change' );
	}
	public static function notify_managers_course_updated( $post_ID ) {
		NotificationUtils::get_content( $post_ID, 'course_update_subject', 'course_update' );
	}

	public static function notify_managers_course_cancellation( $post_ID ) {
		NotificationUtils::get_content( $post_ID, 'course_cancellation_subject', 'course_cancellation' );
	}
}
