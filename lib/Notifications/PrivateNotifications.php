<?php

namespace Lasntg\Admin\Subscriptions\Notifications;

class PrivateNotifications extends BaseNotification {

	protected static $option_name = 'lasntg_subscriptions_private';
	protected static $user_role   = 'customer';

	public static function space_available( $post_ID, $user, $link ) {
		self::set_option_name();
		$email = NotificationUtils::get_email_subject_and_body( $post_ID, 'course_space_available_subject', 'course_space_available' );
		$users = [
			$user,
		];
		if ( $email && $email['subject'] && $email['body'] ) {
<<<<<<< HEAD
			$link    = "<a href='$link'>Payment Link</a>";
=======
>>>>>>> master
			$subject = str_replace( '{%payment-link%}', $link, $email['subject'] );
			$body    = str_replace( '{%payment-link%}', $link, $email['body'] );
			NotificationUtils::parse_emails_for_users( $users, $subject, $body, $post_ID );
			return;
		}
	}
}
