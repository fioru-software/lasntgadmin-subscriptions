<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

class OptionPage {



	protected static $options;
	protected static $option_name;
	protected static $tab_settings;
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



	public static function sanitize( $input ): array {
		$editor_fields = [
			'course_open_for_enrollment',
			'course_new',
			'course_cancellation',
			'status_change',
			'course_update',
		];
		$text_fields   = [
			'course_open_for_enrollment_subject',
			'course_new_subject',
			'course_update_subject',
			'status_change_subject',
			'course_cancellation_subject',
		];

		return self::sanitize_fieds( $input, $editor_fields, $text_fields );
	}
	public static function sanitize_fieds( $input, $editor_fields, $text_fields = [] ): array {
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

	public static function load_page_content(): void {            ?>
		<form method="post" action="options.php">
			<?php
			settings_fields( static::$option_name );
			do_settings_sections( static::$option_name );
			submit_button();
			?>
		</form>
		<?php
	}
	public static function init(): void {
		static::$active_tab = isset( $_GET['tab'] ) ?
			sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'national_manager';
		if ( ! self::$initiated ) {
			add_action( 'admin_menu', [ static::class, 'add_plugin_page' ] );
			self::$initiated = true;
			self::register_setting();
		}
		if ( is_admin() && static::$active_tab == static::$tab_name ) {
			Editors::$option_name = static::$option_name;
			add_action( 'admin_init', [ static::class, 'page_init' ] );
		}
	}
	public static function section_info(): void {
		?>
		<p>
			<?php
			echo __( 'Messages for training officerstttt...', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?>
		</p>
		<?php
		self::show_key();
	}
	public static function page_init(): void {
		add_settings_section(
			static::$tab_settings,
			'',
			[ static::class, 'section_info' ],
			static::$option_name
		);
	}
	public static function set_fields(): void {
		$fields = [
			'course_open_for_enrollment_subject'          => __( 'Course Open For Enrollment Subject', 'lasntgadmin' ),
			'course_open_for_enrollment'                  => __( 'Course Open For Enrollment', 'lasntgadmin' ),
			
			'course_new_subject'          => __( 'New Course Subject', 'lasntgadmin' ),
			'course_new'                  => __( 'New Course Body', 'lasntgadmin' ),

			'course_update_subject'       => __( 'Course Update Subject', 'lasntgadmin' ),
			'course_update'               => __( 'Course Update Body', 'lasntgadmin' ),

			'status_change_subject'       => __( 'Course Status Change Subject', 'lasntgadmin' ),
			'status_change'               => __( 'Course Status Change Body', 'lasntgadmin' ),

			'course_cancellation_subject' => __( 'Course Cancellation Subject', 'lasntgadmin' ),
			'course_cancellation'         => __( 'Course Cancellation Body', 'lasntgadmin' ),

		];
		foreach ( $fields as $subject => $field ) {
			Editors::add_settings_field(
				$subject,
				$field,
				static::$tab_settings
			);
		}
	}
	protected static function register_setting(): void {
		$settings = [
			'lasntg_subscriptions_private'           => PrivateClientOptions::class,
			'lasntg_subscriptions_options'           => NationalManagerOptions::class,
			'lasntg_subscriptions_training_officers' => TrainingOfficersOptions::class,
			'lasntg_subscriptions_regional_options'  => RegionalManagerOptions::class,
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
	public static function add_plugin_page(): void {
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
	public static function create_admin_page(): void {
		?>
		<div class="wrap">
			<h2>
				<?php
				echo __( 'Lasntg Subscriptions', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				?>
			</h2>

			<h2 class="nav-tab-wrapper">
				
				<a href="?page=lasntg-subscriptions&tab=national_manager" class="nav-tab <?php echo 'national_manager' === static::$active_tab ? 'nav-tab-active' : ''; ?>">
					<?php
					echo __( 'National Manager', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					?>
				</a>
				<a href="?page=lasntg-subscriptions&tab=regional_manager" class="nav-tab <?php echo 'regional_manager' === static::$active_tab ? 'nav-tab-active' : ''; ?>">
					<?php
					echo __( 'Regional Manager', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					?>
				</a>
				<a href="?page=lasntg-subscriptions&tab=training_officers" class="nav-tab <?php echo 'training_officers' === static::$active_tab ? 'nav-tab-active' : ''; ?>">
					<?php
					echo __( 'Training Officers', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					?>
				</a>
				<a href="?page=lasntg-subscriptions&tab=private" class="nav-tab <?php echo 'private' === static::$active_tab ? 'nav-tab-active' : ''; ?>">
					<?php
					echo __( 'Private Client', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
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

	/**
	 * Show Table Key.
	 *
	 * @return void
	 */
	public static function show_key(): void {
		?>

		<div class="" style="display: flex">
			<div>
				<p><strong>Course Details</strong></p>

				<p>
					Course Code: {%code%}<br />
					Course Name: {%name%}<br />
					Course Cost: {%cost%}<br />
					Course Description: {%description%}<br />
					Course Link: {%link%}<br />
					Course Capacity: {%capacity%}<br />
					Course Status: {%status%}<br />
					Course Event Type: {%event_type%}<br />
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
