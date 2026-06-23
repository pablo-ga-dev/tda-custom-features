<?php
namespace Crear\TdaCf\Shortcode;

use Crear\TdaCf\Core\ServiceProvider;
use Crear\TdaCf\Shortcode\SearchBar;
use Crear\TdaCf\Shortcode\TagImg;
use function DI\autowire;

class ShortcodeServiceProvider implements ServiceProvider {

	private SearchBar $searchBar;
	private TagImg $tagImg;

	public static function definitions(): array {
		return [
			SearchBar::class => autowire(),
			TagImg::class => autowire(),
			self::class => autowire(),
		];
	}

	public function __construct( SearchBar $searchBar, TagImg $tagImg ) {
		$this->searchBar = $searchBar;
		$this->tagImg = $tagImg;
	}

	/**
	 * Summary of register
	 * @return void
	 */
	public function init(): void {
		add_shortcode( 'tda_search_bar', [ $this->searchBar, 'render' ] );
		add_shortcode( 'tda_tag_img', [ $this->tagImg, 'render' ] );
	}
}