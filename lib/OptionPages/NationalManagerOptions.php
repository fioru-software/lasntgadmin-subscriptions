<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

class NationalManagerOptions extends OptionPage {

	protected static $option_name_ = 'lasntg_subscriptions_options';
	protected static $tab_name     = 'national_manager';
	protected static $tab_settings = 'message_settings';

	public static function init() {
		parent::$tab_name    = static::$tab_name;
		static::$option_name = self::$option_name_;
		parent::init();
	}

	public static function page_init(): void {
		parent::$option_name  = self::$option_name_;
		Editors::$option_name = static::$option_name;
		add_settings_section(
			'message_settings',
			'',
			[ static::class, 'section_info' ],
			self::$option_name
		);

		self::set_fields();
	}
	public static function section_info() {
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
