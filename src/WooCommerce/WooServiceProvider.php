<?php
namespace Crear\TdaCf\WooCommerce;

use Crear\TdaCf\Core\ServiceProvider;
use function DI\autowire;

class WooServiceProvider implements ServiceProvider {
	private ClientFormSubmission $clientFormSubmission;
	private CartOrderMeta $cartOrderMeta;
	private WooRedirects $wooRedirects;

	public static function definitions(): array {
		return [
			FormData::class => autowire(),
			ClientFormSubmission::class => autowire(),
			CartOrderMeta::class => autowire(),
			WooRedirects::class => autowire(),
			self::class => autowire(),
		];
	}

	public function __construct( ClientFormSubmission $clientFormSubmission, CartOrderMeta $cartOrderMeta, WooRedirects $wooRedirects ) {
		$this->clientFormSubmission = $clientFormSubmission;
		$this->cartOrderMeta = $cartOrderMeta;
		$this->wooRedirects = $wooRedirects;
	}

	/**
	 * Summary of register
	 * @return void
	 */
	public function init(): void {
		add_action( 'template_redirect', [ $this->clientFormSubmission, 'handleSubmit' ] );
		add_action( 'template_redirect', [ $this->wooRedirects, 'maybeRedirect' ], 20 );
		add_action( 'rest_api_init', [ $this->clientFormSubmission, 'registerRestRoutes' ] );
		add_filter( 'woocommerce_add_cart_item_data', [ $this->clientFormSubmission, 'ensureCartItemUnique' ], 10, 4 );
		add_filter( 'woocommerce_get_item_data', [ $this->cartOrderMeta, 'renderCartItemMeta' ], 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', [ $this->cartOrderMeta, 'storeOrderItemMeta' ], 10, 4 );
	}
}