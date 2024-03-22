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
		$subject = get_post_meta( $post_ID, '_cancellation_subject', true );
		$body    = get_post_meta( $post_ID, '_cancellation_message', true );

		$email = NotificationUtils::parse_info( $post_ID, $subject, $body );
		if ( $subject && $body ) {
			ManagersNotifications::custom_canellation_with_message( $post_ID, $email['subject'], $email['body'] );
			RegionalManagerNotifications::custom_canellation_with_message( $post_ID, $email['subject'], $email['body'] );
			TrainingCenterNotifications::custom_canellation_with_message( $post_ID, $email['subject'], $email['body'] );
			PrivateNotifications::custom_canellation_with_message( $post_ID, $email['subject'], $email['body'] );
		} else {
			ManagersNotifications::course_cancelled( $post_ID );
			RegionalManagerNotifications::course_cancelled( $post_ID );
			TrainingCenterNotifications::course_cancelled( $post_ID );
			PrivateNotifications::course_cancelled( $post_ID );
		}
	}

	public static function course_updated( $post_ID ): void {
		ManagersNotifications::course_updated( $post_ID );
		RegionalManagerNotifications::course_updated( $post_ID );
		TrainingCenterNotifications::course_updated( $post_ID );
		PrivateNotifications::course_updated( $post_ID );
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
		ManagersNotifications::new_course( $post_ID );
		TrainingCenterNotifications::new_course( $post_ID );
		RegionalManagerNotifications::new_course( $post_ID );
		PrivateNotifications::new_course( $post_ID );
	}

	public static function new_enrollment( $order_id ): void {
		$order = wc_get_order( $order_id );

		$user_id = $order->user_id;
		$items   = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		if ( ! $items ) {
			return;
		}

		$user       = get_user_by( 'ID', $user_id );
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
Course Location: {%location%}</p>
<p data-renderer-start-pos="406">Please log in to the LASNTG Dashboard to see details.</p>
<p data-renderer-start-pos="461">Kind Regards,
LASNTG</p>
<p data-renderer-start-pos="483">Grúpa Oiliúna Náisiúnta um Sheirbhísí Údaráis Áitiúil |Aonad 4/5, Cúirt an Bhráthar|An tAonach |Tiobraid Árann</p>
<p data-renderer-start-pos="595">Local Authority Services National Training Group| Unit 4/5, Friar’s Court | Nenagh | County Tipperary</p>
<p data-renderer-start-pos="698">T: &lt;a href="tel:+353526166260"&gt;+353 52 616 6260&lt;/a&gt; | E: &lt;a <a class="css-tgpl01" title="mailto:href=%22lasntg@tipperarycoco.ie" href="mailto:href=%22lasntg@tipperarycoco.ie" data-testid="link-with-safety" data-renderer-mark="true">href="lasntg@tipperarycoco.ie</a>"&gt;<a class="css-tgpl01" title="mailto:lasntg@tipperarycoco.ie" href="mailto:lasntg@tipperarycoco.ie" data-testid="link-with-safety" data-renderer-mark="true">lasntg@tipperarycoco.ie</a>&lt;/a&gt; | &lt;a href="<span data-inline-card="true" data-card-url="http://www.lasntg.ie"><span class="loader-wrapper"><span aria-expanded="false" aria-haspopup="true" data-testid="hover-card-trigger-wrapper"><a class="css-118vsk3" tabindex="0" role="button" href="http://www.lasntg.ie/" data-testid="inline-card-resolved-view"><span class="css-14tyax2" data-testid="inline-card-icon-and-title"><span class="smart-link-title-wrapper css-0">LASNTG</span></span></a></span></span></span> "&gt;<span data-inline-card="true" data-card-url="http://www.lasntg.ie"><span class="loader-wrapper"><span aria-expanded="false" aria-haspopup="true" data-testid="hover-card-trigger-wrapper"><a class="css-118vsk3" tabindex="0" role="button" href="http://www.lasntg.ie/" data-testid="inline-card-resolved-view"><span class="css-14tyax2" data-testid="inline-card-icon-and-title"><span class="smart-link-title-wrapper css-0">LASNTG</span></span></a></span></span></span> &lt;/a&gt;
&lt;a href="<span data-inline-card="true" data-card-url="http://www.lasntg.ie"><span class="loader-wrapper"><span aria-expanded="false" aria-haspopup="true" data-testid="hover-card-trigger-wrapper"><a class="css-118vsk3" tabindex="0" role="button" href="http://www.lasntg.ie/" data-testid="inline-card-resolved-view"><span class="css-14tyax2" data-testid="inline-card-icon-and-title"><span class="smart-link-title-wrapper css-0">LASNTG</span></span></a></span></span></span> "&gt;&lt;img src="<a class="css-tgpl01" title="https://lasntgadmin-staging.veri.ie/wp-content/uploads/2023/10/image-20231031-085516-e1698745134776.png" href="https://lasntgadmin-staging.veri.ie/wp-content/uploads/2023/10/image-20231031-085516-e1698745134776.png" data-testid="link-with-safety" data-renderer-mark="true">https://lasntgadmin-staging.veri.ie/wp-content/uploads/2023/10/image-20231031-085516-e1698745134776.png</a>" alt="lasntg" /&gt;&lt;/a&gt;</p>';
		$info    = NotificationUtils::parse_info( $product_id, $subject, $body );

		NotificationUtils::parse_emails_for_users( [ $user_id ], $info['subject'], $info['body'], $product_id );
	}
}
