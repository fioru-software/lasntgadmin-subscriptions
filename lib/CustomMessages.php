<?php

namespace Lasntg\Admin\Subscriptions;

class CustomMessages {

	public static function init(): void {
		add_action( 'admin_enqueue_scripts', [ self::class, 'add_cancel_custom_message' ], 99 );
		add_action( 'wp_ajax_lasntgadmin_custom_cancel_message', [ self::class, 'save_message' ] );
		add_action( 'wp_ajax_nopriv_lasntgadmin_custom_cancel_message', [ self::class, 'save_message' ] );
	}

	public static function modal(): void {
		global $post;
		$post_type = property_exists( get_current_screen(), 'post_type' ) ? get_current_screen()->post_type : false;
		if ( ! $post || 'product' !== $post_type ) {
			return;
		}
		?>
		<div id="my-content-id" style="display:none; width:100%; height: 100%">
			<form id="course_cancellation_form">
				<h3>
					Please enter custom cancellation Subject and Message.
				</h3>
				<p>Please note all field variables are available and can be used in here.</p>

				<label for="custom_cancellation_subject">Subject</label>
				<input class="regular-text" type="text" name="custom_cancellation_subject" id="custom_cancellation_subject" value="<?php echo esc_attr( wp_unslash( get_post_meta( $post->ID, '_cancellation_subject', true ) ) ); ?>">

				<textarea name="my-wp-editor" id="my-wp-editor" rows="12" class="myprefix-wpeditor"><?php echo wp_kses_post( get_post_meta( $post->ID, '_cancellation_message', true ) ); ?></textarea>
				<button class="button button-primary button-large">Save</button>
			</form>
		</div>
		<?php
	}
	public static function add_cancel_custom_message(): void {
		global $post;
		$post_type = property_exists( get_current_screen(), 'post_type' ) ? get_current_screen()->post_type : false;
		if ( 'product' !== $post_type ) {
			return;
		}

		add_thickbox();
		wp_enqueue_editor();
		self::modal();
		$assets_dir = untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/../assets/';
		wp_enqueue_script( 'lasntgadmin-custom-messages-admin-js', ( $assets_dir . 'js/lasntgadmin-custom-messages.js' ), array( 'jquery' ), '1.6', true );
		$nonce = wp_create_nonce( 'lasntgadmin-custom-cancel-message-nonce' );
		wp_localize_script(
			'lasntgadmin-custom-messages-admin-js',
			'lasntgadmin_custom_messages_admin_localize',
			array(
				'adminurl'  => admin_url() . 'admin-ajax.php',
				'id'        => $post ? $post->ID : false,
				'post_type' => $post_type,
				'nonce'     => $nonce,
			)
		);
		?>


		<?php
	}

	public static function save_message(): void {
		check_ajax_referer( 'lasntgadmin-custom-cancel-message-nonce', 'security' );
		if (
			! isset( $_POST['product_id'] )
			|| ! isset( $_POST['subject'] )
			|| ! isset( $_POST['message'] )

		) {
			wp_send_json(
				[
					'status' => 0,
					'msg'    => __( 'No product id', 'lasntgadmin' ),
				]
			);
			wp_die();
		}
		$product_id = sanitize_text_field( wp_unslash( $_POST['product_id'] ) );
		$subject    = sanitize_text_field( wp_unslash( $_POST['subject'] ) );
		$email      = wp_kses_post( wp_unslash( $_POST['message'] ) );

		$post = get_post( $product_id );
		if ( ! $post || 'product' !== $post->post_type ) {
			wp_send_json(
				[
					'status' => 0,
					'msg'    => __( 'Product does not exist', 'lasntgadmin' ),
				]
			);
			wp_die();
		}
		update_post_meta( $product_id, '_cancellation_subject', $subject );
		update_post_meta( $product_id, '_cancellation_message', $email );
		wp_send_json(
			[
				'status' => 1,
			]
		);
		wp_die();
	}
}
