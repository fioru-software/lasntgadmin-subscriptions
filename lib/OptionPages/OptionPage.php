<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

abstract class OptionPage {

	protected static $options;
	public static $option_name;
	protected static $options_sanitized = false;

	/**
	 * Slug of currently active tab
	 *
	 * @var string
	 */
	protected static $active_tab = '';

	/**
	 * @var string Slug of this tab.
	 */
	protected static $tab_name;

	public static function get_option_name() {
		return static::$option_name;
	}

	public static function load_page_content() {
		?>
		<form method="post" action="options.php">
			<?php
			settings_fields( static::$option_name );
			do_settings_sections( static::$option_name );
			submit_button();
			?>
		</form>
		<?php
	}
	public static function init() {
		static::$active_tab = isset( $_GET['tab'] ) ?
		sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'national_manager';
		add_action( 'admin_menu', [ static::class, 'add_plugin_page' ] );
	}
	abstract public static function page_init();
	abstract public static function section_info();
	abstract public static function sanitize( $input): array;

	protected static function register_setting() {
		register_setting(
			static::$option_name,
			static::$option_name,
			[ static::class, 'sanitize' ]
		);
	}


	/**
	 * Adds plugin settings page to admin
	 */
	public static function add_plugin_page() {
		add_options_page(
			__( 'Lasntg Subscriptions', 'lasntgadmin' ),
			__( 'Lasntg Subscriptions', 'lasntgadmin' ),
			'manage_options',
			'lasntg-subscriptions',
			[ self::class, 'create_admin_page' ]
		);
	}


	/**
	 * Creates header of admin settings page
	 * Expects loadPageContent() to exist in child class
	 */
	public static function create_admin_page() {
		?>
	<div class="wrap">
			<h2><?php echo __( 'Lasntg Subscriptions', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>

			<h2 class="nav-tab-wrapper">
				<a href="?page=lasntg-subscriptions&tab=national_manager" class="nav-tab <?php echo 'national_manager' === static::$active_tab ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Messages', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<a href="?page=lasntg-subscriptions&tab=training_officers" class="nav-tab <?php echo 'training_officers' === static::$active_tab ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Training Officers', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<a href="?page=lasntg-subscriptions&tab=advanced" class="nav-tab <?php echo 'advanced' === static::$active_tab ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Settings', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</h2>

			<?php call_user_func( [ self::class, 'load_page_content' ] ); ?>
		</div>
		<?php
	}
}
