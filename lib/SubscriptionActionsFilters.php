<?php

namespace Lasntg\Admin\Subscriptions;

use Lasntg\Admin\Products\ProductUtils;

class SubscriptionActionsFilters {


	public static function init(): void {
		add_action( 'post_updated', [ self::class, 'post_updated' ], 10, 3 );
		add_action( 'wp_insert_post', [ self::class, 'wp_insert_post' ], 1, 3 );
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
			} else {
				Notifications::course_status_change( $post_ID, $post_after->post_status, $post_before->post_status );
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

	public static function wp_insert_post( $post_id, $post, $update ) {
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
