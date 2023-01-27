<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

class GeneralOptions extends OptionPage {

	public static function init(): void {
		parent::$tab_name = 'general';

		parent::init();
		parent::$option_name = 'lasntg_subscriptions_general';
		if ( is_admin() && static::$active_tab == static::$tab_name ) {
			self::$option_name    = 'lasntg_subscriptions_general';
			Editors::$option_name = 'lasntg_subscriptions_general';
			add_action( 'admin_init', [ static::class, 'page_init' ] );
		}
	}

	public static function page_init(): void {
		self::$option_name = 'lasntg_subscriptions_general';

		add_settings_section(
			'general_settings',
			'',
			[ static::class, 'section_info' ],
			self::$option_name
		);

		$fields = [
			'cancel_waiting_order_subject'    => __( 'Waiting Order Cancelled(Order Creator) Subject', 'lasntgadmin' ),
			'cancel_waiting_order'            => __( 'Waiting Order Cancelled(Order Creator) Body', 'lasntgadmin' ),

			'status_set_to_enrolling_subject' => __( 'Vacant space available Subject', 'lasntgadmin' ),
			'status_set_to_enrolling'         => __( 'Vacant space available', 'lasntgadmin' ),
		];

		foreach ( $fields as $subject => $field ) {
			Editors::add_settings_field(
				$subject,
				$field,
				'general_settings'
			);
		}
	}


	public static function section_info() {
		?>
		<p>
			<?php echo __( 'General Messages...', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
		<?php
		parent::show_key();
	}

	public static function sanitize( $input ): array {
		$editor_fields = [
			'cancel_waiting_order',
			'status_set_to_enrolling',
		];

		$text_fields = [
			'cancel_waiting_order_subject',
			'status_set_to_enrolling_subject',
		];
		return parent::sanitize_fieds( $input, $editor_fields, $text_fields );
	}
}
