<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

use Lasntg\Admin\Subscriptions\Editors;

class TrainingOfficersOptions extends OptionPage {

	use Editors;
	public static function init(): void {
		parent::$tab_name = 'training_officers';
		parent::init();
		if ( is_admin() && static::$active_tab == static::$tab_name ) {
			parent::$option_name = 'lasntg_subscriptions_training_officers';

			add_action( 'admin_init', [ static::class, 'page_init' ] );
		}
		add_action( 'admin_menu', [ static::class, 'add_plugin_page' ] );
	}
	public static function load_page_content(): void {
	}
	public static function page_init(): void {
		parent::register_setting();

		add_settings_field(
			'course_cancelled',
			__( 'Course Cancelled Subject', 'lasntgadmin' ),
			[ self::class, 'course_cancelled_subject' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'course_cancelled',
			__( 'Course Cancelled', 'lasntgadmin' ),
			[ self::class, 'course_cancelled' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'course_cancelled',
			__( 'Course Cancelled Subject', 'lasntgadmin' ),
			[ self::class, 'course_cancelled_subject' ],
			self::$option_name,
			'message_settings'
		);

		add_settings_field(
			'course_cancelled',
			__( 'Course Cancelled', 'lasntgadmin' ),
			[ self::class, 'training_course_cancelled' ],
			self::$option_name,
			'message_settings'
		);
	}


	public static function section_info() {

		?>
		<p>
			<?php echo __( 'Messages for training officers...', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
		<?php
	}

	public static function sanitize( $input ): array {
		return [];
	}
}
