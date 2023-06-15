<?php

namespace Lasntg\Admin\Subscriptions;

class WP_Emails {

	public static function init(): void {
		self::filters();
	}

	public static function filters(): void {
		add_filter( 'wp_new_user_notification_email', [ self::class, 'new_user_notification_email' ], 10, 3 );
		add_filter(
			'wp_mail',
			function ( $params ) {
				$params['headers'] = 'Content-type: text/html';
				return $params;
			}
		);
	}

	public static function new_user_notification_email( $wp_new_user_notification_email, $user, $blogname ) {
		$key = get_password_reset_key( $user );

		$message  = sprintf( __( '<h3>Hi %1$s %2$s,</h3>', 'lasntgadmin' ), $user->first_name, $user->last_name ) . "\r\n\r\n<br/>"; // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		$message .= "Your account has been created on <strong>LASNTG Online Booking System.</strong>.\r\n\r\n<br/>";
		$message .= sprintf( "Username - <strong>%s</strong> \r\n\r\n<br/>", $user->user_login );
		$message .= sprintf( "Password - To set your password, please <a href=\"%s\"><strong>Click Here</strong></a> \r\n\r\n<br/>", network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) );
		$message .= "<br/>\r\n\r\nFor assistance please contact help@veri.ie <br/>\r\n\r\n";

		$wp_new_user_notification_email['subject'] = __( 'Your account has been created on LASNTG OBS.', 'lasntgadmin' );
		$wp_new_user_notification_email['message'] = $message;

		return $wp_new_user_notification_email;
	}
}
