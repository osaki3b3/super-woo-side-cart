<?php
/**
 * Plugin Name: WooCommerce Side Cart One (Mini Plugin One)
 * Description: A lightweight, no-dependency side cart drawer for WooCommerce. Includes HTML, CSS, and JavaScript, integrated via WordPress + WooCommerce hooks & AJAX.
 * Version: 1.0.0
 * Author: Estuardo Bengoechea
 * License: GPL-2.0+
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WSC_Woo_Side_Cart' ) ) :
class WSC_Woo_Side_Cart {
	const NONCE_ACTION = 'wsc_nonce_action';
	const NONCE_NAME   = 'wsc_nonce';
	const HANDLE       = 'wsc-side-cart';

	public function __construct() {
		// Ensure WooCommerce is active
		add_action( 'plugins_loaded', [ $this, 'maybe_boot' ], 11 );
	}

	public function maybe_boot() {
		if ( ! class_exists( 'WooCommerce' ) ) { return; }

		// Assets
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// Markup in footer
		add_action( 'wp_footer', [ $this, 'render_drawer_markup' ] );

		// AJAX endpoints
		add_action( 'wp_ajax_wsc_get_cart',        [ $this, 'ajax_get_cart' ] );
		add_action( 'wp_ajax_nopriv_wsc_get_cart', [ $this, 'ajax_get_cart' ] );

		add_action( 'wp_ajax_wsc_update_qty',        [ $this, 'ajax_update_qty' ] );
		add_action( 'wp_ajax_nopriv_wsc_update_qty', [ $this, 'ajax_update_qty' ] );

		add_action( 'wp_ajax_wsc_remove_item',        [ $this, 'ajax_remove_item' ] );
		add_action( 'wp_ajax_nopriv_wsc_remove_item', [ $this, 'ajax_remove_item' ] );

		add_action( 'wp_ajax_wsc_remove_item_add_item',        [ $this, 'ajax_remove_item_add_item' ] );
		add_action( 'wp_ajax_nopriv_wsc_remove_item_add_item', [ $this, 'ajax_remove_item_add_item' ] );


		add_action( 'wp_ajax_wsc_apply_coupon',        [ $this, 'ajax_apply_coupon' ] );
		add_action( 'wp_ajax_nopriv_wsc_apply_coupon', [ $this, 'ajax_apply_coupon' ] );

		// Update the floating button count via Woo fragments after add-to-cart
		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'fragments_update' ] );
	}

	 public function enqueue_assets() {
		wp_enqueue_style( self::HANDLE . '-slick-css', '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css', [], '1.0.0' );
		//wp_enqueue_style( self::HANDLE . '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css', [], '1.0.0' );

        wp_enqueue_script( self::HANDLE . '-slick-js', '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', [ 'jquery' ], '1.0.0', true );
		
        wp_enqueue_style( self::HANDLE . '-css', plugin_dir_url( __FILE__ ) . 'css/side-cart.css', [], '1.0.0' );
        wp_enqueue_script( self::HANDLE . '-js', plugin_dir_url( __FILE__ ) . 'js/side-cart.js', [ 'jquery' ], '1.0.0', true );


		




        wp_localize_script( self::HANDLE . '-js', 'WSC', [
            'ajax'        => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( self::NONCE_ACTION ),
            'cartUrl'     => function_exists('wc_get_cart_url')     ? wc_get_cart_url()     : '#',
            'checkoutUrl' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '#',
        ]);
    }


	public function render_drawer_markup() {
		if ( is_admin() && ! wp_doing_ajax() ) { return; }
		if ( apply_filters( 'wsc_disable_on_page', false ) ) { return; }

		$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		?>
		<div class="wsc-overlay" aria-hidden="true"></div>
		<aside class="wsc-drawer" role="dialog" aria-label="Shopping cart">
			<header class="wsc-header">
                <div class="wsc-title"><?php esc_html_e( 'Products', 'wsc' ); ?> (<span class="wsc-cart-count"><?php echo $count; ?></span>)</div>
                <button class="wsc-close" aria-label="<?php esc_attr_e('Close cart','wsc'); ?>">&times;</button>
            </header>
			<div class="wsc-body">
				<?php echo $this->render_cart_items_html(); // <========================= initial content ?>
			</div>
		</aside>
		<?php
	}

	public function fragments_update( $fragments ) {
		$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		$fragments['.wsc-cart-count'] = '<span class="wsc-cart-count">' . (int) $count . '</span>';
		return $fragments;
	}

	/** AJAX: Get cart HTML */
	public function ajax_get_cart() {
		$this->verify_nonce();
		wp_send_json_success([
			'html' => $this->render_cart_items_html(),
			'count' => WC()->cart->get_cart_contents_count(),
			'subtotal_html' => $this->get_subtotal_html(),
			'total_html' => $this->get_total_html(),
		]);
	}

	/** AJAX: Update qty */
	public function ajax_update_qty() {
		$this->verify_nonce();
		$key = sanitize_text_field( $_POST['key'] ?? '' );
		$qty = max( 0, (int) ($_POST['qty'] ?? 0) );
		if ( $key ) {
			WC()->cart->set_quantity( $key, $qty, true ); // true to recalc totals
		}
		wp_send_json_success();
	}

	/** AJAX: Remove item */
	public function ajax_remove_item() {
		$this->verify_nonce();
		$key = sanitize_text_field( $_POST['key'] ?? '' );
		if ( $key ) {
			WC()->cart->remove_cart_item( $key );
		}
		wp_send_json_success();
	}

	/** AJAX: Remove item add item */
	public function ajax_remove_item_add_item() {
		$this->verify_nonce();
		$key = sanitize_text_field( $_POST['key'] ?? '' ); // box to remove
		$key2 = sanitize_text_field( $_POST['key2'] ?? '' ); // box to add
		$parentid = sanitize_text_field( $_POST['parentid'] ?? '' );
		$keyid = sanitize_text_field( $_POST['keyid'] ?? '' );
		$boxname = sanitize_text_field( $_POST['boxname'] ?? '' );
		$boxprice = sanitize_text_field( $_POST['boxprice'] ?? '0.00' );

		if ( $key && $key2 && $parentid && $keyid && $boxname  ) {

			// <=======================================
			// loop through the CART ITEMS to verify the BOX, and assign the new VALUE to the ADD-on
			foreach  ( WC()->cart->get_cart() as $cart_item_key => $cart_item_data ) {

				$addons = [];

				if ( 
					in_array($cart_item_data['product_id'], array( 12073, 12068, 47464 ) ) && 
					isset($cart_item_data['parent_item_key']) && $cart_item_data['parent_item_key'] == $keyid  
				){

						$addons = WC()->cart->cart_contents[$cart_item_data['parent_item_key']]['addons'];

						$return_addons = [];

						// change the BOX VALUE AND PRICE IN THE PARENT PRODUCT ADDONS SECTION
						foreach($addons as $addon_key => $addon_data){

							if (isset($addon_data['name']) && $addon_data['name'] == "Ring Box") {
								$addon_data['value'] = $boxname;
								$addon_data['price'] = $boxprice;

								$return_addons[] = $addon_data;
							}else{
								$return_addons[] = $addon_data;
							}

						}

						WC()->cart->cart_contents[$cart_item_data['parent_item_key']]['addons'] = $return_addons;
						WC()->cart->set_session();
						// global $woocommerce;
						// $woocommerce->cart->cart_contents[$cart_item_key]['whatever_meta'] = 'testing';
						// $woocommerce->cart->set_session();   // when in ajax calls, saves it.

				}
			
			}
			// <============== END CART ADDONS VERIFICATION AND SET =========================
			
			$cartId = WC()->cart->generate_cart_id( $key );
			$cartItemKey = WC()->cart->find_product_in_cart( $key );
			WC()->cart->remove_cart_item( $cartItemKey );

			$data_return = '$cartId is:'.print_r($cartId, true).', ItemKey: '.print_r($cartItemKey, true). ", \n\r ";
			file_put_contents(__DIR__."/remove_item_add_item.log", print_r( $data_return , true), FILE_APPEND);
			
			//WC()->cart->remove_cart_item( $key );
			// WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );
			//WC()->cart->remove_cart_item( $key );
			//WC()->cart->add_to_cart( $key2, 1, 0, [], ['parent_item_key' =>  ] );
			$box_data = array(
				'parent_product_id' => $parentid,
				'parent_item_key' => $keyid
			);

			//file_put_contents(__DIR__."/box_data.log", print_r( $box_data , true), FILE_APPEND);
			WC()->cart->add_to_cart( $key2, 1, 0, array(), $box_data );
			
		}
		wp_send_json_success();
	}


	/** AJAX: Apply coupon */
	public function ajax_apply_coupon() {
		$this->verify_nonce();
		$code = wc_format_coupon_code( wp_unslash( $_POST['code'] ?? '' ) );
		$message = '';
		if ( $code ) {
			$applied = WC()->cart->apply_coupon( $code );
			if ( is_wp_error( $applied ) ) {
				$message = $applied->get_error_message();
			} else if ( ! $applied ) {
				$message = __( 'Coupon not applied.', 'wsc' );
			} else {
				$message = __( 'Coupon applied!', 'wsc' );
			}
		}
		WC()->cart->calculate_totals();
		wp_send_json_success([ 'message' => $message ]);
	}

	private function verify_nonce() {
		$nonce = $_POST[ self::NONCE_NAME ] ?? '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}
	}

	private function render_cart_items_html() {
		$items = WC()->cart ? WC()->cart->get_cart() : [];

		//print_r($items);

		if ( empty( $items ) ) {
			return '<div class="wsc-empty">' . esc_html__( 'Your cart is empty.', 'wsc' ) . '</div>';
		}
		
		ob_start();
		require_once(plugin_dir_path(__FILE__).'src/views/side-cart-products.php');
		
		return ob_get_clean();
	}

	private function get_subtotal_html() {
		if ( ! WC()->cart ) { return wc_price(0); }
		return WC()->cart->get_cart_subtotal(); // already formatted HTML string
	}

	private function get_total_html() {
		if ( ! WC()->cart ) { return wc_price(0); }
		// get_total() returns formatted string, but may include <small> fee text; we show formatted amount
		return wc_price( WC()->cart->get_totals()['total'] ?? 0 );
	}
}
endif;

new WSC_Woo_Side_Cart();
