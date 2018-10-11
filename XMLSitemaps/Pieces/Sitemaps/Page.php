<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\Sitemaps;

use Illuminate\Support\Facades\Cache;
use Statamic\Addons\XMLSitemaps\Pieces\Items\Page as PageItem;

class Page extends Sitemap {

	/**
	 * @var \Statamic\Data\Pages\PageCollection $items
	 */
	public $items;

	/**
	 * @var \Statamic\Data\Pages\PageCollection $items
	 */
	public $paginatedItems;

	/**
	 * Creates a sitemap of all pages in ascending last modified date order.
	 *
	 * @return $this
	 */
	public function withAllPages() {

		$this->handle = 'page';
		$this->type   = 'page';

		// Pull the items out of the cache if possible, these are cached for two
		// reasons.
		//
		// 1. We need to create a sitemap to check if it's valid etc, we don't wanna
		//    make these items from scratch every time.
		// 2. Page 2 of the sitemap has all the same items, it just returns a different
		//    view of the items depending on its currentPage var.
		$this->items          = Cache::remember(
			$this->getItemCacheKey(),
			$this->getConfig( 'cache_length' ),
			function () {
				return \Statamic\API\Page::all()
				                         ->removeUnpublished()
				                         ->map( function ( $page ) {
					                         return ( new PageItem )
						                         ->with( $page );
				                         } )
				                         ->sortBy( function ( $page ) {
					                         /* @var PageItem $page */
					                         return $page->getLastModified();
				                         } );
			} );

		$this->paginatedItems = $this->items->forPage( $this->currentPage, $this->perPage );

		return $this;
	}


	/**
	 * Returns the last modified date of the most recently modified
	 * page within this sitemap.
	 *
	 * @return \Carbon\Carbon
	 */
	public function getLastModified() {
		return $this->paginatedItems->multisort( 'getlastmodified:desc' )
		                            ->limit( 1 )
		                            ->reduce( function ( $carry, $page ) {
			                            /* @var PageItem $page */
			                            return $page->getLastModified();
		                            } );
	}

	/**
	 * Returns true if the sitemap has entries and a
	 * valid route set.
	 *
	 * @return bool
	 */
	public function isValid() {
		$hasEntries = ( $this->items->count() > 0 );
		$hasRoute   = true;

		return ( $hasEntries && $hasRoute );
	}

}