<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\ViewGenerators;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Statamic\Addons\XMLSitemaps\Pieces\Sitemaps\Sitemap;
use Statamic\Addons\XMLSitemaps\Pieces\Sitemaps\SitemapFactory;
use Statamic\Addons\XMLSitemaps\Pieces\SitemapIndex;
use Statamic\Addons\XMLSitemaps\XMLSitemapsConfig;
use Statamic\Extend\Extensible;

class SitemapIndexView {

	use Extensible;

	protected $sitemapConfigs;

	protected $xmlHeader;

	public function __construct() {

		$this->sitemapConfigs = collect( ( new XMLSitemapsConfig )->getSitemapConfig() );

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

		$sitemaps = $this->getAllSitemapPages();

		$sitemapIndex = (new SitemapIndex)->with($sitemaps);

		return $this->view(
			'index',
			[
				'xmlHeader' => $this->xmlHeader,
				'sitemapIndex' => $sitemapIndex,
			]
		);

	}

	/**
	 * Makes all the Sitemaps in the config and their
	 * respective pages.
	 *
	 * @param bool $validOnly Return only valid sitemaps
	 *
	 * @return \Illuminate\Support\Collection
	 */
	protected function getAllSitemapPages( $validOnly = true ) {

		// We need a new collection to hold all the Sitemaps
		// and their respective pages, since otherwise we'll
		// be modifying the collection we're iterating over.
		$allPages = new Collection();

		$this->getAllSitemapFirstPages( $validOnly )
			// Pass in our new collection by reference,
			// so we can add to it from within the closure.
			 ->map( function ( $sitemap ) use ( &$allPages ) {
				/* @var Sitemap $sitemap */

				// Add the Sitemap's first page to our collection.
				$allPages->push( $sitemap );

				// If the Sitemap has more than one page (ask it, it's self aware!),
				// then we can ask it to 6th day itself and create clones for each
				// page it requires.
				if ( $sitemap->hasMultiplePages() ) {
					for ( $page = 2; $page <= $sitemap->getPageCount(); $page ++ ) {
						$allPages->push( $sitemap->createPage( $page ) );
					}
				}

			} );

		return $allPages;
	}

	/**
	 * Creates the first page of every Sitemap and
	 * returns them.
	 *
	 * @param bool $validOnly Return only valid sitemaps
	 *
	 * @return \Illuminate\Support\Collection
	 */
	protected function getAllSitemapFirstPages( $validOnly = true ) {

		return $this->sitemapConfigs
			->map( function ( $sitemapConfig ) {
				return SitemapFactory::create(
					$sitemapConfig['type'],
					$sitemapConfig['handle'],
					$sitemapConfig['alias'],
					1
				);
			} )
			->filter( function ( $sitemap ) use ( $validOnly ) {
				/* @var Sitemap $sitemap */
				return $validOnly ? $sitemap->isValid() : true;
			} );

	}


}