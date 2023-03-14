<?php

namespace Lasntg\Admin\Subscriptions;

use Lasntg\Admin\Subscriptions\Notifications\ManagersNotifications;
use Lasntg\Admin\Subscriptions\Notifications\NotificationUtils;
use Lasntg\Admin\Subscriptions\Notifications\PrivateNotifications;
use Lasntg\Admin\Subscriptions\Notifications\RegionalManagerNotifications;
use Lasntg\Admin\Subscriptions\Notifications\TrainingCenterNotifications;

class Notifications {



	/**
	 * Course Cancelled.
	 *
	 * @param  int $post_ID Post ID.
	 * @return void
	 */
	public static function course_cancelled( $post_ID ): void {
		// @todo confirm if the managers msg can be overwritten.
		// notify Managers.
		// orders should be cancelled already.
		// notify all users that had orders.
		$subject = get_post_meta( $post_ID, '_cancellation_subject', true );
		$body    = get_post_meta( $post_ID, '_cancellation_message', true );

		$email = NotificationUtils::parse_info( $post_ID, $subject, $body );
		if ( $subject && $body ) {
			error_log( "Cancelling......\n" );
			ManagersNotifications::custom_canellation_with_message( $post_ID, $email['subject'], $email['body'] );
			RegionalManagerNotifications::custom_canellation_with_message( $post_ID, $email['subject'], $email['body'] );
			TrainingCenterNotifications::custom_canellation_with_message( $post_ID, $email['subject'], $email['body'] );
			PrivateNotifications::custom_canellation_with_message( $post_ID, $email['subject'], $email['body'] );
		} else {
			ManagersNotifications::course_cancelled( $post_ID );
			RegionalManagerNotifications::course_cancelled( $post_ID );
			TrainingCenterNotifications::course_cancelled( $post_ID );
			PrivateNotifications::course_cancelled( $post_ID );
		}
	}

	public static function course_updated( $post_ID ): void {
		ManagersNotifications::course_updated( $post_ID );
		RegionalManagerNotifications::course_updated( $post_ID );
		TrainingCenterNotifications::course_updated( $post_ID );
		PrivateNotifications::course_updated( $post_ID );
	}
	public static function course_status_change( $post_ID, $post_after, $post_before ): void {
		TrainingCenterNotifications::status_changed( $post_ID );
		ManagersNotifications::status_changed( $post_ID );
		RegionalManagerNotifications::status_changed( $post_ID );
		PrivateNotifications::status_changed( $post_ID );
	}
	public static function open_for_enrollment( $post_ID ): void {
		TrainingCenterNotifications::open_for_enrollment( $post_ID );
		ManagersNotifications::open_for_enrollment( $post_ID );
		RegionalManagerNotifications::open_for_enrollment( $post_ID );
		PrivateNotifications::open_for_enrollment( $post_ID );
	}

	public static function new_course( $post_ID ): void {
		ManagersNotifications::new_course( $post_ID );
		TrainingCenterNotifications::new_course( $post_ID );
		RegionalManagerNotifications::new_course( $post_ID );
		PrivateNotifications::new_course( $post_ID );
	}
}
