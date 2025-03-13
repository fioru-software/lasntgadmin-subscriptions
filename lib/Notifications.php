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
		as_schedule_single_action(
			time() + 60,
			// Run after 1 min.
			'lasntgadmin_start_course_cancelled_notifications',
			array( 'post_ID' => $post_ID ),
			'lasntgadmin-subscriptions'
		);
	}

	public static function course_updated( $post_ID ): void {
		as_schedule_single_action(
			time() + 60,
			// Run after 1 min.
			'lasntgadmin_start_course_updated_notifications',
			array( 'post_ID' => $post_ID ),
			'lasntgadmin-subscriptions'
		);
	}
	public static function course_status_change( $post_ID ): void {
		return;
		TrainingCenterNotifications::status_changed( $post_ID );
		ManagersNotifications::status_changed( $post_ID );
		RegionalManagerNotifications::status_changed( $post_ID );
		PrivateNotifications::status_changed( $post_ID );
	}
	public static function open_for_enrollment( $post_ID ): void {
		return;
		TrainingCenterNotifications::open_for_enrollment( $post_ID );
		ManagersNotifications::open_for_enrollment( $post_ID );
		RegionalManagerNotifications::open_for_enrollment( $post_ID );
		PrivateNotifications::open_for_enrollment( $post_ID );
	}

	public static function new_course( $post_ID ): void {
		as_schedule_single_action(
			time() + 60,
			// Run after 1 min.
			'lasntgadmin_start_new_course_notifications',
			array( 'post_ID' => $post_ID ),
			'lasntgadmin-subscriptions'
		);
	}

	public static function new_enrollment( $order_id ): void {
		$order = wc_get_order( $order_id );

		$user_id = $order->get_user_id();
		$items   = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		if ( ! $items ) {
			return;
		}

		// Get the WP_User Object instance.
		$user       = $order->get_user();
		$roles      = (array) $user->roles;
		$order_link = get_edit_post_link( $order_id );
		if ( in_array( 'customer', $roles ) ) {
			$order_link = $order->get_view_order_url();
		}

		$item       = array_shift( $items );
		$product_id = $item->get_product_id();

		$subject = 'New enrolment is successfully created for course {%name%} | {%start_date%}';
		$body    = '<p data-renderer-start-pos="216">Hi  {%to_user_name%}</p>
<p data-renderer-start-pos="238"><a href="' . $order_link . '">New enrolment</a> for course {%name%} is created successfuly.
Course Start Date: {%start_date%}
Course Location: {%location%}</p>';
		$info    = NotificationUtils::parse_info( $product_id, $subject, $body );

		NotificationUtils::parse_emails_for_users( [ $user_id ], $info['subject'], $info['body'], $product_id );
	}
}
