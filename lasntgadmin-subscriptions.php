<?php
/**
 * Plugin Name:       LASNTG Admin Subscriptions
 * Plugin URI:        https://github.com/fioru-software/lasntgadmin-subscriptions
 * Description:       Manages subscriptions to courses.
 * Version:           2.1.2
 * Requires PHP:      7.2
 * Requires PHP:      7.2
 * Text Domain:       lasntgadmin
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

// composer autoloading.
require_once getenv( 'COMPOSER_AUTOLOAD_FILEPATH' );

use Lasntg\Admin\Subscriptions\{ PluginUtils, SubscriptionActionsFilters, CustomMessages};
use Lasntg\Admin\Subscriptions\OptionPages\{NationalManagerOptions, TrainingOfficersOptions, PrivateClientOptions, RegionalManagerOptions};
use Lasntg\Admin\Subscriptions\SubscriptionPages\{TrainingOfficerOptionPage, PrivateClientPage};

register_activation_hook( __FILE__, [ PluginUtils::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ PluginUtils::class, 'deactivate' ] );

SubscriptionActionsFilters::init();
NationalManagerOptions::init();
TrainingOfficersOptions::init();
RegionalManagerOptions::init();
PrivateClientOptions::init();
CustomMessages::init();
TrainingOfficerOptionPage::init();
PrivateClientPage::init();
