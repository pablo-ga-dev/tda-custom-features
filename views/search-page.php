<?php
$perPage = isset( $perPage ) ? (int) $perPage : 12;
?>
<div class="tda-search-page" data-tda-search-page-root
	data-tda-search-page-per-page="<?php echo esc_attr( (string) $perPage ); ?>">
	<div class="tda-search-page__toolbar">
		<div class="tda-search-page__bar-wrapper">
			<button class="tda-search-page__lens" type="button" aria-label="Buscar vehículo">
				<svg width="17" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
					aria-hidden="true">
					<path d="M7.667 12.667A5.333 5.333 0 107.667 2a5.333 5.333 0 000 10.667zM14.334 14l-2.9-2.9"
						stroke="currentColor" stroke-width="1.333" stroke-linecap="round" stroke-linejoin="round">
					</path>
				</svg>
			</button>
			<input class="tda-search-page__input" type="search" name="tda_search_page" placeholder="Busca tu vehículo"
				autocomplete="off" data-tda-search-page-input />
			<button class="tda-search-page__reset" type="button" aria-label="Limpiar búsqueda">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
					stroke-width="2" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
				</svg>
			</button>
		</div>
		<div class="tda-search-page__status" aria-live="polite" data-tda-search-page-status></div>
	</div>

	<div class="tda-search-page__results" data-tda-search-page-results></div>

	<div class="tda-search-page__pagination" data-tda-search-page-pagination>
		<span class="tda-search-page__page-label" data-tda-search-page-label></span>
		<div class="tda-search-page__scroll-sentinel" aria-hidden="true" data-tda-search-page-sentinel></div>
	</div>
</div>