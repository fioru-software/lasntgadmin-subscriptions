<?php

namespace Lasntg\Admin\Subscriptions\Notifications;

use Lasntg\Admin\Subscriptions\OptionPages\Editors;

class RegionalManagerNotifications extends BaseNotification {
	protected static $option_name = 'lasntg_subscriptions_regional_options';
	public static $user_role      = 'regional_training_centre_manager';
}
