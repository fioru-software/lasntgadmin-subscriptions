<?php

namespace Lasntg\Admin\Subscriptions\SubscriptionPages;

class PrivateClientPage {

	/**
	 * Init.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', [ self::class, 'add_page' ] );
		add_filter( 'woocommerce_account_menu_items', [ self::class, 'add_new_item_tab' ] );
		add_filter( 'query_vars', [ self::class, 'new_item_query_vars' ] );
		add_action( 'woocommerce_account_lasntg-subsriptions_endpoint', [ self::class, 'add_new_item_content' ] );
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue Scripts.
	 *
	 * @return void
	 */
	public static function enqueue_scripts(): void {
		global $wp;
		if ( strpos( $wp->request, 'lasntg-subsriptions' ) === false ) {
			return;
		}
		wp_enqueue_script( 'lasntgadmin-attendees' );
		$assets_dir = self::get_assets_dir();
		wp_enqueue_script( 'lasntgadmin-attendees-js', ( $assets_dir . 'js/lasntgadmin-subscription-list.js' ), array( 'jquery' ), '1.4', true );
		wp_enqueue_style( 'lasntgadmin-attendees-css', ( $assets_dir . 'css/lasntgadmin-list.css' ) );
		SubscriptionManager::process_post();
	}

	/**
	 * Get Assets dir.
	 *
	 * @return string
	 */
	private static function get_assets_dir(): string {
		return untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/../../assets/';
	}

	/**
	 * Add Page.
	 *
	 * @return void
	 */
	public static function add_page(): void {
		add_rewrite_endpoint( 'lasntg-subsriptions', EP_ROOT | EP_PAGES );
	}

	/**
	 * New Item Query Vars.
	 *
	 * @param  array $vars array vars.
	 * @return array
	 */
	public static function new_item_query_vars( $vars ): array {
		$vars[] = 'lasntg-subsriptions';
		return $vars;
	}

	/**
	 * Add New Item Tab.
	 *
	 * @param  mixed $items Tabs.
	 * @return array
	 */
	public static function add_new_item_tab( $items ): array {
		$items['lasntg-subsriptions'] = 'Subcriptions';
		return $items;
	}

	/**
	 * Add New Item Content.
	 *
	 * @return void
	 */
	public static function add_new_item_content(): void {
		SubscriptionManager::process_post();

		?>
		<h3>Subscriptions</h3>
		<?php
		SubscriptionManager::show_form();
	}
}
