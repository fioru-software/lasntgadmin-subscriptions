<?php

namespace Lasntg\Admin\Subscriptions;

use Lasntg\Admin\Subscriptions\Notifications\ManagersNotifications;
use Lasntg\Admin\Subscriptions\Notifications\NotificationUtils;
use Lasntg\Admin\Subscriptions\Notifications\PrivateNotifications;
use Lasntg\Admin\Subscriptions\Notifications\RegionalManagerNotifications;
use Lasntg\Admin\Subscriptions\Notifications\TrainingCenterNotifications;

class Notifications {


	public static function course_cancelled( $post_ID ) {
		// @todo confirm if the managers msg can be overwritten.
		// notify Managers.
		// orders should be cancelled already.
		// notify all users that had orders.
		$users   = NotificationUtils::get_users_by_product_orders( $post_ID );
		$subject = get_post_meta( $post_ID, '_cancellation_subject', true );
		$body    = get_post_meta( $post_ID, '_cancellation_message', true );
		/**
		 * Should the other users receive generic info or receive the new ones??
		 */
		$email = NotificationUtils::parse_info( $post_ID, $subject, $body );
		if ( $subject && $body ) {
			NotificationUtils::parse_emails_for_users( $users, $email['subject'], $email['body'] );
		} else {
			ManagersNotifications::course_cancelled( $post_ID );
			RegionalManagerNotifications::course_cancelled( $post_ID );
			TrainingCenterNotifications::course_cancelled( $post_ID );
		}
	}

	public static function course_updated( $post_ID ) {
		ManagersNotifications::course_updated( $post_ID );
		RegionalManagerNotifications::course_updated( $post_ID );
		TrainingCenterNotifications::course_updated( $post_ID );
	}
	public static function course_status_change( $post_ID, $post_after, $post_before ) {
		TrainingCenterNotifications::status_changed( $post_ID );
		ManagersNotifications::status_changed( $post_ID );
		RegionalManagerNotifications::status_changed( $post_ID );
	}
	public static function open_for_enrollment( $post_ID ) {
		PrivateNotifications::open_for_enrollment( $post_ID );
	}

	public static function new_course( $post_ID ) {
		ManagersNotifications::new_course( $post_ID );
		TrainingCenterNotifications::new_course( $post_ID );
		RegionalManagerNotifications::new_course( $post_ID );
	}
}
