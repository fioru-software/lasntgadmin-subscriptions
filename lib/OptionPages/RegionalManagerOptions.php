<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

class RegionalManagerOptions extends OptionPage {

	protected static $option_name_ = 'lasntg_subscriptions_regional_options';
	protected static $tab_name     = 'regional_manager';
	protected static $tab_settings = 'regional_settings';

	public static function init(): void {
		parent::$tab_name    = static::$tab_name;
		static::$option_name = self::$option_name_;
		parent::init();
	}

	public static function page_init(): void {
		parent::$option_name  = self::$option_name_;
		Editors::$option_name = static::$option_name;
		self::$tab_settings   = 'message_settings';
		add_settings_section(
			self::$tab_settings,
			'',
			[ static::class, 'section_info' ],
			self::$option_name
		);

		self::set_fields();
	}

	public static function section_info(): void {
		?>
		<p>
			<?php
			echo __( 'Messages for National managers emails...', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?>
		</p>
		<?php
		self::show_key();
	}
}
