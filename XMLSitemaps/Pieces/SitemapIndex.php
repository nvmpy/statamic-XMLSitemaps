<?php

namespace Statamic\Addons\XMLSitemaps\Pieces;


class SitemapIndex {

	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $sitemaps;

	/**
	 * Consumes a collection of sitemaps.
	 *
	 * @param \Illuminate\Support\Collection $sitemaps
	 *
	 * @return $this
	 */
	public function with( $sitemaps ) {
		$this->sitemaps = $sitemaps;

		return $this;
	}

	/**
	 * Returns a collection of sitemaps ordered by
	 * the last modified date.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getSitemaps() {

		return $this->sitemaps;

		// If ya ever wanna sort sitemaps in the index - this ya boy.
		 /*
			->sortBy( function ( $sitemap ) {
				// @var Sitemap $sitemap
				return $sitemap->getLastModified();
			} );
		*/
	}

}
