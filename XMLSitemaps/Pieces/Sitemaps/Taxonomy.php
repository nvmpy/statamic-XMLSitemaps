<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\Sitemaps;


use Illuminate\Support\Facades\Cache;
use Statamic\Addons\XMLSitemaps\Pieces\Items\Term as TermItem;

class Taxonomy extends Sitemap {

	/**
	 * @var \Statamic\Data\Taxonomies\TermCollection
	 */
	protected $items;

	/**
	 * @var \Statamic\Data\Taxonomies\TermCollection $items
	 */
	protected $paginatedItems;

	/**
	 * @var \Statamic\Contracts\Data\Taxonomies\Taxonomy $taxonomy
	 */
	protected $taxonomy;


	/**
	 * Returns the last modified date of the most recently modified
	 * term within this sitemap.
	 *
	 * Term last modified date can come from itself or any of the
	 * entries belonging to it.
	 *
	 * @return \Carbon\Carbon
	 */
	public function getLastModified() {
		return $this->paginatedItems
			->sortByDesc( function ( $term ) {
				/* @var TermItem $term */
				return $term->getLastModified();
			} )
			->limit( 1 )
			->reduce( function ( $carry, $term ) {
				/* @var TermItem $term */
				return $term->getLastModified();
			} );
	}


	/**
	 * Creates a sitemap of terms in ascending last modified date order
	 * from a given handle.
	 *
	 * @param $handle
	 *
	 * @return $this
	 */
	public function fromHandle( $handle ) {

		$this->handle = $handle;
		$this->type   = 'taxonomy';

		// Grab the Taxonomy - we need it to know if the route is valid or not.
		$this->taxonomy = \Statamic\API\Taxonomy::whereHandle( $handle );

		// Pull the items out of the cache if possible, these are cached for two
		// reasons.
		//
		// 1. We need to create a sitemap to check if it's valid etc, we don't wanna
		//    make these items from scratch every time.
		// 2. Page 2 of the sitemap has all the same items, it just returns a different
		//    view of the items depending on its currentPage var.
		$taxonomy    = $this->taxonomy;
		$this->items = Cache::remember(
			$this->getItemCacheKey(),
			$this->getConfig( 'cache_length' ),
			function () use ( $taxonomy ) {
				return $taxonomy->terms()
				         ->removeUnpublished()
				         ->map( function ( $term ) {
					         return ( new TermItem )->with( $term );
				         } )
				         ->sortBy( function ( $term ) {
					         /* @var TermItem $term */
					         return $term->getLastModified();
				         } );
			} );


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
		$hasRoute   = ( ! is_null( $this->taxonomy->route() ) );

		return ( $hasEntries && $hasRoute );
	}


}