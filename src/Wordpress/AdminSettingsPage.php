<?php

namespace Crear\TdaCf\Wordpress;

class AdminSettingsPage {
	public const MENU_SLUG = 'tda-custom-features-settings';
	public const SETTINGS_GROUP = 'tda_custom_features_settings';
	public const OPTION_TRAMITE_PRODUCT_ID = 'tda_cf_tramite_product_id';

	public function registerAdminMenu(): void {
		add_menu_page(
			__( 'TDA Custom Features', 'tda-custom-features' ),
			__( 'TDA Custom Features', 'tda-custom-features' ),
			'manage_options',
			self::MENU_SLUG,
			[ $this, 'renderPage' ],
			'dashicons-cart',
			56
		);
	}

	public function registerSettings(): void {
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_TRAMITE_PRODUCT_ID,
			[
				'type' => 'integer',
				'sanitize_callback' => [ $this, 'sanitizeProductId' ],
				'default' => 0,
			]
		);
	}

	/**
	 * @param mixed $value
	 */
	public function sanitizeProductId( $value ): int {
		$productId = max( 0, (int) $value );

		if ( $productId === 0 ) {
			return 0;
		}

		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $productId ) : null;
		if ( ! $product ) {
			add_settings_error(
				self::OPTION_TRAMITE_PRODUCT_ID,
				'tda_invalid_tramite_product',
				__( 'Selecciona un producto de WooCommerce valido para el tramite.', 'tda-custom-features' )
			);

			return 0;
		}

		return $productId;
	}

	public function renderPage(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$selectedProductId = (int) get_option( self::OPTION_TRAMITE_PRODUCT_ID, 0 );
		$products = [];

		if ( function_exists( 'wc_get_products' ) ) {
			$products = wc_get_products( [
				'status' => 'publish',
				'limit' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
			] );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'TDA Custom Features', 'tda-custom-features' ); ?></h1>
			<p><?php esc_html_e( 'Configura el producto de tramite que se anadira al carrito cuando un cliente inicie la solicitud. La compra de la etiqueta ambiental queda fuera de este flujo.', 'tda-custom-features' ); ?>
			</p>

			<?php settings_errors( self::OPTION_TRAMITE_PRODUCT_ID ); ?>

			<form method="post" action="options.php">
				<?php settings_fields( self::SETTINGS_GROUP ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label
								for="tda-cf-tramite-product-id"><?php esc_html_e( 'Producto de tramite', 'tda-custom-features' ); ?></label>
						</th>
						<td>
							<select id="tda-cf-tramite-product-id"
								name="<?php echo esc_attr( self::OPTION_TRAMITE_PRODUCT_ID ); ?>">
								<option value="0"><?php esc_html_e( 'Selecciona un producto', 'tda-custom-features' ); ?>
								</option>
								<?php foreach ( $products as $product ) : ?>
									<option value="<?php echo esc_attr( (string) $product->get_id() ); ?>" <?php selected( $selectedProductId, $product->get_id() ); ?>>
										<?php echo esc_html( sprintf( '#%d %s', $product->get_id(), $product->get_name() ) ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Este producto recibira los datos del cliente y del vehiculo en el carrito y en el pedido.', 'tda-custom-features' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}