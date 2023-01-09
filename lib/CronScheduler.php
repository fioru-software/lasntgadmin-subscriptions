<?php

namespace Lasntg\Admin\Subscriptions;

require_once '/var/www/html/wp-content/plugins/smtp-mailing-queue/classes/SMTPMailingQueue.php';
require_once '/var/www/html/wp-content/plugins/smtp-mailing-queue/classes/OriginalPluggeable/OriginalPluggeable.php';

use SMTPMailingQueue, OriginalPluggeable;

class CronScheduler {

	const HOOK = 'lasntgadmin_mail_queue_hook';

	public static function add_actions() {
		$smtp_mailing_queue = new SMTPMailingQueue( null, new OriginalPluggeable() );
		add_action( self::HOOK, [ $smtp_mailing_queue, 'processQueue' ], 50 );
	}

	public static function add_filters() {
		add_filter( 'cron_schedules', [ self::class, 'add_schedule' ] );
	}

	public static function add_schedule() {
		$schedules['tenseconds'] = array(
			'interval' => 10,
			'display'  => __( 'Ten Seconds', 'lasntgadmin' ),
		);
		return $schedules;
	}

	public static function add_event() {
		$args = [ false ];
		if ( ! wp_next_scheduled( self::HOOK, $args ) ) {
			wp_schedule_event( time(), 'tenseconds', self::HOOK, $args, true );
		}
	}

	public static function remove_event() {
		wp_clear_scheduled_hook( self::HOOK );
	}

}
