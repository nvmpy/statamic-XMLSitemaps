<?php

namespace Statamic\Addons\XMLSitemaps;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Statamic\Addons\XMLSitemaps\Pieces\ViewGenerators\SingleSitemapView;
use Statamic\Addons\XMLSitemaps\Pieces\ViewGenerators\SitemapIndexView;
use Statamic\Extend\Controller;

class XMLSitemapsController extends Controller {

	/**
	 * Show the sitemap index containing all the valid Sitemaps
	 * that are in the current config and all their respective
	 * pages (if they have any).
	 *
	 * @return mixed
	 */
	public function showIndex() {

		$configHelper = new XMLSitemapsConfig();

		$indexCacheKey = $configHelper::CACHE_KEY_INDEX;
		$cacheDuration = $configHelper->cacheDuration;

		$view = Cache::remember(
			$indexCacheKey,
			$cacheDuration,
			function () {
				$indexViewGenerator = new SitemapIndexView();
				return $indexViewGenerator->make()->render();
			}
		);

		return response( $view )->header( 'Content-Type', 'text/xml' );

	}

	/**
	 * This is a wrapper around showSitemap for the benefit
	 * of catching {page} in the URL.
	 *
	 * I know Laravel has optional URL parameters, but I couldn't
	 * get them to work, so this'll do for now.
	 *
	 * @param $alias
	 * @param $pageInUrl
	 *
	 * @return mixed
	 */
	public function showSitemapPage( $alias, $pageInUrl ) {
		return $this->showSitemap( $alias, $pageInUrl );
	}


	/**
	 * Create and return a Single valid sitemap from the provided
	 * alias after running through some initial checks.
	 *
	 * @param $alias
	 * @param $pageInUrl
	 *
	 * @return mixed
	 */
	public function showSitemap( $alias, $pageInUrl = false ) {

		// It'd be nice to be able to 404 here early if we know the page
		// provided doesn't match up with the config or the amount of
		// pages a sitemap has, but to do that we'd have to make the
		// sitemap outside of the cache closure, so it's more efficient
		// to just continue and let the cache return a 404 for this URL.

		$configHelper = new XMLSitemapsConfig();

		// Returns false if no config found, so we can 404 safe in the knowledge
		// that they made the URL up. Dummies.
		$viewCacheKey = $configHelper->makeViewCacheKeyFromAlias( $alias, $pageInUrl ? $pageInUrl : 1 );

		if ( ! $viewCacheKey ) {
			abort( 404 );
		}

		$cacheDuration = $configHelper->cacheDuration;

		$view = Cache::remember(
			$viewCacheKey,
			$cacheDuration,
			function () use ($alias, $pageInUrl) {
				$sitemapViewGenerator = new SingleSitemapView($alias, $pageInUrl);
				return $sitemapViewGenerator->make()->render();
			}
		);

		return response( $view )->header( 'Content-Type', 'text/xml' );

	}

	/**
	 * Serves the .xsl stylesheet for the sitemaps.
	 *
	 * @return mixed
	 */
	public function showStyling() {
		return response(
			File::get( $this->getDirectory() . '/resources/assets/xsl/main-sitemap.xsl' )
		)->header( 'Content-Type', 'text/xsl' );
	}

}
