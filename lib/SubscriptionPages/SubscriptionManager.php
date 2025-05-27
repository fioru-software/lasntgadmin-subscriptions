<?php

namespace Lasntg\Admin\Subscriptions\SubscriptionPages;

use Lasntg\Admin\Subscriptions\Notifications\NotificationUtils;

class SubscriptionManager {


	public static $name = 'lasntg_subscription_';
	public static function add_to_mailing_list( $user_id, $value, $type = 'category' ): void {
		if ( ! self::user_in_mailing_list( $user_id, $value, $type ) ) {
			add_user_meta( $user_id, self::$name . $type, $value );
		}
	}

	public static function add_remove_user( $user_id, $value, $type = 'category' ): int {
		if ( self::user_in_mailing_list( $user_id, $value, $type ) ) {
			self::remove_from_mailing_list( $user_id, $value, $type );
			return 1;
		}
		self::add_to_mailing_list( $user_id, $value, $type );
		return 2;
	}
	public static function remove_from_mailing_list( $user_id, $value, $type = 'category' ): void {
		delete_user_meta( $user_id, self::$name . $type, $value );
	}
	public static function remove_all_from_mailing_list( $user_id, $type = 'category' ): void {
		delete_user_meta( $user_id, self::$name . $type );
	}
	public static function user_in_mailing_list( $user_id, $value, $type = 'category' ): bool {
		$metas = get_user_meta( $user_id, self::$name . $type );
		return in_array( $value, $metas );
	}

	public static function get_all_inputs( $user_id, $type = 'category' ) {
		return get_user_meta( $user_id, self::$name . $type );
	}

	public static function confirm_meta( $user_id, $value, $type = 'category' ) {
		global $wpdb;
		$meta_key = self::$name . $type;
		// if the $value is array.
		if ( is_array( $value ) && ! empty( $value ) ) {
			$sql = "SELECT umeta_id
				FROM $wpdb->usermeta
				WHERE user_id = %d
				AND meta_key = %s
				AND meta_value IN (" . implode( ',', array_fill( 0, count( $value ), '%s' ) ) . ')';

			$params = array_merge( [ $user_id, $meta_key ], $value );

			return $wpdb->get_col(
				$wpdb->prepare( $sql, ...$params ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			);
		} else {
			// fallback to single value.
			return $wpdb->get_col(
				$wpdb->prepare(
					"SELECT umeta_id FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s AND meta_value = %s",
					$user_id,
					$meta_key,
					$value
				)
			);
		}//end if
	}

	public static function show_form(): void {
		global $wp;
		$request = $wp->request ? $wp->request : 'admin';

		?>
		<form class="" method="post" action="">
			<?php
			$nonce = wp_create_nonce( 'lasntgadmin-subscription-list-nonce' );
			?>
			<input type="hidden" name="lasntgadmin-subscription-list-nonce" value="<?php echo esc_attr( $nonce ); ?>" />
			<input type="hidden" name="lasntgadmin-url" value="<?php echo esc_attr( $request ); ?>" />
			<div class="lists">

				<?php
				// Show Categories and courses.
				self::show_categories();
				self::show_courses();
				?>

			</div>
			<input type="submit" value="Save" />
		</form>
		<?php
	}

	public static function show_categories(): void {
		$user_cats = self::get_all_inputs( get_current_user_id() );
		?>
		<div class="div-list">
			<h3>Categories</h3>
			<?php

			$args = array(
				'show_option_all'    => '',
				'container'          => false,
				'orderby'            => 'name',
				'order'              => 'ASC',
				'hide_empty'         => 0,
				'use_desc_for_title' => 0,
				'child_of'           => 0,
				'hierarchical'       => 1,
				'number'             => null,
				'echo'               => 1,
				'depth'              => -1,
				'taxonomy'           => 'product_cat',
				'parent'             => 0,

			);
			$parent_categories = get_categories( $args );
			echo "<ul class='cat-list'>";
			foreach ( $parent_categories as $category ) {
				?>
				<li><label><input type='checkbox' class='parent_cat' name='parent_cat[]' data-id='<?php echo esc_attr( $category->term_id ); ?>' /><?php echo esc_attr( $category->name ); ?></label></li>
				<?php
				$categories = self::get_category_hierachy( $category->term_id, 'product_cat', true );
				?>
				<ul class='cat-child-list' data-id='<?php echo esc_attr( $category->term_id ); ?>'>
					<?php

					foreach ( $categories as $cat ) {
						?>
						<li><label><input type='checkbox' class='child_cat' name='child_cat[]' data-parent='<?php echo esc_attr( $category->term_id ); ?>' value='<?php echo esc_attr( $cat->term_id ); ?>' <?php echo ( in_array( $cat->term_id, $user_cats ) ? 'checked' : '' ); ?> /><?php echo esc_attr( $cat->name ); ?></label></li>
						<?php
					}
					echo '</ul>';
			}
			?>
				</ul>
		</div>
		<?php
	}


	public static function show_courses() {
		$course_types = self::get_all_inputs( get_current_user_id(), 'course_type' );
		$field        = get_field_object( NotificationUtils::$course_acf );
		if ( $field['choices'] ) :
			?>
			<div class="div-list">
				<h3>Event Type</h3>
				<ul  class='cat-list'>
					<li><label><input type="checkbox" class='select_all_list' /><span class="label-text">All</span></label></li>
					<?php foreach ( $field['choices'] as $value => $label ) : ?>
						<li><label><input type="checkbox" name="course_type[]" value="<?php echo esc_attr( $value ); ?>" <?php echo in_array( $value, $course_types ) ? 'checked' : 'no'; ?> /><span class="label-text"><?php echo esc_attr( $label ); ?></span></label></li>
					<?php endforeach; ?>
				</ul>

			</div>
			<?php
endif;
	}
	public static function get_category_hierachy( $cat_id = 0, $taxonomy = 'product_cat' ) {
		$args = array(
			'hide_empty'   => 0,
			'hierarchical' => 1,
			'taxonomy'     => $taxonomy,
			'parent'       => $cat_id,
		);

		return get_categories( $args );
	}

	public static function process_post(): void {
		if ( ! isset( $_POST['lasntgadmin-subscription-list-nonce'] ) ) {
			return;
		}
		wp_verify_nonce( 'lasntgadmin-subscription-list-nonce' );
		$request = isset( $_POST['lasntgadmin-url'] ) ? sanitize_text_field( wp_unslash( $_POST['lasntgadmin-url'] ) ) : false;
		// clear all just in case user decides not to have any selection.
		$current_user_id = get_current_user_id();
		self::remove_all_from_mailing_list( $current_user_id );
		self::remove_all_from_mailing_list( $current_user_id, 'location' );
		self::remove_all_from_mailing_list( $current_user_id, 'course_type' );
		if (
			! isset( $_POST['child_cat'] ) &&
			! isset( $_POST['course_type'] )
		) {
			self::redirect_back( $request );
			return;
		}

		$cats         = wp_unslash( $_POST['child_cat'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$course_types = wp_unslash( $_POST['course_type'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		foreach ( $cats as $cat ) {
			$cat = sanitize_text_field( $cat );
			self::add_to_mailing_list( $current_user_id, (int) $cat );
		}

		foreach ( $course_types as $course_type ) {
			$course_type = sanitize_text_field( $course_type );
			self::add_to_mailing_list( $current_user_id, $course_type, 'course_type' );
		}
		self::redirect_back( $request );
	}

	/**
	 * Redirects back depending on the lasntg-admin-ur
	 *
	 * @param  mixed $request Current page.
	 * @return void
	 */
	protected static function redirect_back( $request ): void {
		if ( $request && strpos( $request, 'lasntg-subsriptions' ) !== false ) {
			wp_redirect( home_url( 'my-account/lasntg-subsriptions/?updated=true' ) );
		} else {
			wp_redirect( admin_url( 'options-general.php?page=lasntg-subscriptions-list&updated=true' ) );
		}
	}
}
