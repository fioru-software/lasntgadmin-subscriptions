<?php
/**
 * Plugin Name:       LASNTG Admin Subscriptions
 * Plugin URI:        https://github.com/fioru-software/lasntgadmin-subscriptions
 * Description:       Manages subscriptions to courses.
 * Version:           1.0.0
 * Requires PHP:      7.2
 * Text Domain:       lasntgadmin
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

// composer autoloading.
require_once getenv( 'COMPOSER_AUTOLOAD_FILEPATH' );

use Lasntg\Admin\Subscriptions\{ PluginUtils, CronScheduler, SubscriptionActionsFilters, OptionsPage };


register_activation_hook( __FILE__, [ PluginUtils::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ PluginUtils::class, 'deactivate' ] );

CronScheduler::add_filters();
CronScheduler::add_actions();
SubscriptionActionsFilters::init();
OptionsPage::init();

