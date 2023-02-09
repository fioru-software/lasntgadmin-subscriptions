<?php

namespace Lasntg\Admin\Subscriptions\SubscriptionPages;

use Lasntg\Admin\Subscriptions\SubscriptionManager;

class TrainingOfficerOptionPage {

	public static function init() {
		add_action( 'admin_menu', [ static::class, 'add_plugin_page' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'admin_enqueue_scripts' ] );
	}
	public static function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( $screen && 'settings_page_lasntg-subscriptions-list' === $screen->base ) {
			$assets_dir = untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/../../assets/';

			wp_enqueue_script( 'lasntgadmin-subscription-subscribe-list-js', ( $assets_dir . 'js/lasntgadmin-subscription-list.js' ), array( 'jquery' ), '1.0', true );

			$nonce = wp_create_nonce( 'lasntgadmin-subscription-subscribe-list-nonce' );
			wp_localize_script(
				'lasntgadmin-subscription-subscribe-list-js',
				'lasntgadmin_subscription_list_localize',
				array(
					'adminurl'        => admin_url() . 'admin-ajax.php',
					'subscribe_nonce' => $nonce,
				)
			);
		}
	}

	/**
	 * Adds plugin settings page to admin
	 */
	public static function add_plugin_page() {
		add_options_page(
			__( 'Lasntg Subscription Options', 'lasntgadmin' ),
			__( 'Lasntg Subscriptions Options', 'lasntgadmin' ),
			'lasntg_list_options',
			'lasntg-subscriptions-list',
			[ self::class, 'create_admin_page' ]
		);
	}

	public static function process_post() {
		if (
			! isset( $_POST['child_cat'] ) &&
			! isset( $_POST['location'] ) &&
			! isset( $_POST['course_type'] )
		) {
			return;
		}
		wp_verify_nonce( 'lasntgadmin-subscription-list-nonce' );
		$current_user_id = get_current_user_id();
		SubscriptionManager::remove_all_from_mailing_list( $current_user_id );
		$cats         = wp_unslash( $_POST['child_cat'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$locations    = wp_unslash( $_POST['location'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$course_types = wp_unslash( $_POST['course_type'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		foreach ( $cats as $cat ) {
			$cat = sanitize_text_field( $cat );
			SubscriptionManager::add_to_mailing_list( $current_user_id, (int) $cat );
		}
		SubscriptionManager::remove_all_from_mailing_list( $current_user_id, 'location' );
		foreach ( $locations as $location ) {
			$location = sanitize_text_field( $location );
			SubscriptionManager::add_to_mailing_list( $current_user_id, $location, 'location' );
		}

		SubscriptionManager::remove_all_from_mailing_list( $current_user_id, 'course_type' );
		foreach ( $course_types as $course_type ) {
			$course_type = sanitize_text_field( $course_type );
			SubscriptionManager::add_to_mailing_list( $current_user_id, $course_type, 'course_type' );
		}
		wp_redirect( admin_url( 'options-general.php?page=lasntg-subscriptions-list&updated=true' ) );
	}
	/**
	 * Creates header of admin settings page
	 * Expects loadPageContent() to exist in child class
	 */
	public static function create_admin_page() {
		$current_user_id = get_current_user_id();
		$user_cats       = SubscriptionManager::get_all_inputs( $current_user_id );
		$course_types    = SubscriptionManager::get_all_inputs( $current_user_id, 'course_type' );
		$locations       = SubscriptionManager::get_all_inputs( $current_user_id, 'location' );
		self::process_post();
		?>
		<style>
			.cat-list>li {
				font-weight: bold;
			}

			.cat-list ul { 

			
				font-weight: normal;
				text-indent: 15px;
			}

			.lists {
				display: flex;
			}

			.lists>.div-list {
				margin-left: 30px;
			}
		</style>
		<div class="wrap">
			<h2>
				<?php
				echo __( 'Lasntg Subscriptions List', 'lasntgadmin' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				?>
			</h2>
			<form class="" method="post" action="">
			<?php
			$nonce = wp_create_nonce( 'lasntgadmin-subscription-list-nonce' );
			?>
			<input type="hidden" name="lasntgadmin-subscription-list-nonce" value="<?php echo esc_attr( $nonce ); ?>"/>
				<div class="lists">
					<div class="div-list">
						<h3>Categories</h3>
						<ul>
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
										<li><label><input type='checkbox' class='child_cat' name='child_cat[]' data-parent='<?php echo esc_attr( $category->term_id ); ?>' value='<?php echo esc_attr( $cat->term_id ); ?>'  <?php echo ( in_array( $cat->term_id, $user_cats ) ? 'checked' : '' ); ?>/><?php echo esc_attr( $cat->name ); ?></label></li>
									<?php
								}
								echo '</ul>';
							}
							echo '</ul>';
							?>
						</ul>

					</div>
					<?php
					$field = get_field_object( 'field_63881b84798a5' );
					if ( $field['choices'] ) :
						?>
						<div class="div-list">
							<h3>Location</h3>
							<ul>
								<li><label><input type="checkbox" class='select_all_list' />All</label></li>
								<?php foreach ( $field['choices'] as $value => $label ) : ?>
									<li><label><input type="checkbox" name="location[]" value="<?php echo esc_attr( $value ); ?>" <?php echo in_array( $value, $locations ) ? 'checked' : 'no'; ?> /><?php echo esc_attr( $label ); ?></label></li>
								<?php endforeach; ?>
							</ul>

						</div>
					<?php endif; ?>
					<?php
					$field = get_field_object( 'field_6387864196776' );
					if ( $field['choices'] ) :
						?>
						<div class="div-list">
							<h3>Course Type</h3>
							<ul>
								<li><label><input type="checkbox" class='select_all_list' />All</label></li>
								<?php foreach ( $field['choices'] as $value => $label ) : ?>
									<li><label><input type="checkbox" name="course_type[]" value="<?php echo esc_attr( $value ); ?>" <?php echo in_array( $value, $course_types ) ? 'checked' : 'no'; ?> /><?php echo esc_attr( $label ); ?></label></li>
								<?php endforeach; ?>
							</ul>

						</div>
					<?php endif; ?>

				</div>
				<input type="submit" value="Save" />
			</form>
		</div>
		<?php
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
}
