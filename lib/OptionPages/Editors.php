<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

class Editors {
	public static $option_name;
	public static $options;

	/**
	 * Add Text Field.
	 *
	 * @param  string $name Text Field name.
	 * @return void
	 */
	public static function add_text_field( $name ) {
		$name = esc_attr( $name );
		printf(
			'<input class="regular-text" type="text" name="%s[' . esc_attr( $name ) . ']" id="' . esc_attr( $name ) . '" value="%s">',
			self::$option_name, //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			self::get_options( $name ) //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Add Settings Field.
	 *
	 * @param  string $id Text ID.
	 * @param  string $label Text Label.
	 * @param  string $section Field Section.
	 * @return void
	 */
	public static function add_settings_field( $id, $label, $section = 'message_settings' ): void {
		add_settings_field(
			$id,
			$label,
			[ self::class, $id ],
			self::$option_name,
			$section
		);
	}

	/**
	 * Course Open for Enrollment Subject Field.
	 *
	 * @return void
	 */
	public static function course_open_for_enrollment_subject(): void {
		self::add_text_field( 'course_open_for_enrollment_subject' );
	}
	/**
	 * Course New Subject Field.
	 *
	 * @return void
	 */
	public static function course_new_subject(): void {
		self::add_text_field( 'course_new_subject' );
	}

	/**
	 * Training Course Cancelled Text Field.
	 *
	 * @return void
	 */
	public static function training_course_cancelled(): void {
		self::add_text_field( 'training_course_cancelled' );
	}

	/**
	 * Status Change Subject Field.
	 *
	 * @return void
	 */
	public static function status_change_subject(): void {
		self::add_text_field( 'status_change_subject' );
	}
	/**
	 * Course Update Subject Field.
	 *
	 * @return void
	 */
	public static function course_update_subject(): void {
		self::add_text_field( 'course_update_subject' );
	}
	/**
	 * Course Cancellation Subject.
	 *
	 * @return void
	 */
	public static function course_cancellation_subject(): void {
		self::add_text_field( 'course_cancellation_subject' );
	}
	/**
	 * Course Creation Subject Field.
	 *
	 * @return void
	 */
	public static function course_creation_subject(): void {
		self::add_text_field( 'course_creation_subject' );
	}
	/**
	 * Cancel Waiting Order Subject Field.
	 *
	 * @return void
	 */
	public static function cancel_waiting_order_subject(): void {
		self::add_text_field( 'cancel_waiting_order_subject' );
	}
	/**
	 * Training Centre Confirms Order Subject Text  Field.
	 *
	 * @return void
	 */
	public static function training_centre_confirms_order_subject(): void {
		self::add_text_field( 'training_centre_confirms_order_subject' );
	}
	/**
	 * Order Cancellation Subject.
	 *
	 * @return void
	 */
	public static function order_cancellation_subject(): void {
		self::add_text_field( 'order_cancellation_subject' );
	}
	/**
	 * Status Set To Enrolling Subject.
	 *
	 * @return void
	 */
	public static function status_set_to_enrolling_subject(): void {
		self::add_text_field( 'status_set_to_enrolling_subject' );
	}

	public static function course_space_available_subject(): void {
		self::add_text_field( 'course_space_available_subject' );
	}

	public static function course_space_available_free_subject(): void {
		self::add_text_field( 'course_space_available_free_subject' );
	}

	/**
	 * Course Update WP_Editor.
	 *
	 * @return void
	 */
	public static function course_update(): void {
		self::wp_editor( 'course_update' );
	}
	/**
	 * Course Cancellation WP_Editor.
	 *
	 * @return void
	 */
	public static function course_cancellation(): void {
		self::wp_editor( 'course_cancellation' );
	}

	/**
	 * Status Set To Enrolling WP_Editor.
	 *
	 * @return void
	 */
	public static function status_set_to_enrolling(): void {
		self::wp_editor( 'status_set_to_enrolling' );
	}

	/**
	 * Order Cancellation WP_Editor.
	 *
	 * @return void
	 */
	public static function order_cancellation(): void {
		self::wp_editor( 'order_cancellation' );
	}

	/**
	 * Course Creation WP_Editor.
	 *
	 * @return void
	 */
	public static function course_creation(): void {
		self::wp_editor( 'course_creation' );
	}
	/**
	 * Course Creation WP_Editor.
	 *
	 * @return void
	 */
	public static function course_open_for_enrollment(): void {
		self::wp_editor( 'course_open_for_enrollment' );
	}

	/**
	 * Cancel Waiting Order WP_Editor.
	 *
	 * @return void
	 */
	public static function cancel_waiting_order(): void {
		self::wp_editor( 'cancel_waiting_order' );
	}

	/**
	 * Training Centre Confirms Order.
	 *
	 * @return void
	 */
	public static function training_centre_confirms_order(): void {
		self::wp_editor( 'training_centre_confirms_order' );
	}

	/**
	 * Status Change.
	 *
	 * @return void
	 */
	public static function status_change(): void {
		self::wp_editor( 'status_change' );
	}

	/**
	 * New Course Editor.
	 *
	 * @return void
	 */
	public static function course_new(): void {
		self::wp_editor( 'course_new' );
	}

	/**
	 * New Course Editor.
	 *
	 * @return void
	 */
	public static function course_space_available(): void {
		self::wp_editor( 'course_space_available' );
	}

	public static function course_space_available_free(): void {
		self::wp_editor( 'course_space_available_free' );
	}

	/**
	 * Create WP_Editor.
	 *
	 * @param  mixed $name Name of the editor.
	 * @param  mixed $textarea_rows Row count.
	 * @return void
	 */
	public static function wp_editor( $name, $textarea_rows = 20 ): void {
		$input_name = self::$option_name . "[$name]";
		$settings   = array(
			'textarea_name' => $input_name,
			'media_buttons' => false,
			'textarea_rows' => $textarea_rows,
		);
		echo wp_editor( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_kses_post( self::get_options( $name ) ),
			$name,
			$settings
		);
	}

	/**
	 * Get Options.
	 *
	 * @param  mixed $name Option Name.
	 * @return mixed
	 */
	public static function get_options( $name ) {
		self::$options = get_option( self::$option_name );

		return self::$options && isset( self::$options[ $name ] ) ? self::$options[ $name ] : '';
	}
}
