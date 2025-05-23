<?php

namespace Lasntg\Admin\Subscriptions\Notifications;

use Lasntg\Admin\Group\GroupUtils;
use Lasntg\Admin\Subscriptions\OptionPages\Editors;
use Lasntg\Admin\Subscriptions\SubscriptionPages\SubscriptionManager;

class NotificationUtils {

	public static $location_acf = 'field_63881b84798a5';
	public static $course_acf   = 'field_6387864196776';
	public static $per_page     = 5;
	/**
	 * Should be placed in groups plugin.
	 */
	public static function get_post_group_ids( $post_ID ) {
		// this is of the assumption it's the current group.
		if ( ! isset( $_POST['groups-read'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			return GroupUtils::get_read_group_ids( $post_ID );
		}
		return (array) array_map( 'sanitize_text_field', wp_unslash( $_POST['groups-read'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * @todo replace with QuotaUtils
	 */
	public static function get_group_quotas( $post_ID, $group_id ) {
		$value = get_post_meta( $post_ID, '_quotas_field_' . $group_id, true );
		if ( isset( $_POST[ '_quotas_field_' . $group_id ] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = sanitize_text_field( wp_unslash( $_POST[ '_quotas_field_' . $group_id ] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( empty( $value ) ) {
			return 1;
		}
		if ( is_numeric( $value ) ) {
			return (int) $value;
		}
		return $value;
	}

	public static function check_subscription( int $post_ID, array $users ): array {
		if ( ! $users ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			return [];
		}
		/**
		 * Check categories.
		 * Check location.
		 * Check course type.
		 */
		$product = new \WC_Product( $post_ID );
		$cat_ids = $product->get_category_ids();
		$course_type = get_field( self::$course_acf, $post_ID, true);
		
		foreach ( $users as $key => $user ) {
			// check if user has any checked options.
			// Category.
			$in_mailing_category = SubscriptionManager::confirm_meta( $user->ID, $cat_ids );

			// Event Type.
			$in_course = SubscriptionManager::confirm_meta( $user->ID, $course_type, 'course_type' );

			if ( ! $in_mailing_category || ! $in_course ) {
				// unset user from $users since they do not have any of the required options.
				unset( $users[ $key ] );
			}
		}

		return $users;
	}

	private static function filter_groups_with_quotas( $post_ID, $group_ids ) {
		return array_filter(
			$group_ids,
			function ( $group_id ) use ( $post_ID ) {
				return self::get_group_quotas( $post_ID, (int) $group_id ) > 0;
			}
		);
	}

	/**
	 * Get Users in group.
	 *
	 * @param  int    $post_ID Post ID.
	 * @param  string $role User Role.
	 * @param  string $page Page(count) to send to.
	 * @return array
	 */
	public static function get_users_in_group( $post_ID, $role = 'national_manager', $page = 1 ) {
		global $wpdb;
		$user_role       = "%$role%";
		$group_ids       = self::get_post_group_ids( $post_ID );
		$is_special_role = in_array( $role, [ 'training_officer', 'customer' ], true );

		// Filter groups with non-zero quota.
		if ( $is_special_role ) {
			$group_ids = self::filter_groups_with_quotas( $post_ID, $group_ids );
		}
		
		if ( empty( $group_ids ) ) {
			return [];
		}

		$params = array_merge( [ $user_role ], $group_ids );
		$offset = ( $page - 1 ) * self::$per_page;

		$query = "SELECT u.ID, u.display_name, u.user_email 
			FROM wp_users u
			INNER JOIN wp_usermeta um ON um.user_id = u.ID AND um.meta_key = 'wp_capabilities' AND um.meta_value LIKE %s
			INNER JOIN wp_groups_user_group g ON g.user_id = u.ID
			WHERE g.group_id IN (" . implode( ', ', array_fill( 0, count( $group_ids ), '%s' ) ) . ')
			GROUP BY u.ID
			ORDER BY u.ID ASC
			LIMIT %d OFFSET %d';

		$params[] = self::$per_page;
		$params[] = $offset;

		$results = $wpdb->get_results( $wpdb->prepare( $query, $params ) ); // phpcs:ignore

		if ( $is_special_role ) {
			$users_with_orders = self::get_users_by_product_orders_by_role( $post_ID );
			$results           = self::check_subscription( $post_ID, $results );
			$results           = array_merge( $users_with_orders, $results );

			$unique_results = [];
			foreach ( $results as $user ) {
				if ( ! isset( $unique_results[ $user->ID ] ) ) {
					$unique_results[ $user->ID ] = $user;
				}
			}
			$results = array_values( $unique_results );
		}

		return $results;
	}


	/**
	 * Get Users by Product Order and also by role.
	 *
	 * @param  int    $product_id Product ID.
	 * @param  string $role User Role.
	 * @param  array  $order_status default array( 'wc-cancelled', 'wc-processing', 'wc-completed' ).
	 * @return array
	 */
	public static function get_users_by_product_orders_by_role( $product_id, $role = 'training_officer', $order_status = array( 'wc-cancelled', 'wc-processing', 'wc-completed' ) ) {
		global $wpdb;
		$user_role = "%$role%";
		$args      = implode( ',', array_fill( 0, count( $order_status ), '%s' ) );
		/**
		 * Had to join users since woocommerce posts always have the post_ author as 1.
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
	public static function get_users_by_product_orders( $product_id, $order_status = [ 'wc-cancelled', 'wc-processing', 'wc-completed' ] ) {
		global $wpdb;
		$allowed_statuses = [ 'wc-cancelled', 'wc-processing', 'wc-completed' ];
		$order_status     = array_intersect( $order_status, $allowed_statuses );

		if ( empty( $order_status ) ) {
			return [];
		}

		$placeholders = implode( ', ', array_fill( 0, count( $order_status ), '%s' ) );

		$query = "
        SELECT DISTINCT users.ID, users.display_name, users.user_email
        FROM {$wpdb->prefix}woocommerce_order_items AS order_items
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta 
            ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts 
            ON order_items.order_id = posts.ID
        LEFT JOIN {$wpdb->postmeta} AS order_p_meta 
            ON order_p_meta.post_id = posts.ID AND order_p_meta.meta_key = '_customer_user'
        LEFT JOIN {$wpdb->users} AS users 
            ON users.ID = order_p_meta.meta_value
        WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ($placeholders)
            AND order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_product_id'
            AND order_item_meta.meta_value = %s
        ORDER BY users.display_name ASC
    ";

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array_merge( $order_status, [ $product_id ] ) )// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);

		return $results;
	}

	/**
	 * Get's email subject and body and is also parsed.
	 *
	 * @param  int    $post_ID Post ID.
	 * @param  string $subject Subject.
	 * @param  string $body Body.
	 * @return bool|array returns false or an array.
	 */
	public static function get_email_subject_and_body( $post_ID, $subject, $body ) {
		$email_subject = Editors::get_options( $subject );
		$email_body    = Editors::get_options( $body );
		if ( ! $email_subject || ! $email_body ) {
			return false;
		}
		return self::parse_info( $post_ID, $email_subject, $email_body );
	}

	/**
	 * Parse info.
	 *
	 * @param  int    $post_ID Post ID.
	 * @param  string $email_subject Subject.
	 * @param  string $email_body Body.
	 * @return array
	 */
	public static function parse_info( $post_ID, $email_subject, $email_body ): array {
		$email_subject = ParseEmail::add_course_info( $post_ID, $email_subject );
		$email_body    = ParseEmail::add_course_info( $post_ID, $email_body );

		$email_body = apply_filters( 'the_content', $email_body ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		return [
			'subject' => $email_subject,
			'body'    => $email_body,
		];
	}

	/**
	 * Get Content for users.
	 *
	 * @param  array  $users Users.
	 * @param  int    $post_ID Post ID.
	 * @param  string $subject Subject.
	 * @param  string $body Body.
	 * @return bool
	 */
	public static function get_content_for_users( $users, $post_ID, $subject, $body ): bool {
		$email = self::get_email_subject_and_body( $post_ID, $subject, $body );

		if ( ! $email ) {
			return false;
		}
		self::parse_emails_for_users( $users, $email['subject'], $email['body'], $post_ID );
		return true;
	}

	/**
	 * Get Content.
	 *
	 * @param  int    $post_ID Post ID.
	 * @param  string $subject Subject.
	 * @param  string $body Body.
	 * @param  string $user_role User Role.
	 * @return bool True|False.
	 */
	public static function get_content( $post_ID, $subject, $body, $user_role = 'national_manager', $page = 1 ) {
		$email = self::get_email_subject_and_body( $post_ID, $subject, $body );

		if ( ! $email ) {
			return false;
		}
		$email_subject = $email['subject'];
		$email_body    = $email['body'];
		$users         = self::get_users_in_group( $post_ID, $user_role, $page );
		self::parse_emails_for_users( $users, $email_subject, $email_body, $post_ID );

		return count( $users );
	}
	/**
	 * Parse Emails for users.
	 *
	 * @param  array  $users Users.
	 * @param  string $subject Subject.
	 * @param  string $body Body.
	 * @return void
	 */
	public static function parse_emails_for_users( $users, $subject, $body, $post_ID ): void {

		foreach ( $users as $user ) {
			$unique_body    = ParseEmail::add_receiver_info( $user, $body, $post_ID );
			$unique_subject = ParseEmail::add_receiver_info( $user, $subject, $post_ID );
			if ( gettype( $user ) == 'string' || gettype( $user ) == 'integer' ) {
				$user = get_userdata( $user );
			}
			self::send_mail( $user->user_email, $unique_subject, $unique_body );
		}
	}

	/**
	 * Send Mail.
	 *
	 * @param  string $email User email address.
	 * @param  string $subject Subject.
	 * @param  string $body Body.
	 * @return bool
	 */
	public static function send_mail( $email, $subject, $body ): bool {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$body .= '<p>Please log in to the LASNTG Dashboard to see details - <strong><a href="' . get_option( 'siteurl' ) . '/wp-admin/" target="_blank" rel="noopener">LASNTG OBS login</a></strong></p>
<p>Kind Regards,</p>
<p>LASNTG<br/></p>
<p>Grúpa Oiliúna Náisiúnta um Sheirbhísí Údaráis Áitiúil |Aonad 4/5, Cúirt an Bhráthar|An tAonach |Tiobraid Árann</p>

<p>Local Authority Services National Training Group| Unit 4/5, Friar’s Court | Nenagh | County Tipperary</p>

<p>T: <a href="tel:+353526166260">+353 52 616 6260</a> | E: <a href="lasntg@tipperarycoco.ie">lasntg@tipperarycoco.ie</a> | <a href="www.lasntg.ie">www.lasntg.ie</a></p>

<p><img src="cid:lasntg-logo" width="100" alt="lasntg"></p>';

		return wp_mail( $email, $subject, $body, $headers );
	}
}
