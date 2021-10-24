<?php

if ( ! class_exists( 'Recommended_Products' ) ) {

	class Recommended_Products {
		protected static $instance;
		private $shortcode = 'recommended_products';
		private $block_settings;

		// Singleton pattern
		public static function set_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {

			// Check if WooCommerce is enabled
			if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

				// Add shortcode for products' block
				add_shortcode( $this->shortcode, array( $this, 'recommended_products_shortcode' ) );

				// Add products' block via woocommerce hook
				add_action( 'woocommerce_after_cart', array( $this, 'recommended_products_wc_hook' ) );

				// Plugin settings data
				$this->block_settings = get_option( 'prefix_settings', false );
			}
		}

		/**
		 *
		 * Render Products block on 'recommended-block' shortcode
		 *
		 * @param $atts
		 * @param $content
		 * @param $tag
		 *
		 * @return string
		 */
		public function recommended_products_shortcode( $atts, $content, $tag ) {
			$current_cart_products = $this->get_current_cart_products_ids();
			$products              = $this->get_products_ids( $current_cart_products );
			$title                 = ( $this->block_settings && isset( $this->block_settings['block_title'] ) ) ? $this->block_settings['block_title'] : '';

			if ( ! $products || ! is_array( $products ) || empty( $products ) ) {
				return '';
			}

			ob_start();
			?>

			<div class="woocommerce-recommended-products recommended">
				<div class="recommended__wrapper">
					<?php if ( $title ) : ?>
						<h3 class="recommended__title">
							<?php echo esc_html( $title ); ?>
						</h3>
					<?php endif; ?>
					<?php
					// We are using default WooCommerce shortcode for displaying products list by their IDs, because it's the convenient and optimal approach
					echo do_shortcode( '[products ids="' . implode( ',', $products ) . '"]' );
					?>
				</div>
			</div>

			<?php
			return wp_kses_post( ob_get_clean() );
		}

		/**
		 *
		 * Render Products block under the main cart page content
		 *
		 * @return string|void
		 */
		public function recommended_products_wc_hook() {
			$current_cart_products = $this->get_current_cart_products_ids();
			$products              = $this->get_products_ids( $current_cart_products );
			$title                 = ( $this->block_settings && isset( $this->block_settings['block_title'] ) ) ? $this->block_settings['block_title'] : '';

			if ( ! $products || ! is_array( $products ) || empty( $products ) ) {
				return '';
			}

			ob_start();
			?>

			<div class="woocommerce-recommended-products recommended">
				<div class="recommended__wrapper">
					<?php if ( $title ) : ?>
						<h3 class="recommended__title">
							<?php echo esc_html( $title ); ?>
						</h3>
					<?php endif; ?>
					<?php
					// We are using default WooCommerce shortcode for displaying products list by their IDs, because it's the convenient and optimal approach
					echo do_shortcode( '[products ids="' . implode( ',', $products ) . '"]' );
					?>
				</div>
			</div>

			<?php
			echo wp_kses_post( ob_get_clean() );
		}

		/**
		 *
		 * Return 5 associated products from all orders history. If no 5 such products have been found, we add the most popular product
		 *
		 * @param array $current_products_ids
		 *
		 * @return array
		 */
		function get_products_ids( array $current_products_ids ):array {
			if ( ! $current_products_ids || empty( $current_products_ids ) ) {
				return $this->get_popular_products( $current_products_ids );
			}

			$associated_products_ids = array();
			$popular_products_ids    = array();
			$result                  = array();

			// Try to get 5 associated products first
			$associated_products_ids = $this->get_associated_products_ids( $current_products_ids );

			// If there are enough associated products return first 5 of them to the render function
			if ( count( $associated_products_ids ) >= 5 ) {
				return $associated_products_ids;
			}

			// If there are not enough associated products found, lets add the most popular products to the list
			$popular_products_ids = $this->get_popular_products( array_merge( $associated_products_ids, $current_products_ids ), 5 - count( $associated_products_ids ) );

			// Merging two products arrays in order to show 5 products
			return array_merge( $associated_products_ids, $popular_products_ids );
		}

		/**
		 *
		 * Get current cart products IDs
		 *
		 * @return array
		 */
		public function get_current_cart_products_ids(): array {
			global $woocommerce;

			$products     = $woocommerce->cart->get_cart();
			$products_ids = array();

			if ( ! $products || ! is_array( $products ) || empty( $products ) ) {
				return $products_ids;
			}

			foreach( $products as $product ) {
				$products_ids[] = $product['data']->get_id();
			}

			return $products_ids;
		}

		/**
		 *
		 * Return array with products' ids, which have been in the same order with the listed products
		 *
		 * @param array $products_ids
		 *
		 * @return array
		 */
		public function get_associated_products_ids( array $products_ids ): array {
			if ( ! $products_ids || ! is_array( $products_ids ) || empty( $products_ids ) ) {
				return array();
			}

			global $wpdb;

			// SQL Query. Select All order which include products from $products_ids
			$orders_data = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT a.order_item_id, a.meta_value as product_id, b.order_id FROM `".$wpdb->prefix."woocommerce_order_itemmeta` a
								JOIN `".$wpdb->prefix."woocommerce_order_items` b
								ON a.order_item_id = b.order_item_id
								WHERE a.meta_key = '_product_id'
								AND a.meta_value IN (".implode( ',', $products_ids ).")"
					),
				'ARRAY_A'
			);
			if ( !$orders_data || ! is_array( $orders_data ) || empty( $orders_data ) ) {
				return array();
			}

			// Remove duplicates
			$orders_ids = array_unique(
				array_map(
					function( $order ) {
						return $order['order_id'];
					},
					$orders_data
				)
			);

			// SQL Query. Select all OTHER (different from products of $products_ids list) products from all the orders of the $orders_ids array
			$orders_other_products = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DISTINCT a.meta_value as product_id FROM `".$wpdb->prefix."woocommerce_order_itemmeta` a
								JOIN `".$wpdb->prefix."woocommerce_order_items` b
								ON a.order_item_id = b.order_item_id
								WHERE a.meta_key = '_product_id'
								AND b.order_id IN (" . implode( ',', $orders_ids ) . ")
								AND a.meta_value NOT IN (".implode( ',', $products_ids ).")
								LIMIT 5"
				),
				'ARRAY_A'
			);
			if ( !$orders_other_products || ! is_array( $orders_other_products ) || empty( $orders_other_products ) ) {
				return array();
			}

			return array_map(
				function( $product ) {
					return $product['product_id'];
				},
				$orders_other_products
			);
		}

		/**
		 *
		 * Returns N most popular products excluding some products
		 *
		 * @param $excluded_products
		 * @param int $number
		 *
		 * @return array
		 */
		public function get_popular_products(  $excluded_products, $number = 5 ): array {
			global $wpdb;

			// SQL Query. Select limited number of the most popular (the highest value of total_sales meta) products
			$popular_products = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT a.meta_value, a.post_id FROM $wpdb->postmeta a 
    				JOIN wp_posts b ON a.post_id = b.ID 
					WHERE a.meta_key = 'total_sales' 
				  	AND b.post_type = 'product' 
					AND a.post_id NOT IN (" . implode( ',', $excluded_products ) . ")
					ORDER BY a.meta_value DESC
					LIMIT %d",
					$number
				),
				'ARRAY_A'
			);
			$popular_products = array_map(
				function( $product ) {
					return $product['post_id'];
				},
				$popular_products
			);

			return $popular_products;
		}
	}

	Recommended_Products::set_instance();

}
