<?php

namespace Lasntg\Admin\Subscriptions\Notifications;

use Lasntg\Admin\Subscriptions\OptionPages\Editors;

class NotificationUtils {



	/**
	 * Should be placed in groups plugin.
	 */
	public static function get_post_group_ids( $post_ID ) {
		return \Groups_Post_Access::get_read_group_ids( $post_ID );
	}
	public static function get_users_in_group( $post_ID, $role = 'national_manager' ) {
		global $wpdb;

		$user_role = "%$role%";
		$group_ids = self::get_post_group_ids( $post_ID );
		$users     = [];
		// check quotas for training officer.
		// remove groups with zero quotas.
		if ( 'training_officer' === $role ) {
			foreach ( $group_ids as $key => $group_id ) {
				$value = get_post_meta( $post_ID, '_quotas_field_' . $group_id, true );

				if ( is_numeric( $value ) && 0 === (int) $value ) {
					unset( $group_ids[ $key ] );
				}
			}
			// add users with orders. will find those that are excluded by group.
			$users = self::get_users_by_product_orders_by_role( $post_ID );
		}
		if ( ! $group_ids ) {
			return [];
		}

		$params   = array_merge( [ $user_role ], $group_ids );
		$string_s = implode( ', ', array_fill( 0, count( $group_ids ), '%s' ) );
		$query    = "select u.ID, u.display_name, u.user_email From wp_users u
                  INNER JOIN {$wpdb->usermeta} um
                  on um.user_id = u.ID
                  INNER JOIN wp_groups_user_group g
                  on g.user_id = u.ID
                  where meta_value like %s
                  AND meta_key = 'wp_capabilities'
                  AND g.group_id in ($string_s) GROUP BY u.ID";

		$results = $wpdb->get_results( $wpdb->prepare( $query, $params ) ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		// get unique users rather than duplicates.
		if ( 'training_officer' === $role ) {
			$results  = array_merge( $users, $results );
			$user_ids = [];
			foreach ( $results as $key => $user ) {
				if ( in_array( $user->ID, $user_ids ) ) {
					unset( $results[ $key ] );
					continue;
				}
				$user_ids[] = $user->ID;
			}
		}
		return $results;
	}

	public static function get_users_by_product_orders_by_role( $product_id, $role = 'training_officer', $order_status = array( 'wc-cancelled', 'wc-processing', 'wc-completed' ) ) {
		global $wpdb;
		$user_role = "%$role%";
		$args      = implode( ',', array_fill( 0, count( $order_status ), '%s' ) );
		/**
		 * had to join users since woocommerce posts always have the post_ author as 1
		 */
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT users.ID, users.display_name, users.user_email
			FROM {$wpdb->prefix}woocommerce_order_items as order_items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
			LEFT JOIN {$wpdb->postmeta} as order_p_meta ON order_p_meta.post_id = posts.ID
			LEFT JOIN {$wpdb->users} as users ON users.ID = order_p_meta.meta_value 
			INNER JOIN {$wpdb->usermeta} um on um.user_id = users.ID
			WHERE posts.post_type = 'shop_order'
			AND posts.post_status IN ( $args )" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					. "AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_product_id'
			AND order_p_meta.meta_key = '_customer_user'
			AND order_item_meta.meta_value = %s
			AND um.meta_value like %s
            AND um.meta_key = 'wp_capabilities'
            GROUP BY users.ID
			",
				array_merge( $order_status, [ $product_id, $user_role ] )
			)
		);

		return $results;
	}
	/**
	 * Get All orders IDs for a given product ID.
	 *
	 * @param  integer $product_id (required).
	 * @param  array   $order_status (optional) Default is ['wc-cancelled', 'wc-processing', 'wc-completed'].
	 *
	 * @return array
	 */
	public static function get_users_by_product_orders( $product_id, $order_status = array( 'wc-cancelled', 'wc-processing', 'wc-completed' ) ) {
		global $wpdb;
		$args = implode( ',', array_fill( 0, count( $order_status ), '%s' ) );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT users.ID, users.display_name, users.user_email
			FROM {$wpdb->prefix}woocommerce_order_items as order_items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
			LEFT JOIN {$wpdb->postmeta} as order_p_meta ON order_p_meta.post_id = posts.ID
            LEFT JOIN {$wpdb->users} as users ON users.ID = order_p_meta.meta_value
			WHERE posts.post_type = 'shop_order'
			AND posts.post_status IN ( $args )" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					. "AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_product_id'
			AND order_item_meta.meta_value = %s
            GROUP BY users.ID
			",
				array_merge( $order_status, [ $product_id ] )
			)
		);
		return $results;
	}

	protected static function get_email_subject_and_body( $post_ID, $subject, $body ) {
		$email_subject = Editors::get_options( $subject );
		$email_body    = Editors::get_options( $body );
		if ( ! $email_subject || ! $email_body ) {
			return false;
		}
		return self::parse_info( $post_ID, $email_subject, $email_body );
	}

	public static function parse_info( $post_ID, $email_subject, $email_body ) {
		$email_subject = ParseEmail::add_course_info( $post_ID, $email_subject );
		$email_body    = ParseEmail::add_course_info( $post_ID, $email_body );
		$email_body    = apply_filters( 'the_content', $email_body ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		return [
			'subject' => $email_subject,
			'body'    => $email_body,
		];
	}

	public static function get_content_for_users( $users, $post_ID, $subject, $body ) {
		$email = self::get_email_subject_and_body( $post_ID, $subject, $body );

		if ( ! $email ) {
			return false;
		}
		self::parse_emails_for_users( $users, $email['subject'], $email['body'] );
	}

	public static function get_content( $post_ID, $subject, $body, $user_role = 'national_manager' ) {
		$email = self::get_email_subject_and_body( $post_ID, $subject, $body );
		if ( ! $email ) {
			return false;
		}
		$email_subject = $email['subject'];
		$email_body    = $email['body'];
		$users         = self::get_users_in_group( $post_ID, $user_role );
		error_log( 'Users: ' . json_encode( $users ) );
		self::parse_emails_for_users( $users, $email_subject, $email_body );
	}
	public static function parse_emails_for_users( $users, $subject, $body ) {
		foreach ( $users as $user ) {
			$unique_body    = ParseEmail::add_receiver_info( $user, $body );
			$unique_subject = ParseEmail::add_receiver_info( $user, $subject );
			self::send_mail( $user->user_email, $unique_subject, $unique_body );
		}
	}

	protected static function send_mail( $email, $subject, $body ) {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$sent    = wp_mail( $email, $subject, $body, $headers );
		error_log( "Sent: $sent, $email: $subject" );
	}
}
