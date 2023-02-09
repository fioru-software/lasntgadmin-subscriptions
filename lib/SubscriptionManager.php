<?php
namespace Lasntg\Admin\Subscriptions;

class SubscriptionManager {
	public static $name = 'lasntg_subscription_';
	public static function add_to_mailing_list( $user_id, $value, $type = 'category' ) {
		if ( ! self::user_in_mailing_list( $user_id, $value, $type ) ) {
			add_user_meta( $user_id, self::$name . $type, $value );
		}
	}
	public static function add_remove_user( $user_id, $value, $type = 'category' ) {
		if ( self::user_in_mailing_list( $user_id, $value, $type ) ) {
			self::remove_from_mailing_list( $user_id, $value, $type );
			return 1;
		} else {
			self::add_to_mailing_list( $user_id, $value, $type );
			return 2;
		}
	}
	public static function remove_from_mailing_list( $user_id, $value, $type = 'category' ) {
		delete_user_meta( $user_id, self::$name . $type, $value );
	}
	public static function remove_all_from_mailing_list( $user_id, $type = 'category' ) {
		delete_user_meta( $user_id, self::$name . $type );
	}
	public static function user_in_mailing_list( $user_id, $value, $type = 'category' ) {
		$metas = get_user_meta( $user_id, self::$name . $type );
		return in_array( $value, $metas );
	}

	public static function get_all_inputs( $user_id, $type = 'category' ) {
		return get_user_meta( $user_id, self::$name . $type );
	}

	public static function confirm_meta( $user_id, $value, $type = 'category' ) {
		global $wpdb;
		return $wpdb->get_col(
			$wpdb->prepare( "SELECT umeta_id FROM $wpdb->usermeta WHERE user_id = %s and meta_value = %s and meta_key = %s", [ $user_id, $value, self::$name . $type ] )
		);
	}
}
