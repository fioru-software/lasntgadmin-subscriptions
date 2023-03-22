<?php

namespace Lasntg\Admin\Subscriptions\Notifications;

use Lasntg\Admin\Products\ProductUtils;
use Groups_User_Group;
use Lasntg\Admin\Group\GroupUtils;

class ParseEmail
{
	/**
	 * Add course info.
	 *
	 * @param  int    $post_ID Post ID.
	 * @param  string $message Message to replace with placeholders.
	 * @return string
	 */
	public static function add_course_info($post_ID, $message)
	{
		$product       = new \WC_Product($post_ID);

		$acf_fields = $_POST['acf'];
		
		$post = $_POST;
		$course_fields = [
			'code'                     => $post['_sku'],
			'name'                     => $post['post_title'],
			'cost'                     => $post['_regular_price'],
			'capacity'                 => $post['_stock'],
			'description'              => $product->get_description(),
			'link'                     => $product->get_permalink(),
			'status'                   => ProductUtils::get_status_name($product->get_status()),
			'event_type'               => $acf_fields['field_6387864196776'],
			'awarding_body'            => $acf_fields['field_638786be96777'],
			'start_date'               => $acf_fields['field_63881aee31478'],
			'start_time'               => $acf_fields['field_63881b0531479'],
			'end_date'                 => $acf_fields['field_63881b1e3147a'],
			'end_time'                 => $acf_fields['field_63881b2c3147b'],
			'duration'                 => $acf_fields['field_63881b63798a4'],
			'location'                 => $acf_fields['field_63881b84798a5'],
			'training_centre'          => $acf_fields['field_63881beb798a7'],
			'training_group'           => $acf_fields['field_63881c1ff4453'],
			'trainer_name'             => $acf_fields['field_63881cc2f4455'],
			'trainer_email'            => $acf_fields['field_63881ce6f4456'],
			'training_provider'        => $acf_fields['field_63881cf7f4457'],
			'training_aim'             => $acf_fields['field_6387890fd6a25'],
			'award'                    => $acf_fields['field_63881d74f445a'],
			'applicable_regulation'    => $acf_fields['field_63878939d6a27'],
			'primary_target_grade'     => $acf_fields['field_63881f7f3e5af'],
			'other_grades_applicable'  => $acf_fields['field_638820173e5b0'],
			'expiry_period'            => $acf_fields['field_63882047beae3'],
			'link_to_more_information' => $acf_fields['field_6388216175740'],
			'course_order'             => $acf_fields['field_6388218175741'],

		];
		return self::replace($message, $course_fields);
	}

	/**
	 * Replace placeholder with value.
	 *
	 * @param  string $message Message.
	 * @param  array  $fields Fields.
	 * @return string
	 */
	private static function replace($message, array $fields)
	{
		foreach ($fields as $name => $value) {
			$message = str_replace("{%$name%}", $value, $message);
			$message = str_replace("{% $name %}", $value, $message);
			$message = str_replace("{%$name %}", $value, $message);
			$message = str_replace("{% $name%}", $value, $message);
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
	public static function add_receiver_info($user, $message, $post_ID)
	{
		$customer = new \WC_Customer($user->ID);

		$fields = [
			'to_user_email'      => $user->user_email,
			'to_user_name'       => $user->display_name,
			'to_user_department' => get_field('field_63908cd5d9835', 'user_' . $user->ID, true),
			'to_user_phone'      => $customer->get_billing_phone(),
			'course_quotas'      => self::add_quotas($post_ID, $user),
		];
		return self::replace($message, $fields);
	}

	public static function add_quotas($post_ID, $user)
	{
		// removed  NotificationUtils::get_post_group_ids since it got old data rather than new
		
		$groups = GroupUtils::get_all_groups(
			[
				'include' => $_POST['groups-read'],
			]
		);
		$quotas = [];
		foreach ($groups as $group) {
			$group_id      = $group->group_id;
			$quota         = NotificationUtils::get_group_quotas($post_ID, $group_id);
			$administrator = in_array('administrator', $user->roles);

			if ($administrator) {
				$quotas[] = " <strong>{$group->name}:</strong> $quota ";
				continue;
			}
			$is_a_member = Groups_User_Group::read($user->ID, $group_id);
			if ($is_a_member) {
				$quotas[] = " <strong>{$group->name}:</strong> $quota ";
			}
		}
		return join(', ', $quotas);
	}
}
