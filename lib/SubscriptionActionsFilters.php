<?php

namespace Lasntg\Admin\Subscriptions;

use DateTime;
use Lasntg\Admin\Group\GroupUtils;
use Lasntg\Admin\Orders\OrderUtils;
use Lasntg\Admin\Products\ProductUtils;
use Lasntg\Admin\Products\QuotaUtils;
use Lasntg\Admin\Subscriptions\Notifications\ManagersNotifications;
use Lasntg\Admin\Subscriptions\Notifications\NotificationUtils;
use Lasntg\Admin\Subscriptions\Notifications\PrivateNotifications;
use Lasntg\Admin\Subscriptions\Notifications\RegionalManagerNotifications;
use Lasntg\Admin\Subscriptions\Notifications\TrainingCenterNotifications;
use Lasntg\Admin\Subscriptions\SubscriptionPages\SubscriptionManager;

class SubscriptionActionsFilters {

	private static $action = 'lasntgadmin-enrolment';
	public static function init(): void {
		add_action( 'post_updated', [ self::class, 'post_updated' ], 999, 3 );
		add_action( 'wp_insert_post', [ self::class, 'wp_insert_post' ], 1, 2 );
		add_filter( 'tag_row_actions', [ self::class, 'add_subscription_link_to_woocommerce_category' ], 10, 2 );

		add_action( 'wp_ajax_lasntgadmin_subscribe', [ self::class, 'subscribe' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'admin_enqueue_scripts' ] );
		add_action( 'woocommerce_order_status_changed', [ self::class, 'order_cancelled' ], 10, 3 );
		add_action( 'woocommerce_order_status_changed', [ self::class, 'waiting_list_order_updated' ], 10, 3 );

		add_action( 'lasntgadmin-products_quotas_field_changed', [ self::class, 'quotas_changed' ], 10, 4 );
		add_action( 'save_post_product', [ self::class, 'save_post' ], 100 );
		add_action( 'woocommerce_order_status_changed', [ self::class, 'order_cancelled' ], 10, 3 );
		add_action( 'woocommerce_order_status_completed', [ self::class, 'new_enrolment_completed' ], 10, 2 );

		add_action( 'phpmailer_init', [ self::class, 'add_logo_to_mail' ] );
		add_action( 'lasntgadmin_course_notifications', [ self::class, 'lasntgadmin_course_notifications' ], 10, 4 );

		add_action( 'lasntgadmin_start_new_course_notifications', [ self::class, 'lasntgadmin_start_new_course_notifications' ], 10, 1 );
		add_action( 'lasntgadmin_start_course_updated_notifications', [ self::class, 'lasntgadmin_start_course_updated_notifications' ], 10, 1 );
		add_action( 'lasntgadmin_start_course_cancelled_notifications', [ self::class, 'lasntgadmin_start_course_cancelled_notifications' ], 10, 1 );
	}

	public static function lasntgadmin_start_course_cancelled_notifications( $post_ID ): void {
		$subject = get_post_meta( $post_ID, '_cancellation_subject', true );
		$body    = get_post_meta( $post_ID, '_cancellation_message', true );

		$email = NotificationUtils::parse_info( $post_ID, $subject, $body );

		if ( $subject && $body ) {
			ManagersNotifications::custom_cancelletaion( $post_ID, $email['subject'], $email['body'] );
			RegionalManagerNotifications::custom_cancelletaion( $post_ID, $email['subject'], $email['body'] );
			TrainingCenterNotifications::custom_cancelletaion( $post_ID, $email['subject'], $email['body'] );
			PrivateNotifications::custom_cancelletaion( $post_ID, $email['subject'], $email['body'] );
		} else {
			ManagersNotifications::course_cancelled( $post_ID );
			RegionalManagerNotifications::course_cancelled( $post_ID );
			TrainingCenterNotifications::course_cancelled( $post_ID );
			PrivateNotifications::course_cancelled( $post_ID );
		}
	}

	public static function lasntgadmin_start_course_updated_notifications( $post_ID ): void {
		ManagersNotifications::course_updated( $post_ID );
		RegionalManagerNotifications::course_updated( $post_ID );
		TrainingCenterNotifications::course_updated( $post_ID );
		PrivateNotifications::course_updated( $post_ID );
	}

	public static function lasntgadmin_start_new_course_notifications( $post_ID ): void {
		ManagersNotifications::new_course( $post_ID );
		TrainingCenterNotifications::new_course( $post_ID );
		RegionalManagerNotifications::new_course( $post_ID );
		PrivateNotifications::new_course( $post_ID );
	}

	public static function lasntgadmin_course_notifications( $page, $post_ID, $cls, $method ): void {
		if ( 'custom_cancelletaion' == $method ) {
			$subject = get_post_meta( $post_ID, '_cancellation_subject', true );
			$body    = get_post_meta( $post_ID, '_cancellation_message', true );

			$email = NotificationUtils::parse_info( $post_ID, $subject, $body );
			call_user_func( [ $cls, $method ], $post_ID, $email['subject'], $email['body'], $page );
			return;
		}
		call_user_func( [ $cls, $method ], $post_ID, $page );
	}
	public static function add_logo_to_mail( &$phpmailer ) {

		$assets_dir = __DIR__ . '/../assets/';

		$file = $assets_dir . 'img/logo.png';
		$uid  = 'lasntg-logo';
		$name = 'logo.png';

		$phpmailer->SMTPKeepAlive = true; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$phpmailer->AddEmbeddedImage( $file, $uid, $name );
	}

	public static function new_enrolment_completed( $order_id ): void {
		Notifications::new_enrollment( $order_id );
	}

	public static function order_cancelled( $order_id, $old_status, $new_status ): void {

		if ( 'cancelled' !== $new_status ) {
			return;
		}
		if (
			'pending' !== $old_status && 'on-hold' !== $old_status &&
			'completed' !== $old_status
		) {
			return;
		}
		$order = wc_get_order( $order_id );
		$items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		if ( ! $items ) {
			return;
		}
		$item = array_shift( $items );

		$product_id   = $item->get_product_id();
		$order_groups = GroupUtils::get_read_group_ids( $order_id );
		$group_quotas = self::get_groups_quotas( $order_groups, $product_id );
		$sum          = 0;
		foreach ( $group_quotas as $group_quota ) {
			$sum += $group_quota;
		}
		$product = \wc_get_product( $product_id );
		// check if the course had more empty spaces than the order quantity.
		if ( $sum - $item->get_quantity() > 0 ) {
			return;
		}
		if ( ! ProductUtils::is_open_for_enrollment_by_product_id( $product_id ) ) {
			return;
		}
		if ( $order_groups ) {
			self::process_quotas_changed( $product_id, $order_groups );
		}
	}

	private static function process_group( $post_ID ): void {
		$groups  = GroupUtils::get_read_group_ids( $post_ID );
		$allowed = [];
		foreach ( $groups as $group_id ) {
			$quota = QuotaUtils::get_product_quota( $post_ID, false, $group_id );

			if ( '' === $quota || (int) $quota > 0 ) {
				$allowed[] = $group_id;
			}
		}
		if ( $allowed ) {
			self::process_quotas_changed( $post_ID, $allowed );
		}
	}

	public static function save_post( $post_ID ): void {
		if ( ! ProductUtils::is_open_for_enrollment_by_product_id( $post_ID ) ) {
			return;
		}
		$old_stock = get_post_meta( $post_ID, '_stock', true );
		if ( ! isset( $_POST['_stock'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		$new_stock = sanitize_text_field( wp_unslash( $_POST['_stock'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( 0 < $old_stock ) {
			return;
		}
		$product = wc_get_product( $post_ID );
		$product->set_stock_quantity( $new_stock );
		$product->save();

		self::process_group( $post_ID );
	}


	public static function identify_product_change( $product ): void {
		$product_id = $product->get_id();
		$old        = \wc_get_product( $product_id );

		$old_stock = $old->get_stock_quantity();
		$new_stock = $product->get_stock_quantity();
		if ( $new_stock > $old_stock ) {
			$groups  = GroupUtils::get_read_group_ids( $product_id );
			$allowed = [];
			foreach ( $groups as $group_id ) {
				$quota = QuotaUtils::get_product_quota( $product_id, false, $group_id );

				if ( '' === $quota || (int) $quota > 0 ) {
					$allowed[] = $group_id;
				}
			}
			if ( $allowed ) {
				self::process_quotas_changed( $product_id, $allowed );
			}
		}
	}
	private static function get_groups_quotas( $groups_allowed, $product_id ) {
		$groups_quotas = [];
		foreach ( $groups_allowed as $group_id ) {
			$quota = QuotaUtils::get_product_quota( $product_id, false, $group_id );
			if ( $quota > 0 ) {
				$groups_quotas[ $group_id ] = $quota;
			}
		}
		return $groups_quotas;
	}

	private static function process_quotas_changed( $post_id, $groups_allowed ): void {
		$order_ids    = ProductUtils::get_orders_ids_by_product_id( $post_id, [ 'wc-waiting-list' ] );
		$group_quotas = self::get_groups_quotas( $groups_allowed, $post_id );

		// make sure user doesn't get multiple notifications.
		$user_ids = [];
		foreach ( $order_ids as $order_id ) {
			$order   = wc_get_order( $order_id );
			$user_id = $order->get_user_id();

			if ( $user_id ) {
				if ( in_array( $user_id, $user_ids ) ) {
					continue;
				}
				$user          = get_user_by( 'ID', $user_id );
				$role          = self::check_user_role( $user );
				$allowed_roles = [ 'training_officer', 'customer' ];

				if ( ! in_array( $role, $allowed_roles ) ) {
					continue;
				}

				$user_groups = GroupUtils::get_group_ids_by_user_id( $user_id );
				$allowed     = array_intersect( $user_groups, array_keys( $group_quotas ) );

				if ( ! $allowed ) {
					$user_ids[] = $user_id;
					continue;
				}

				if ( 'customer' == $role ) {
					PrivateNotifications::space_available( $post_id, $user, get_permalink( $post_id ) );
				} else {
					$nonce        = wp_generate_password( 12, false );
					$attendee_url = admin_url( 'post.php?post=' . $order->get_id() ) . '&action=edit&email_notification=' . $nonce . '&tab=order';

					update_post_meta( $order->get_id(), self::$action . "_$user_id", $nonce );
					TrainingCenterNotifications::space_available( $post_id, $user, $attendee_url );
				}

				$user_ids[] = $user_id;
			}//end if
		}//end foreach
	}

	public static function quotas_changed( $post_id, $group_id, $old_value, $new_value ): void {
		if (
			'' == $old_value
			|| ! ProductUtils::is_open_for_enrollment_by_product_id( $post_id )
		) {
			return;
		}
		if ( '' == $new_value || (int) $new_value > (int) $old_value ) {
			$quota = QuotaUtils::get_product_quota( $post_id, false, $group_id );
			if ( $quota ) {
				self::process_quotas_changed( $post_id, [ $group_id ] );
			}
		}
	}

	/**
	 * Check user role. If the current user role is in the needed roles.
	 *
	 * @todo should be moved to groups plugin.
	 * @param  mixed $user WP_User.
	 * @return bool|string Returns role or false.
	 */
	private static function check_user_role( $user ) {
		$roles        = (array) $user->roles;
		$needed_roles = [ 'national_manager', 'regional_training_centre_manager', 'training_officer', 'customer' ];
		foreach ( $needed_roles as $role ) {
			if ( in_array( $role, $roles ) !== false ) {
				return $role;
			}
		}
		return false;
	}


	public static function waiting_list_order_updated( $order_id, $old_status, $new_status ): void {
		if (
			'waiting-list' !== $old_status
			|| 'pending' !== $new_status
		) {
			self::order_cancelled( $order_id, $old_status, $new_status );
			return;
		}
		$order = wc_get_order( $order_id );
		$items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		if ( ! $items ) {
			return;
		}
		$item       = array_shift( $items );
		$product_id = $item->get_product_id();
		$user_id    = $order->get_user_id();
		if ( $user_id ) {
			$user = get_user_by( 'ID', $user_id );
			$role = self::check_user_role( $user );
			if ( 'customer' == $role ) {
				PrivateNotifications::space_available_waiting_list_pending( $product_id, $user, $order->get_checkout_payment_url() );
			}
		}
	}
	public static function admin_enqueue_scripts(): void {
		$screen = get_current_screen();
		if ( $screen && 'edit-tags' === $screen->base ) {
			$assets_dir = untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/../assets/';

			wp_enqueue_script( 'lasntgadmin-subscription-subscribe-js', ( $assets_dir . 'js/lasntgadmin-admin-subscription.js' ), array( 'jquery' ), '1.0', true );

			$nonce = wp_create_nonce( 'lasntgadmin-subscription-subscribe-nonce' );
			wp_localize_script(
				'lasntgadmin-subscription-subscribe-js',
				'lasntgadmin_subscription_localize',
				array(
					'adminurl'        => admin_url() . 'admin-ajax.php',
					'subscribe_nonce' => $nonce,
				)
			);
		}
	}
	public static function subscribe(): void {
		check_ajax_referer( 'lasntgadmin-subscription-subscribe-nonce', 'security' );

		if ( ! isset( $_POST['id'] ) ) {
			wp_send_json(
				[
					'status' => 0,
					'msgs'   => [ __( 'Error! no ID', 'lasntgadmin' ) ],
				]
			);
			return;
		}
		$id  = sanitize_text_field( wp_unslash( $_POST['id'] ) );
		$res = SubscriptionManager::add_remove_user( get_current_user_id(), $id );
		wp_send_json(
			[
				'status' => $res,
				'msgs'   => [ __( 'updated', 'lasntgadmin' ) ],
			]
		);
	}
	public static function add_subscription_link_to_woocommerce_category( $actions, $post ): array {
		if ( 'product_cat' !== $post->taxonomy ) {
			return $actions;
		}
		$in                          = SubscriptionManager::user_in_mailing_list( get_current_user_id(), $post->term_id );
		$actions['lasntg_subscribe'] = '<a href="javascript:void(0)" data-id="' . $post->term_id . '" class="lasntgadmin_subscription">' .
			( $in ? 'Unsubscribe' : 'Subscribe' )
			. '</a>';
		return $actions;
	}

	public static function post_updated( $post_ID, $post_after, $post_before ) {
		if ( 'product' !== $post_after->post_type ) {
			return $post_ID;
		}
		if ( $post_after->post_status !== $post_before->post_status ) {
			if ( ProductUtils::$publish_status === $post_after->post_status ) {
				Notifications::open_for_enrollment( $post_ID );
				return $post_ID;
			}

			if ( 'cancelled' === $post_after->post_status ) {
				Notifications::course_cancelled( $post_ID );
				return $post_ID;
			} elseif ( 'open_for_enrollment' === $post_after->post_status ) {
				Notifications::open_for_enrollment( $post_ID );
				return $post_ID;
			} else {
				Notifications::course_status_change( $post_ID );
				return $post_ID;
			}
		} elseif (
			'draft' === $post_after->post_status
			|| ( 'cancelled' === $post_after->post_status &&
				'cancelled' === $post_before->post
			)
		) {
			// do not send notifications for drafts or cancelled.
			return $post_ID;
		}//end if

		if ( ProductUtils::$publish_status !== $post_after->post_status ) {
			return $post_ID;
		}
		$new_location = isset( $_POST['acf'] ) && isset( $_POST['acf']['field_63881b84798a5'] ) ? sanitize_text_field( wp_unslash( $_POST['acf']['field_63881b84798a5'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$old_location = get_field( 'field_63881b84798a5', $post_ID );

		$new_start_date = isset( $_POST['acf'] ) && isset( $_POST['acf']['field_63881aee31478'] ) ? sanitize_text_field( wp_unslash( $_POST['acf']['field_63881aee31478'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$old_start_date = get_field( 'field_63881aee31478', $post_ID );

		$date1 = \DateTime::createFromFormat( 'd/m/Y', $old_start_date );
		$date2 = \DateTime::createFromFormat( 'Ymd', $new_start_date );

		if ( $new_location !== $old_location || $date1 != $date2 ) {
			Notifications::course_updated( $post_ID );
		}
		return $post_ID;
	}

	public static function wp_insert_post( $post_id, $post ) {
		if (
			'product' === $post->post_type
			&& 'open_for_enrollment' === $post->post_status
			&& ( empty( get_post_meta( $post_id, 'check_if_run_once', true ) ) || (int) get_post_meta( $post_id, 'check_if_run_once', true ) !== (int) $post_id )
		) {
			Notifications::new_course( $post_id );
			// And update the meta so it won't run again.
			update_post_meta( $post_id, 'check_if_run_once', $post_id );
		}
		return;
	}
}
