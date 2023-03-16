<?php
namespace Lasntg\Admin\Subscriptions\Notifications;

use Lasntg\Admin\Products\ProductUtils;
class ParseEmail {
	/**
	 * Add course info.
	 *
	 * @param  int    $post_ID Post ID.
	 * @param  string $message Message to replace with placeholders.
	 * @return string
	 */
	public static function add_course_info( $post_ID, $message ) {
		$product       = new \WC_Product( $post_ID );
		$course_fields = [
			'code'                     => $product->get_sku(),
			'name'                     => $product->get_title(),
			'cost'                     => $product->get_price(),
			'capacity'                 => $product->get_stock_quantity(),
			'description'              => $product->get_description(),
			'link'                     => $product->get_permalink(),
			'status'                   => ProductUtils::get_status_name( $product->get_status() ),
			'event_type'               => get_field( 'field_6387864196776', $post_ID, true ),
			'awarding_body'            => get_field( 'field_638786be96777', $post_ID, true ),
			'start_date'               => get_field( 'field_63881aee31478', $post_ID, true ),
			'start_time'               => get_field( 'field_63881b0531479', $post_ID, true ),
			'end_date'                 => get_field( 'field_63881b1e3147a', $post_ID, true ),
			'end_time'                 => get_field( 'field_63881b2c3147b', $post_ID, true ),
			'duration'                 => get_field( 'field_63881b63798a4', $post_ID, true ),
			'location'                 => get_field( 'field_63881b84798a5', $post_ID, true ),
			'training_centre'          => get_field( 'field_63881beb798a7', $post_ID, true ),
			'training_group'           => get_field( 'field_63881c1ff4453', $post_ID, true ),
			'trainer_name'             => get_field( 'field_63881cc2f4455', $post_ID, true ),
			'trainer_email'            => get_field( 'field_63881ce6f4456', $post_ID, true ),
			'training_provider'        => get_field( 'field_63881cf7f4457', $post_ID, true ),
			'training_aim'             => get_field( 'field_6387890fd6a25', $post_ID, true ),
			'award'                    => get_field( 'field_63881d74f445a', $post_ID, true ),
			'applicable_regulation'    => get_field( 'field_63878939d6a27', $post_ID, true ),
			'primary_target_grade'     => get_field( 'field_63881f7f3e5af', $post_ID, true ),
			'other_grades_applicable'  => get_field( 'field_638820173e5b0', $post_ID, true ),
			'expiry_period'            => get_field( 'field_63882047beae3', $post_ID, true ),
			'link_to_more_information' => get_field( 'field_6388216175740', $post_ID, true ),
			'course_order'             => get_field( 'field_6388218175741', $post_ID, true ),

		];
		return self::replace( $message, $course_fields );
	}

	public static function set_quoatas() {
		global $wpdb;
	}
	/**
	 * Replace placeholder with value.
	 *
	 * @param  string $message Message.
	 * @param  array  $fields Fields.
	 * @return string
	 */
	private static function replace( $message, array $fields ) {
		foreach ( $fields as $name => $value ) {
			$message = str_replace( "{%$name%}", $value, $message );
			$message = str_replace( "{% $name %}", $value, $message );
			$message = str_replace( "{%$name %}", $value, $message );
			$message = str_replace( "{% $name%}", $value, $message );
		}
		return $message;
	}

	/**
	 * Add Receiver info to the message
	 *
	 * @param  mixed  $user WP_User.
	 * @param  string $message Message.
	 * @return string
	 */
	public static function add_receiver_info( $user, $message, $post_ID ) {
		$customer = new \WC_Customer( $user->ID );

		$fields = [
			'to_user_email'      => $user->user_email,
			'to_user_name'       => $user->display_name,
			'to_user_department' => get_field( 'field_63908cd5d9835', 'user_' . $user->ID, true ),
			'to_user_phone'      => $customer->get_billing_phone(),
			'course_quotas'      => self::add_quotas( $post_ID, $user ),
		];
		return self::replace( $message, $fields );
	}

	public static function add_quotas( $post_ID, $user ) {
		global $wpdb;
		$table          = $wpdb->prefix . 'groups_group';
		$post_group_ids = NotificationUtils::get_post_group_ids( $post_ID );
		$sql            = "SELECT * FROM $table WHERE group_id IN(" . implode( ', ', array_fill( 0, count( $post_group_ids ), '%s' ) ) . ')  ORDER BY name ASC';
		// Call $wpdb->prepare passing the values of the array as separate arguments.
		$query = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $post_group_ids ) );

		$groups = $wpdb->get_results( $query ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$quotas = [];
		foreach ( $groups as $group ) {
			$group_id = $group->group_id;
			$quota    = NotificationUtils::get_group_quotas( $post_ID, $group_id );

			$is_a_member = \Groups_User_Group::read( $user->ID, $group_id );
			if ( $is_a_member ) {
				$quotas [] = " <strong>{$group->name}:</strong> $quota ";
			}
		}
		return join( ', ', $quotas );
	}
}
