<?php

namespace Lasntg\Admin\Subscriptions;

use Lasntg\Admin\Subscriptions\{ Capabilities, CronScheduler };

/**
 * Plugin utilities
 */
class PluginUtils {

	public static function activate() {
		Capabilities::add();
		CronScheduler::add_event();
	}

	public static function deactivate() {
		Capabilities::remove();
		CronScheduler::remove_event();
	}

	public static function get_camel_case_name(): string {
		return 'lasntgadmin_orders';
	}

	public static function get_kebab_case_name(): string {
		return 'lasntgadmin-orders';
	}

	public static function get_absolute_plugin_path(): string {
		return sprintf( '/var/www/html/wp-content/plugins/%s', self::get_kebab_case_name() );
	}
}


