<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

class PrivateClientOptions extends OptionPage {
	protected static $option_name_ = 'lasntg_subscriptions_private';
	protected static $tab_name     = 'private';
	protected static $tab_settings = 'private_settings';
	public static function init(): void {
		parent::$tab_name    = static::$tab_name;
		static::$option_name = self::$option_name_;
		parent::init();
	}

	public static function page_init(): void {
		self::$option_name    = 'lasntg_subscriptions_private';
		parent::$option_name  = self::$option_name_;
		Editors::$option_name = static::$option_name;
		add_settings_section(
			'private_settings',
			'',
			[ static::class, 'section_info' ],
			self::$option_name
		);

		$fields = [
			'course_cancellation_subject'     => __( 'Course Cancelled Subject', 'lasntgadmin' ),
			'course_cancellation'             => __( 'Course Cancelled Body', 'lasntgadmin' ),

			'status_set_to_enrolling_subject' => __( 'Vacant space available Subject', 'lasntgadmin' ),
			'status_set_to_enrolling'         => __( 'Vacant space available', 'lasntgadmin' ),
		];

		foreach ( $fields as $subject => $field ) {
			Editors::add_settings_field(
				$subject,
				$field,
				'private_settings'
			);
		}
	}


	public static function section_info() {
		?>
		<p>
			<?php echo __( 'Private Messages...', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
		<?php
		parent::show_key();
	}

	public static function sanitize( $input ): array {
		$editor_fields = [
			'course_cancellation',
			'status_set_to_enrolling',
		];

		$text_fields = [
			'course_cancellation_subject',
			'status_set_to_enrolling_subject',
		];
		return parent::sanitize_fieds( $input, $editor_fields, $text_fields );
	}
}
