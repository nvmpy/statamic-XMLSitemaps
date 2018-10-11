<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\Sitemaps;

use Illuminate\Support\Facades\Cache;
use Statamic\Addons\XMLSitemaps\Pieces\Items\Entry as EntryItem;

class Collection extends Sitemap {


	/**
	 * @var \Statamic\Data\Entries\EntryCollection $items
	 */
	protected $items;

	/**
	 * @var \Statamic\Data\Entries\EntryCollection $items
	 */
	protected $paginatedItems;

	/**
	 * @var \Statamic\Contracts\Data\Entries\Collection $collection
	 */
	protected $collection;


	/**
	 * Returns the last modified date of the most recently modified
	 * entry within this sitemap.
	 *
	 * @return \Carbon\Carbon
	 */
	public function getLastModified() {
		return $this->paginatedItems->multisort( 'getlastmodified:desc' )
		                            ->limit( 1 )
		                            ->reduce( function ( $carry, $entry ) {
			                            /* @var EntryItem $entry */
			                            return $entry->getLastModified();
		                            } );
	}


	/**
	 * Creates a sitemap of entires in ascending last modified date order
	 * from a given handle.
	 *
	 * @param $handle
	 *
	 * @return $this
	 */
	public function fromHandle( $handle ) {

		$this->handle = $handle;
		$this->type   = 'collection';

		$this->collection = \Statamic\API\Collection::whereHandle( $handle );


		// Pull the items out of the cache if possible, these are cached for two
		// reasons.
		//
		// 1. We need to create a sitemap to check if it's valid etc, we don't wanna
		//    make these items from scratch every time.
		// 2. Page 2 of the sitemap has all the same items, it just returns a different
		//    view of the items depending on its currentPage var.
		$collection  = $this->collection;
		$this->items = $this->items = Cache::remember(
			$this->getItemCacheKey(),
			$this->getConfig( 'cache_length' ),
			function () use ( $collection ) {
				return $collection->entries()
				                  ->removeUnpublished()
				                  ->map( function ( $entry ) {
					                  return ( new EntryItem )
						                  ->with( $entry );
				                  } )
				                  ->sortBy( function ( $entry ) {
					                  /* @var EntryItem $entry */
					                  return $entry->getLastModified();
				                  } );
			} );

		$this->paginatedItems = $this->items->forPage( $this->currentPage, $this->perPage );

		return $this;
	}

	/**
	 * Returns true if the sitemap has entries and a
	 * valid route set.
	 *
	 * @return bool
	 */
	public function isValid() {
		$hasEntries = ( $this->items->count() > 0 );
		$hasRoute   = ( ! is_null( $this->collection->route() ) );

		return ( $hasEntries && $hasRoute );
	}
}