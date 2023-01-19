<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

use Lasntg\Admin\Subscriptions\Editors;

class NationalManagerOptions extends OptionPage {

	use Editors;
	public static function init() {
		parent::$tab_name = 'national_manager';
		parent::init();
		if ( is_admin() && static::$active_tab == static::$tab_name ) {
			parent::$option_name  = 'lasntg_subscriptions_options';
			Editors::$option_name = 'lasntg_subscriptions_options';
			add_action( 'admin_menu', [ static::class, 'add_plugin_page' ] );
			add_action( 'admin_init', [ static::class, 'page_init' ] );
		}
	}
	public static function page_init() {
		parent::register_setting();
		add_settings_section(
			'message_settings',
			'',
			[ static::class, 'section_info' ],
			self::$option_name
		);

		add_settings_field(
			'course_update_subject',
			__( 'Course Update Subject', 'lasntgadmin' ),
			[ self::class, 'course_update_subject' ],
			self::$option_name,
			'message_settings'
		);
		add_settings_field(
			'course_update',
			__( 'Course update To National Manager Body', 'lasntgadmin' ),
			[ self::class, 'course_update' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'status_change_subject',
			__( 'Course Status Change Subject', 'lasntgadmin' ),
			[ self::class, 'status_change_subject' ],
			self::$option_name,
			'message_settings'
		);
		add_settings_field(
			'status_change',
			__( 'Status Change To National Manager Body', 'lasntgadmin' ),
			[ self::class, 'status_change' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'course_cancellation_subject',
			__( 'Course Cancellation Subject', 'lasntgadmin' ),
			[ self::class, 'course_cancellation_subject' ],
			self::$option_name,
			'message_settings'
		);
		add_settings_field(
			'course_cancellation',
			__( 'Course Cancellation Body', 'lasntgadmin' ),
			[ self::class, 'course_cancellation' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'course_creation_subject',
			__( 'Course Creation Subject', 'lasntgadmin' ),
			[ self::class, 'course_creation_subject' ],
			self::$option_name,
			'message_settings'
		);
		add_settings_field(
			'course_creation',
			__( 'Course Creation To National Manager Body', 'lasntgadmin' ),
			[ self::class, 'course_creation' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'cancel_waiting_order_subject',
			__( 'Waiting Order Cancelled(Order Creator) Subject', 'lasntgadmin' ),
			[ self::class, 'cancel_waiting_order_subject' ],
			self::$option_name,
			'message_settings'
		);
		add_settings_field(
			'cancel_waiting_order',
			__( 'Waiting Order Cancelled(Order Creator) Body', 'lasntgadmin' ),
			[ self::class, 'cancel_waiting_order' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'training_centre_confirms_order_subject',
			__( 'Order Approved Subject', 'lasntgadmin' ),
			[ self::class, 'training_centre_confirms_order_subject' ],
			self::$option_name,
			'message_settings'
		);
		add_settings_field(
			'training_centre_confirms_order',
			__( 'Order Approved Body', 'lasntgadmin' ),
			[ self::class, 'training_centre_confirms_order' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'order_cancellation_subject',
			__( 'Order Cancelled Subject', 'lasntgadmin' ),
			[ self::class, 'order_cancellation_subject' ],
			self::$option_name,
			'message_settings'
		);
		add_settings_field(
			'order_cancellation',
			__( 'Order Cancelled Body', 'lasntgadmin' ),
			[ self::class, 'order_cancellation' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'status_set_to_enrolling_subject',
			__( 'Vacant space available Subject', 'lasntgadmin' ),
			[ self::class, 'status_set_to_enrolling_subject' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'status_set_to_enrolling',
			__( 'Vacant space available', 'lasntgadmin' ),
			[ self::class, 'status_set_to_enrolling' ],
			self::$option_name,
			'message_settings'
		);
	}

	/**
	 * Prints tab section info.
	 */
	public static function section_info() {
		?>
		<p>
			<?php echo __( 'Messages can have info from courses and orders.', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>

		<div class="" style="display: flex">
			<div>
				<p><strong>Course Details</strong></p>

				<p>
					Course Code: {%code%}<br />
					Course Name: {%name%}<br />
					Course Cost: {%cost%}<br />
					Course Capacity: {%capacity%}<br />
					Course Status: {%status%}<br />
					Course Order: {%order%}<br />
					Course Award: {%award%}<br />
					Course Awarding Body: {%awarding_body%}<br />
					Course Start Date: {%start_date%}<br />
					Course Start Time: {%start_time%}<br />
					Course End Date: {%end_date%}<br />
					Course End Time: {%end_time%}<br />
					Course Duration: {%duration%}<br />
					Course Location: {%location%}<br />
					Course Trainer Name: {%trainer_name%}<br />
					Course Trainer Email: {%trainer_email%}<br />
					Course Training Provider: {%training_provider%}<br />
					Course Training Aim: {%training_aim%}<br />
					Course Primary Target Grade: {%primary_target_grade%}<br />
					Course Other Grades Applicable: {%other_grades_applicable%}<br />
					Course Expiry Period: {%expiry_period%}<br />
					Course Link To More Information: {%link_to_more_information%}<br />
					Course Order: {%course_order%}<br />
					Course Applicable Regulation: {%applicable_regulation%}<br />
				</p>
			</div>
			
			<div style="margin-left: 12px">
				<p><strong>Receiver info <small>National, Regional or Training officer</small></strong></p>
				<p>
					Name: {%to_name%}<br />
					Email: {%to_user_email%}<br />
					Department: {%to_user_department%}<br />
					Phone: {%to_user_phone%}<br />
				</p>
			</div>
		</div>


		<?php
	}

	/**
	 * Sanitizes settings form input.
	 *
	 * @param array $input input to sanitize.
	 *
	 * @return array
	 */
	public static function sanitize( $input ): array {
		// Fix for issue that options are sanitized twice when no db entry exists
		// "It seems the data is passed through the sanitize function twice.[...]
		// This should only happen when the option is not yet in the wp_options table."
		// @see https://codex.wordpress.org/Function_Reference/register_setting#Notes .
		if ( self::$options_sanitized ) {
			return $input;
		}
		self::$options_sanitized = true;

		$sanitary_values = array();
		$editor_fields   = [
			'course_cancellation',
			'course_creation',
			'cancel_waiting_order',
			'status_change',
			'training_centre_confirms_order',
			'order_cancellation',
			'status_set_to_enrolling',
			'course_update',
		];
		foreach ( $editor_fields as $text_field ) {
			if ( isset( $input[ $text_field ] ) ) {
				$sanitary_values[ $text_field ] = wp_kses_post( $input[ $text_field ] );
			}
		}
		$text_fields = [
			'course_update_subject',
			'status_change_subject',
			'course_cancellation_subject',
			'course_creation_subject',
			'cancel_waiting_order_subject',
			'training_centre_confirms_order_subject',
			'order_cancellation_subject',
			'status_set_to_enrolling_subject',
		];
		foreach ( $text_fields as $text_field ) {
			if ( isset( $input[ $text_field ] ) ) {
				$sanitary_values[ $text_field ] = sanitize_text_field( $input[ $text_field ] );
			}
		}

		return $sanitary_values;
	}
}
