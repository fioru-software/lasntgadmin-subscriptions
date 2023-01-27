<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

class Editors {
	public static $option_name;
	public static $options;

	public static function add_text_field( $name ) {
		$name = esc_attr( $name );
		printf(
			'<input class="regular-text" type="text" name="%s[' . esc_attr( $name ) . ']" id="' . esc_attr( $name ) . '" value="%s">',
			self::$option_name, //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			self::get_options( $name ) //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	public static function add_settings_field( $id, $label, $section = 'message_settings' ) {
		add_settings_field(
			$id,
			$label,
			[ self::class, $id ],
			self::$option_name,
			$section
		);
	}

	public static function course_new_subject() {
		self::add_text_field( 'course_new_subject' );
	}

	public static function training_course_cancelled() {
		self::add_text_field( 'training_course_cancelled' );
	}

	public static function status_change_subject() {
		self::add_text_field( 'status_change_subject' );
	}
	public static function course_update_subject() {
		self::add_text_field( 'course_update_subject' );
	}
	public static function course_cancellation_subject() {
		self::add_text_field( 'course_cancellation_subject' );
	}
	public static function course_creation_subject() {
		self::add_text_field( 'course_creation_subject' );
	}
	public static function cancel_waiting_order_subject() {
		self::add_text_field( 'cancel_waiting_order_subject' );
	}
	public static function training_centre_confirms_order_subject() {
		self::add_text_field( 'training_centre_confirms_order_subject' );
	}
	public static function order_cancellation_subject() {
		self::add_text_field( 'order_cancellation_subject' );
	}
	public static function status_set_to_enrolling_subject() {
		self::add_text_field( 'status_set_to_enrolling_subject' );
	}

	public static function course_update() {
		self::wp_editor( 'course_update' );
	}
	public static function course_cancellation() {
		self::wp_editor( 'course_cancellation' );
	}

	public static function status_set_to_enrolling() {
		self::wp_editor( 'status_set_to_enrolling' );
	}

	public static function order_cancellation() {
		self::wp_editor( 'order_cancellation' );
	}

	public static function course_creation() {
		self::wp_editor( 'course_creation' );
	}

	public static function cancel_waiting_order() {
		self::wp_editor( 'cancel_waiting_order' );
	}

	public static function training_centre_confirms_order() {
		self::wp_editor( 'training_centre_confirms_order' );
	}

	public static function status_change() {
		self::wp_editor( 'status_change' );
	}

	public static function course_new() {
		self::wp_editor( 'course_new' );
	}

	public static function wp_editor( $name, $textarea_rows = 20 ) {
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

	public static function get_options( $name ) {
		self::$options = get_option( self::$option_name );
		return self::$options && isset( self::$options[ $name ] ) ? self::$options[ $name ] : '';
	}
}
