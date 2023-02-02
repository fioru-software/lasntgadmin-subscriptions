<?php

namespace Lasntg\Admin\Subscriptions\Notifications;

class PrivateNotifications extends BaseNotification {
	protected static $option_name = 'lasntg_subscriptions_private';
	protected static $user_role   = '';

	public static function course_cancellation( $post_ID ) {
		self::set_option_name();
	}
}
