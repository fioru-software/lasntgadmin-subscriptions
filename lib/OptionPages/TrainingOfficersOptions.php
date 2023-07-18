<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

class TrainingOfficersOptions extends OptionPage {

	protected static $option_name_ = 'lasntg_subscriptions_training_officers';
	protected static $tab_name     = 'training_officers';
	protected static $tab_settings = 'training_officer_settings';

	public static function init(): void {
		parent::$tab_name    = static::$tab_name;
		static::$option_name = self::$option_name_;
		parent::init();
	}

	public static function page_init(): void {
		self::$option_name    = self::$option_name_;
		Editors::$option_name = static::$option_name;
		add_settings_section(
			static::$tab_settings,
			'',
			[ static::class, 'section_info' ],
			static::$option_name
		);

		self::set_fields( 2 );
	}
	public static function section_info(): void {
		?>
		<p>
			<?php
			echo __( 'Messages for training officers...', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?>
		</p>
		<?php
		self::show_key();
	}
}
