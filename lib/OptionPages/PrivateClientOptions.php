<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

class PrivateClientOptions extends OptionPage {
	protected static $option_name_ = 'lasntg_subscriptions_private';
	protected static $tab_name     = 'private';
	protected static $tab_settings = 'private_settings';

	/**
	 * Init.
	 *
	 * @return void
	 */
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

		self::set_fields();
	}
	public static function section_info(): void {
		?>
		<p>
			<?php
			echo __( 'Private Messages...', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?>
		</p>
		<?php
		self::show_key();
	}
}
