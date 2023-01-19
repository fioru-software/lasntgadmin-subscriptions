<?php

namespace Lasntg\Admin\Subscriptions;

use Lasntg\Admin\Subscriptions\Traits\ManagersNotifications;
use Lasntg\Admin\Subscriptions\Traits\TrainingCenterNotifications;

class Notifications {


	use ManagersNotifications;
	use TrainingCenterNotifications;


	/**
	 * Should be placed in groups plugin.
	 */
	private static function get_post_group_ids( $post_ID ) {
		return \Groups_Post_Access::get_read_group_ids( $post_ID );
	}
	protected static function get_users_in_group( $post_ID, $user_role = 'national_manager' ) {
		global $wpdb;
		$user_role = "%$user_role%";
		$group_ids = self::get_post_group_ids( $post_ID );
		$params    = array_merge( [ $user_role ], $group_ids );
		$string_s  = implode( ', ', array_fill( 0, count( $group_ids ), '%s' ) );
		$query     = "select u.ID, u.display_name, u.user_email From wp_users u
                  INNER JOIN wp_usermeta um
                  on um.user_id = u.ID
                  INNER JOIN wp_groups_user_group g
                  on g.user_id = u.ID
                  where meta_value like %s
                  and meta_key = 'wp_capabilities'
                  and g.group_id in ($string_s)";
		return $wpdb->get_results( $wpdb->prepare( $query, $params ) ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get All orders IDs for a given product ID.
	 *
	 * @param  integer $product_id (required).
	 * @param  array   $order_status (optional) Default is ['wc-processing','wc-completed'].
	 *
	 * @return array
	 */
	public static function get_users_by_product_orders( $product_id, $order_status = array( 'wc-processing', 'wc-completed' ) ) {
		global $wpdb;
		$args = implode( ',', array_fill( 0, count( $order_status ), '%s' ) );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT users.ID, users.display_name, users.user_email
			FROM {$wpdb->prefix}woocommerce_order_items as order_items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            LEFT JOIN {$wpdb->users} as users ON users.ID = posts.post_author
			WHERE posts.post_type = 'shop_order'
			AND posts.post_status IN ( $args )" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				. "AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_product_id'
			AND order_item_meta.meta_value = %s
            GROUP BY posts.post_author
			",
				array_merge( $order_status, [ $product_id ] )
			)
		);

		return $results;
	}

	public static function course_cancelled( $post_ID ) {
		// notify Managers.
		self::notify_managers_course_cancellation( $post_ID );
		// orders should be cancelled already.
		// notify all users that had orders.
		$users = self::get_users_by_product_orders( $post_ID, [ 'wc-cancelled' ] );

		self::get_content_for_users( $users, $post_ID, 'course_cancellation_subject', 'course_cancellation' );
	}

	protected static function get_email_subject_and_body( $post_ID, $subject, $body ) {
		$email_subject = Editors::get_options( $subject );
		$email_body    = Editors::get_options( $body );

		$email_subject = ParseEmail::add_course_info( $post_ID, $email_subject );
		$email_body    = ParseEmail::add_course_info( $post_ID, $email_body );
		$email_body    = apply_filters( 'the_content', $email_body ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		return [
			'subject' => $email_subject,
			'body'    => $email_body,
		];
	}

	protected static function get_content_for_users( $users, $post_ID, $subject, $body ) {
		$email = self::get_email_subject_and_body( $post_ID, $subject, $body );

		self::parse_emails_for_users( $users, $email['subject'], $email['body'] );
	}

	protected static function get_content( $post_ID, $subject, $body ) {
		$email         = self::get_email_subject_and_body( $post_ID, $subject, $body );
		$email_subject = $email['subject'];
		$email_body    = $email['body'];

		$users = self::get_users_in_group( $post_ID );
		self::parse_emails_for_users( $users, $email_subject, $email_body );
	}
	protected static function parse_emails_for_users( $users, $subject, $body ) {
		foreach ( $users as $user ) {
			$unique_body    = ParseEmail::add_receiver_info( $user, $body );
			$unique_subject = ParseEmail::add_receiver_info( $user, $subject );
			self::send_mail( $user->user_email, $unique_subject, $unique_body );
		}
	}

	protected static function send_mail( $email, $subject, $body ) {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$sent    = wp_mail( $email, $subject, $body, $headers );
		$sent    = wp_mail( 'test@mail.com', 'test subject', 'body', $headers );
	}
}
