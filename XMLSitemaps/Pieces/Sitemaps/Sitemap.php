<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\Sitemaps;

use Carbon\Carbon;
use DeepCopy\DeepCopy;
use Illuminate\Support\Facades\Cache;
use Statamic\Addons\XMLSitemaps\XMLSitemapsConfig;
use Statamic\API\URL;
use Statamic\Extend\Extensible;

abstract class Sitemap {

	use Extensible;
	use Paginated;
	use CachedParts;

	protected $type;
	protected $handle;
	protected $alias;

	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $items;

	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $paginatedItems;


	public function getItems() {
		return $this->items;
	}

	public function getType() {
		return $this->type;
	}

	public function getHandle() {
		return $this->handle;
	}

	public function getAlias() {
		return $this->alias;
	}

	public function withAlias( $alias ) {
		$this->alias = $alias;

		return $this;
	}

	/**
	 * @return Carbon
	 */
	abstract public function getLastModified();

	/**
	 * @return boolean
	 */
	abstract public function isValid();


	/**
	 * Returns the absolute URL of this sitemap.
	 *
	 * @return string
	 */
	public function getLocation() {

		$values = [
			'{alias}' => $this->alias,
			'{page}'  => ( $this->hasMultiplePages() ||
			               $this->getConfig( 'show_page_number_if_only_page' ) )
				? $this->currentPage
				: '',
		];

		return URL::makeAbsolute(
			strtr( $this->getConfig( 'single_sitemap_url' ), $values )
		);
	}



	// Pagination methods

	/**
	 * Creates a clone of this Sitemap, but with
	 * a different current page.
	 *
	 * @param $newPageNumber
	 *
	 * @return Sitemap
	 */
	public function createPage( $newPageNumber ) {
		$newPage = clone $this;
		$newPage->paginated( $newPageNumber );

		return $newPage;
	}

	/**
	 * Returns a Collection of this Sitemap's items that
	 * belong to the current page.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getPaginatedItems() {
		return $this->paginatedItems;
	}


	// Cache methods

	/**
	 * Clear out the item cache, the view cache for every
	 * page, and the index by default.
	 *
	 * @param bool $index
	 */
	public function clearAllCaches( $index = true ) {
		$this->clearItemCache();
		$this->clearViewCaches();
		if ( $index ) {
			Cache::forget( XMLSitemapsConfig::CACHE_KEY_INDEX );
		}
	}

	/**
	 * Clears the item cache that belongs to this Sitemap
	 * only.
	 */
	public function clearItemCache() {
		Cache::forget( $this->getItemCacheKey() );
	}

	/**
	 * Formats and returns the cache key used for this
	 * Sitemap's items.
	 *
	 * @return string
	 */
	public function getItemCacheKey() {
		$template = XMLSitemapsConfig::CACHE_KEY_TEMPLATE_ITEMS;

		return strtr( $template, [ '{type}' => $this->type, '{handle}' => $this->handle ] );
	}

	/**
	 * Clears the view caches that belong to this Sitemap.
	 */
	public function clearViewCaches() {
		foreach ( $this->getViewCacheKeys() as $cacheKey ) {
			Cache::forget( $cacheKey );
		}
	}

	/**
	 * Formats and returns the cache keys used for this
	 * Sitemap's views. One for each page.
	 *
	 * @return array
	 */
	public function getViewCacheKeys() {
		$template = XMLSitemapsConfig::CACHE_KEY_TEMPLATE_ITEMS;
		$handle   = $this->handle;
		$alias    = $this->alias;

		// Return the formatted cache key for each page.
		return array_map(
			function ( $page ) use ( $template, $alias, $handle ) {
				return strtr( $template,
					[ '{alias}' => $alias, '{handle}' => $handle, '{page}' => $page ]
				);
			},
			range( 1, $this->pageCount )
		);

	}
}