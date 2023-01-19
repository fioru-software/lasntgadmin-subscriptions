<?php

namespace Lasntg\Admin\Subscriptions;

class OptionsPage {

	use Editors;

	protected static $options;
	protected static $option_name     = 'lasntg_subscriptions_options';
	private static $options_sanitized = false;

	/**
	 * Slug of currently active tab
	 *
	 * @var string
	 */
	protected static $active_tab;


	/**
	 * @var string Slug of this tab
	 */
	private static $tab_name = 'national_manager';

	public static function init() {
		self::$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : self::$active_tab;

		if ( is_admin() && self::$active_tab == self::$tab_name ) {
			add_action( 'admin_menu', [ self::class, 'add_plugin_page' ] );
		}
		add_action( 'admin_init', [ self::class, 'page_init' ] );
	}
	/**
	 * Prints page content
	 */
	public static function loadPageContent() {
		?>
		<form method="post" action="options.php">
			<?php
			settings_fields( self::$option_name );
			do_settings_sections( self::$option_name );
			submit_button();
			?>
		</form>
		<?php
	}
	/**
	 * Loads settings fields
	 */
	public static function page_init() {
		register_setting(
			self::$option_name,
			// option_group
			self::$option_name,
			// option_name
			[ self::class, 'sanitize' ]
			// sanitize_callback
		);

		add_settings_section(
			'message_settings',
			// id
			'',
			// title
			[ self::class, 'section_info' ],
			// callback
			self::$option_name
			// page
		);

		add_settings_section(
			'training_officers',
			// id
			'',
			// title
			[ self::class, 'training_officer_messages' ],
			// callback
			self::$option_name
			// page
		);

		add_settings_field(
			'course_update_subject',
			// id
			__( 'Course Update Subject', 'lasntgadmin' ),
			// title
			[ self::class, 'course_update_subject' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);
		add_settings_field(
			'course_update',
			// id
			__( 'Course update To National Manager Body', 'lasntgadmin' ),
			// title
			[ self::class, 'course_update' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);

		add_settings_field(
			'status_change_subject',
			// id
			__( 'Course Status Change Subject', 'lasntgadmin' ),
			// title
			[ self::class, 'status_change_subject' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);
		add_settings_field(
			'status_change',
			// id
			__( 'Status Change To National Manager Body', 'lasntgadmin' ),
			// title
			[ self::class, 'status_change' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);

		add_settings_field(
			'course_cancellation_subject',
			// id
			__( 'Course Cancellation Subject', 'lasntgadmin' ),
			// title
			[ self::class, 'course_cancellation_subject' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);
		add_settings_field(
			'course_cancellation',
			// id
			__( 'Course Cancellation Body', 'lasntgadmin' ),
			// title
			[ self::class, 'course_cancellation' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);

		add_settings_field(
			'course_creation_subject',
			// id
			__( 'Course Creation Subject', 'lasntgadmin' ),
			// title
			[ self::class, 'course_creation_subject' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);
		add_settings_field(
			'course_creation',
			// id
			__( 'Course Creation To National Manager Body', 'lasntgadmin' ),
			// title
			[ self::class, 'course_creation' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);

		add_settings_field(
			'cancel_waiting_order_subject',
			// id
			__( 'Waiting Order Cancelled(Order Creator) Subject', 'lasntgadmin' ),
			// title
			[ self::class, 'cancel_waiting_order_subject' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);
		add_settings_field(
			'cancel_waiting_order',
			// id
			__( 'Waiting Order Cancelled(Order Creator) Body', 'lasntgadmin' ),
			// title
			[ self::class, 'cancel_waiting_order' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);

		add_settings_field(
			'training_centre_confirms_order_subject',
			// id
			__( 'Order Approved Subject', 'lasntgadmin' ),
			// title
			[ self::class, 'training_centre_confirms_order_subject' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);
		add_settings_field(
			'training_centre_confirms_order',
			// id
			__( 'Order Approved Body', 'lasntgadmin' ),
			// title
			[ self::class, 'training_centre_confirms_order' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);

		add_settings_field(
			'order_cancellation_subject',
			// id
			__( 'Order Cancelled Subject', 'lasntgadmin' ),
			// title
			[ self::class, 'order_cancellation_subject' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);
		add_settings_field(
			'order_cancellation',
			// id
			__( 'Order Cancelled Body', 'lasntgadmin' ),
			// title
			[ self::class, 'order_cancellation' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);

		add_settings_field(
			'status_set_to_enrolling_subject',
			// id
			__( 'Vacant space available Subject', 'lasntgadmin' ),
			// title
			[ self::class, 'status_set_to_enrolling_subject' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);

		add_settings_field(
			'status_set_to_enrolling',
			// id
			__( 'Vacant space available', 'lasntgadmin' ),
			// title
			[ self::class, 'status_set_to_enrolling' ],
			// callback
			self::$option_name,
			// page
			'message_settings'
			// section
		);
	}


	/**
	 * Prints tab section info
	 */
	public static function section_info() {
		?>
		<p>
			<?php echo __( 'Messages can have info from courses and orders.', 'lasntgadmin' ); ?>
		</p>

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
				<p><strong>Order Details. <small>Only applies for emails with orders ie order Cancellation</small>.</strong></p>
				<p>
					First Name: {%first_name%}<br />
					Last Name: {%last_name%}<br />
					Phone Name: {%phone%}<br />
					Address 1: {%address_one%}<br />
					Address 2: {%address_two%}<br />
					City: {%city%}<br />
					Eircode: {%eircode%}<br />
					Country: {%country%}<br />
					County: {%county%}<br />
					Order Status: {%order_status%}<br />
				</p>
			</div>
			<div style="margin-left: 12px">
				<p><strong>Receiver info <small>National, Regional or Training officer</small></strong></p>
				<p>
					Name: {%to_name%}<br />
					Email: {%to_user_email%}<br />
					Department: {%to_user_department%}<br />
					Phone: {%to_user_phone%}<br />
				</p>
			</div>
		</div>


		<?php
	}


	/**
	 * Sanitizes settings form input
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public static function sanitize( $input ) {
		// Fix for issue that options are sanitized twice when no db entry exists
		// "It seems the data is passed through the sanitize function twice.[...]
		// This should only happen when the option is not yet in the wp_options table."
		// @see https://codex.wordpress.org/Function_Reference/register_setting#Notes
		if ( self::$options_sanitized ) {
			return $input;
		}
		self::$options_sanitized = true;

		$sanitary_values = array();
		$editor_fields   = [
			'course_cancellation',
			'course_creation',
			'cancel_waiting_order',
			'status_change',
			'training_centre_confirms_order',
			'order_cancellation',
			'status_set_to_enrolling',
			'course_update',
		];
		foreach ( $editor_fields as $text_field ) {
			if ( isset( $input[ $text_field ] ) ) {
				$sanitary_values[ $text_field ] = wp_kses_post( $input[ $text_field ] );
			}
		}
		$text_fields = [
			'course_update_subject',
			'status_change_subject',
			'course_cancellation_subject',
			'course_creation_subject',
			'cancel_waiting_order_subject',
			'training_centre_confirms_order_subject',
			'order_cancellation_subject',
			'status_set_to_enrolling_subject',
		];
		foreach ( $text_fields as $text_field ) {
			if ( isset( $input[ $text_field ] ) ) {
				$sanitary_values[ $text_field ] = sanitize_text_field( $input[ $text_field ] );
			}
		}

		return $sanitary_values;
	}

	/**
	 * Adds plugin settings page to admin
	 */
	public static function add_plugin_page() {
		add_options_page(
			__( 'Lasntg Subscriptions', 'lasngtadmin' ),
			// page_title
			__( 'Lasntg Subscriptions', 'lasngtadmin' ),
			// menu_title
			'manage_options',
			// capability
			'lasntg-subscriptions',
			// menu_slug
			[ self::class, 'create_admin_page' ]
			// callback function
		);
	}

	/**
	 * Creates header of admin settings page
	 * Expects loadPageContent() to exist in child class
	 */
	public static function create_admin_page() {
		?>
	<div class="wrap">
			<h2><?php echo __( 'Lasntg Subscriptions', 'lasngtadmin' ); ?></h2>

			<h2 class="nav-tab-wrapper">
				<a href="?page=lasngtadmin-subscriptions&tab=national_manager" class="nav-tab <?php echo self::$active_tab == 'national_manager' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Messages', 'lasngtadmin' ); ?></a>
				<a href="?page=lasngtadmin-subscriptions&tab=training_officers" class="nav-tab <?php echo self::$active_tab == 'training_officers' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Training Officers', 'lasngtadmin' ); ?></a>
				<a href="?page=lasngtadmin-subscriptions&tab=advanced" class="nav-tab <?php echo self::$active_tab == 'advanced' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Settings', 'lasngtadmin' ); ?></a>
			</h2>

			<?php call_user_func( [ self::class, 'loadPageContent' ] ); ?>
		</div>
		<?php
	}
}
