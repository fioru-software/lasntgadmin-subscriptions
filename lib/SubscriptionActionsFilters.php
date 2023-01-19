<?php

namespace Lasntg\Admin\Subscriptions;

class SubscriptionActionsFilters {

	public static function init(): void {
		add_action( 'post_updated', [ self::class, 'wp_insert_post' ], 10, 3 );
		add_action( 'wp_insert_post', [ self::class, 'new_course' ], 1, 3 );
	}


	public static function wp_insert_post( $post_ID, $post_after, $post_before ) {
		if ( 'product' !== $post_after->post_type ) {
			return;
		}
		if ( $post_after->post_status !== $post_before->post_status ) {
			if ( 'cancelled' === $post_after->post_status ) {
				Notifications::course_cancelled( $post_ID );
			}
		}

		if ( 'cancelled' !== $post_after->post_status ) {
			Notifications::notify_managers_course_updated( $post_ID );
		}
	}

	public static function new_course( $post_id, $post, $update ) {
		if (
			'product' === $post->post_type
			&& 'open_for_enrollment' === $post->post_status
			&& empty( get_post_meta( $post_id, 'check_if_run_once' ) )
		) {
			Notifications::notify_managers_new_course( $post );
			// And update the meta so it won't run again.
			update_post_meta( $post_id, 'check_if_run_once', true );
		}
	}
}
