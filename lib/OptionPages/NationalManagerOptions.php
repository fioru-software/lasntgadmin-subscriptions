<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

class NationalManagerOptions extends OptionPage {
	public static function init() {
		parent::$tab_name    = 'national_manager';
		parent::$option_name = 'lasntg_subscriptions_options';
		parent::init();

		if ( is_admin() && static::$active_tab == static::$tab_name ) {
			Editors::$option_name = 'lasntg_subscriptions_options';

			add_action( 'admin_init', [ static::class, 'page_init' ] );
		}
	}
	public static function page_init() {
		self::$option_name = 'lasntg_subscriptions_options';

		add_settings_section(
			'message_settings',
			'',
			[ static::class, 'section_info' ],
			self::$option_name
		);

		$fields = [
			'course_new_subject'                     => __( 'New Course Subject', 'lasntgadmin' ),
			'course_new'                             => __( 'New Course', 'lasntgadmin' ),

			'course_update_subject'                  => __( 'Course Update Subject', 'lasntgadmin' ),
			'course_update'                          => __( 'Course Update', 'lasntgadmin' ),

			'status_change_subject'                  => __( 'Course Status Change Subject', 'lasntgadmin' ),
			'status_change'                          => __( 'Status Change To National Manager Body', 'lasntgadmin' ),

			'course_cancellation_subject'            => __( 'Course Cancellation Subject', 'lasntgadmin' ),
			'course_cancellation'                    => __( 'Course Cancellation Body', 'lasntgadmin' ),

			'course_creation_subject'                => __( 'Course Creation Subject', 'lasntgadmin' ),
			'course_creation'                        => __( 'Course Creation To National Manager Body', 'lasntgadmin' ),

			'training_centre_confirms_order_subject' => __( 'Order Approved Subject', 'lasntgadmin' ),
			'training_centre_confirms_order'         => __( 'Order Approved Body', 'lasntgadmin' ),

		];
		foreach ( $fields as $subject => $field ) {
			Editors::add_settings_field(
				$subject,
				$field
			);
		}
	}

	/**
	 * Prints tab section info.
	 */
	public static function section_info() {
		?>
		<p>
			<?php echo __( 'Messages can have info from courses and orders.', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
		<?php
		parent::show_key();
	}

	/**
	 * Sanitizes settings form input.
	 *
	 * @param array $input input to sanitize.
	 *
	 * @return array
	 */
	public static function sanitize( $input ): array {
		$editor_fields = [
			'course_new',
			'course_cancellation',
			'course_creation',
			'status_change',
			'training_centre_confirms_order',
			'order_cancellation',
			'course_update',
		];
		$text_fields   = [
			'course_new_subject',
			'course_update_subject',
			'status_change_subject',
			'course_cancellation_subject',
			'course_creation_subject',
			'training_centre_confirms_order_subject',
			'order_cancellation_subject',
			'status_set_to_enrolling_subject',
		];

		return parent::sanitize_fieds( $input, $editor_fields, $text_fields );
	}
}
