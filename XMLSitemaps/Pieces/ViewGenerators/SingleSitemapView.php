<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\ViewGenerators;


use Illuminate\Support\Facades\URL;
use Statamic\Addons\XMLSitemaps\Pieces\Sitemaps\Collection;
use Statamic\Addons\XMLSitemaps\Pieces\Sitemaps\Page;
use Statamic\Addons\XMLSitemaps\Pieces\Sitemaps\Sitemap;
use Statamic\Addons\XMLSitemaps\Pieces\Sitemaps\SitemapFactory;
use Statamic\Addons\XMLSitemaps\Pieces\Sitemaps\Taxonomy;
use Statamic\Addons\XMLSitemaps\XMLSitemapsConfig;
use Statamic\Extend\Extensible;

class SingleSitemapView {

	use Extensible;

	protected $config;

	protected $xmlHeader;

	/**
	 * The page that was in the URL that got us here, we
	 * will need this to validate against the settings.
	 *
	 * @var int
	 */
	protected $pageInUrl;

	/**
	 * The actual page we want to show, isn't necessarily
	 * the page that was in the URL
	 *
	 * @var int
	 */
	protected $pageToShow;

	public function __construct( $alias, $pageInUrl ) {

		$this->config    = ( new XMLSitemapsConfig )->getConfigByAlias( $alias );
		$this->pageInUrl = $pageInUrl;
		$this->pageToShow= $pageInUrl === 0 ? 1 : (int) $pageInUrl;

		$this->xmlHeader = sprintf(
			'<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="%s"?>',
			URL::to( '/main-sitemap.xsl' )

		);
	}


	/**
	 * Make and return the View for this Sitemap.
	 *
	 * @return \Illuminate\View\View
	 */
	public function make() {

		// We were given an Alias that doesn't belong to any config. 404.
		if ( ! $this->config ) {
			abort( 404 );
		}

		$sitemap = $this->getFirstPage();

		// Check the Sitemap is valid (has Entries and a route).
		if ( ! $sitemap->isValid() ) {
			abort( 404 );
		}

		// Check it was requested on a valid URL.
		if ( ! $this->isUrlValidForSitemap( $sitemap ) ) {
			abort( 404 );
		}

		// If the page we need to show isn't the first, ask the
		// Sitemap to clone itself and return the correct page.
		if ( ! ($this->pageToShow === 1) ) {
			$sitemap = $sitemap->createPage( $this->pageToShow );
		}

		return $this->view(
			'sitemap',
			[
				'xmlHeader' => $this->xmlHeader,
				'sitemap'   => $sitemap,
			]
		);

	}

	/**
	 * Create and return the first page of the
	 * Sitemap.
	 *
	 * @return Collection|Page|Sitemap|Taxonomy
	 */
	protected function getFirstPage() {
		return SitemapFactory::create(
			$this->config['type'],
			$this->config['handle'],
			$this->config['alias'],
			1
		);
	}

	/**
	 * A URL should only contain a page number if the Sitemap has
	 * more than one page OR the page requested is the first page
	 * and the Settings dictate that we always show the first page
	 * number.
	 *
	 * I was very tired writing this, in case you couldn't guess.
	 *
	 * @param Sitemap $sitemap
	 *
	 * @return bool
	 */
	protected function isUrlValidForSitemap( $sitemap ) {
		$pageWasInUrl                = ( $this->pageInUrl > 0 );
		$hasPages                    = $sitemap->hasMultiplePages();
		$pageCount                   = $sitemap->getPageCount();
		$showPageNumberIfOnlyOnePage = $this->getConfig( 'show_page_number_if_only_page' );

		$hasPageNumberWhenItShouldnt = (
			// There's a page number in the URL, but this Sitemap only has one
			// page, and the setting to show the page number even if there's
			// only one page is set to false.
		( $pageWasInUrl && ! $hasPages && ! $showPageNumberIfOnlyOnePage )
		);

		$doesntHavePageNumberWhenItShould = (
			// There was no page in the URL, but this Sitemap has pages.
			( ! $pageWasInUrl && $hasPages )

			// There's no page number in the URL but the setting to use the page
			// number even if there's only one page is set to yes.
			|| ( $showPageNumberIfOnlyOnePage && $this->pageInUrl === 0 && ! $hasPages )
		);

		$hasAPageNumberThatDoesntExist = (
			$this->pageToShow > $pageCount
		);

		return ! ( $hasPageNumberWhenItShouldnt || $doesntHavePageNumberWhenItShould || $hasAPageNumberThatDoesntExist );
	}
}
