<?php

namespace Lasntg\Admin\Subscriptions;

use Lasntg\Admin\Products\ProductUtils;
use Lasntg\Admin\Subscriptions\SubscriptionPages\SubscriptionManager;

class SubscriptionActionsFilters {
	public static function init(): void {
		add_action( 'post_updated', [ self::class, 'post_updated' ], 999, 3 );
		add_action( 'wp_insert_post', [ self::class, 'wp_insert_post' ], 1, 2 );
		add_filter( 'tag_row_actions', [ self::class, 'add_subscription_link_to_woocommerce_category' ], 10, 2 );

		add_action( 'wp_ajax_lasntgadmin_subscribe', [ self::class, 'subscribe' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'admin_enqueue_scripts' ] );
	}

	public static function admin_enqueue_scripts() {
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
	public static function subscribe() {
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
	public static function add_subscription_link_to_woocommerce_category( $actions, $post ) {
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
		Notifications::course_updated( $post_ID );
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
