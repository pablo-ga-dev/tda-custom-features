<?php
namespace Crear\TdaCf\Shortcode;

use Crear\TdaCf\Core\ServiceProvider;
use Crear\TdaCf\Shortcode\SearchPage;
use Crear\TdaCf\Shortcode\SearchBar;
use Crear\TdaCf\Shortcode\TagImg;
use Crear\TdaCf\Shortcode\ClientDataForm;
use function DI\autowire;

class ShortcodeServiceProvider implements ServiceProvider {

	private SearchBar $searchBar;
	private SearchPage $searchPage;
	private TagImg $tagImg;
	private ClientDataForm $clientDataForm;
	public static function definitions(): array {
		return [
			SearchBar::class => autowire(),
			SearchPage::class => autowire(),
			TagImg::class => autowire(),
			ClientDataForm::class => autowire(),
			self::class => autowire(),
		];
	}

	public function __construct( SearchBar $searchBar, SearchPage $searchPage, TagImg $tagImg, ClientDataForm $clientDataForm ) {
		$this->searchBar = $searchBar;
		$this->searchPage = $searchPage;
		$this->tagImg = $tagImg;
		$this->clientDataForm = $clientDataForm;
	}

	/**
	 * Summary of register
	 * @return void
	 */
	public function init(): void {
		add_shortcode( 'tda_search_bar', [ $this->searchBar, 'render' ] );
		add_shortcode( 'tda_search_page', [ $this->searchPage, 'render' ] );
		add_shortcode( 'tda_tag_img', [ $this->tagImg, 'render' ] );
		add_shortcode( 'tda_client_data_form', [ $this->clientDataForm, 'render' ] );
	}
}