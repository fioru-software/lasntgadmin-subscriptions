<?php

namespace Lasntg\Admin\Subscriptions\SubscriptionPages;

class RegionalManagerOptionPage {


	public static function init(): void {
		add_action( 'admin_menu', [ static::class, 'add_plugin_page' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'admin_enqueue_scripts' ] );
	}
	public static function admin_enqueue_scripts(): void {
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
	public static function add_plugin_page(): void {
		add_options_page(
			__( 'Lasntg Subscription Options', 'lasntgadmin' ),
			__( 'Lasntg Subscriptions Options', 'lasntgadmin' ),
			'lasntg_list_options',
			'lasntg-subscriptions-list',
			[ self::class, 'create_admin_page' ]
		);
	}

	/**
	 * Creates header of admin settings page
	 * Expects loadPageContent() to exist in child class
	 */
	public static function create_admin_page(): void {
		SubscriptionManager::process_post();
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
			<?php SubscriptionManager::show_form(); ?>
		</div>
		<?php
	}
}
