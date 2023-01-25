<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

abstract class OptionPage {


	protected static $options;
	public static $option_name;
	protected static $options_sanitized = false;
	protected static $initiated         = false;

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

	abstract public static function page_init();
	abstract public static function section_info();
	abstract public static function sanitize( $input): array;
	public static function sanitize_fieds( $input, $editor_fields, $text_fields = [] ) {
		// Fix for issue that options are sanitized twice when no db entry exists
		// "It seems the data is passed through the sanitize function twice.[...]
		// This should only happen when the option is not yet in the wp_options table."
		// @see https://codex.wordpress.org/Function_Reference/register_setting#Notes .
		if ( static::$options_sanitized ) {
			return $input;
		}
		static::$options_sanitized = true;
		$sanitary_values           = array();
		foreach ( $editor_fields as $text_field ) {
			if ( isset( $input[ $text_field ] ) ) {
				$sanitary_values[ $text_field ] = wp_kses_post( $input[ $text_field ] );
			}
		}
		foreach ( $text_fields as $text_field ) {
			if ( isset( $input[ $text_field ] ) ) {
				$sanitary_values[ $text_field ] = sanitize_text_field( $input[ $text_field ] );
			}
		}
		return $sanitary_values;
	}
	public static function get_option_name() {
		return static::$option_name;
	}

	public static function load_page_content() {        ?>
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
		if ( ! self::$initiated ) {
			add_action( 'admin_menu', [ static::class, 'add_plugin_page' ] );
			self::$initiated = true;
			self::register_setting();
		}
	}

	protected static function register_setting() {
		$settings = [
			'lasntg_subscriptions_general' => GeneralOptions::class,
			'lasntg_subscriptions_options' => NationalManagerOptions::class,
		];
		foreach ( $settings as $setting => $class ) {
			register_setting(
				$setting,
				$setting,
				[ $class, 'sanitize' ]
			);
		}
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
			<h2>
			<?php
			echo __( 'Lasntg Subscriptions', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?>
				</h2>

			<h2 class="nav-tab-wrapper">
				<a href="?page=lasntg-subscriptions&tab=general" class="nav-tab <?php echo 'general' === static::$active_tab ? 'nav-tab-active' : ''; ?>">
																							<?php
																							echo __( 'General', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																							?>
																																							</a>
				<a href="?page=lasntg-subscriptions&tab=national_manager" class="nav-tab <?php echo 'national_manager' === static::$active_tab ? 'nav-tab-active' : ''; ?>">
																									<?php
																									echo __( 'National Manager', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																									?>
																																											</a>
				<a href="?page=lasntg-subscriptions&tab=training_officers" class="nav-tab <?php echo 'training_officers' === static::$active_tab ? 'nav-tab-active' : ''; ?>">
																									<?php
																										echo __( 'Training Officers', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																									?>
																																												</a>
				<a href="?page=lasntg-subscriptions&tab=advanced" class="nav-tab <?php echo 'advanced' === static::$active_tab ? 'nav-tab-active' : ''; ?>">
																							<?php
																							echo __( 'Settings', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																							?>
																																							</a>
			</h2>

			<?php call_user_func( [ self::class, 'load_page_content' ] ); ?>
		</div>
		<?php
	}

	public static function show_key() {
		?>

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
					Name: {%to_user_name%}<br />
					Email: {%to_user_email%}<br />
					Department: {%to_user_department%} <small>(If National Managaer)</small><br />
					Phone: {%to_user_phone%}<br />
				</p>
			</div>
		</div>
		<?php
	}
}
