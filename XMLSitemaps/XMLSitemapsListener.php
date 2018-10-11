<?php

namespace Statamic\Addons\XMLSitemaps;

use Illuminate\Support\Facades\Cache;
use Statamic\Addons\XMLSitemaps\Pieces\Sitemaps\SitemapFactory;
use Statamic\Events\Data\ContentSaved;
use Statamic\Extend\Extensible;
use Statamic\Extend\Listener;

class XMLSitemapsListener extends Listener {

	use Extensible;

	/**
	 * The events to be listened for, and the methods to call.
	 *
	 * @var array
	 */
	public $events = [
		\Statamic\Events\RoutesMapping::class     => 'checkAndAddRoutes',
		\Statamic\Events\Data\ContentSaved::class => 'clearSitemapCaches',
	];


	/**
	 * Checks that the config for the sitemap URLs have all the tags they need,
	 * and that we're on the default locale before handing off routes to
	 * addSitemapRoutes().
	 *
	 * @param $event
	 */
	public function checkAndAddRoutes( $event ) {
		$onDefaultLocale = ( site_locale() === default_locale() );
		$sitemapUrl      = $this->getConfig( 'single_sitemap_url' );
		$indexUrl        = $this->getConfig( 'sitemap_index_url' );

		// Check that both required tags exist in the sitemap URL.
		$siteMapUrlHasTags = ( strpos( $sitemapUrl, '{alias}' ) !== false
		                       && strpos( $sitemapUrl, '{page}' ) !== false );

		// Return a message telling the user to check their settings for the missing tags.
		if ( ! $siteMapUrlHasTags ) {
			$message = 'Oops, your Sitemap URLs are missing placeholders for either {alias} or 
							{page}, or both. Go check in the settings, quick!';

			$event->router->get( $indexUrl, function () use ( $message ) {
				return $message;
			} );

			return;
		} elseif ( ! $onDefaultLocale ) {
			return;
		}

		// Config all good! Add those routes, boy.
		$this->addSitemapRoutes( $event, $sitemapUrl, $indexUrl );
	}


	/**
	 * Does what it says on the tin.
	 *
	 * @param $event
	 * @param $sitemapUrl
	 * @param $indexUrl
	 */
	public function addSitemapRoutes( $event, $sitemapUrl, $indexUrl ) {

		// Can't get optional route parameters to work, so using both with & without parameter,
		// with a controller function to make up for it.
		$sitemapUrlWithoutPage = str_replace( '{page}', '', $sitemapUrl );

		$event->router->get( 'main-sitemap.xsl',
			'Statamic\Addons\XMLSitemaps\XMLSitemapsController@showStyling' );

		$event->router->get( $sitemapUrlWithoutPage,
			'Statamic\Addons\XMLSitemaps\XMLSitemapsController@showSitemap' );

		$event->router->get( $sitemapUrl,
			'Statamic\Addons\XMLSitemaps\XMLSitemapsController@showSitemapPage' )->where( 'page', '[0-9]+' );

		$event->router->get( $indexUrl,
			'Statamic\Addons\XMLSitemaps\XMLSitemapsController@showIndex' );

	}

	/**
	 * Takes the data of the Content that was saved, tries to figure out
	 * the relation to any sitemaps - creates and flushes the caches for
	 * those sitemaps.
	 *
	 * @param $event
	 */
	public function clearSitemapCaches( ContentSaved $event ) {

		if ( ! $this->getConfig( 'flush_cache_on_save' ) ) {
			return;
		}

		$data = collect( $event->data->toArray() );

		// We don't care about drafts, they don't affect sitemaps. Stupid drafts.
		$published = $data->get( 'published', false );
		if ( ! $published ) {
			return;
		}

		// I'm all about those unreadable ternaries. This one's not that bad, c'mon. If/else is just as ugly.
		$isTerm  = $data->get( 'is_term', false );
		$isEntry = $data->get( 'is_entry', false );
		$isPage  = $data->get( 'is_page', false );
		$type    = ( $isTerm ? 'taxonomy' : ( $isEntry ? 'entry' : ( $isPage ? 'page' : false ) ) );

		// Get the handle depending on the type.
		switch ( $type ) {
			case 'taxonomy':
				$handle = $data->get( 'taxonomy', false );
				break;
			case 'entry':
				$handle = $data->get( 'collection', false );
				break;
			case 'page':
				$handle = 'page';
				break;
			default:
				$handle = false;
		}

		$configHelper = new XMLSitemapsConfig();

		// Keep track of whether or not something cleared, so we can clear the index
		// at the very end, instead of possibly doing it twice. I know this gets overwritten,
		// ok, but it's used inside the first if and I feel like this
		$weClearedSomething = false;

		// If this is an entry, we might be interested in it even if the Collection
		// isn't in our Sitemap. Could belong to a Taxonomy that is in our Sitemap.
		//
		// This would of course rely on the Taxonomy field being under the "taxonomies"
		// key.
		if ( $type === 'entry' ) {
			$taxonomy = $data->get( 'taxonomy', false );

			if ( $taxonomy ) {

				// Under the right key, taxonomies are saved as "taxonomy/term",
				// we only need the taxonomy name, that's the handle.
				$taxonomyName = explode( '/', $taxonomy )[0];

				$weClearedSomething = $this->clearCacheByHandle( $taxonomyName, $configHelper );
			}
		}

		// We figured out a handle, try clearing it from the cache.
		if ( $handle ) {
			$weClearedSomething = $this->clearCacheByHandle( $handle, $configHelper );
		}

		// If we cleared any individual Sitemap, we'll be needing to clear the
		// index too. Last modification dates n' that.
		if ( $weClearedSomething ) {
			Cache::forget( $configHelper::CACHE_KEY_INDEX );
		}

	}

	/**
	 * Clears a Sitemap caches if the given handle exists in
	 * the config.
	 *
	 * Returns true if cleared, false if it didn't exist.
	 *
	 * @param string $handle
	 * @param XMLSitemapsConfig|boolean $configHelper
	 *
	 * @return bool
	 */
	protected function clearCacheByHandle( $handle, $configHelper = false ) {

		// Instantiate a configHelper if we weren't given one.
		$configHelper = ! is_bool( $configHelper ) ? $configHelper : new XMLSitemapsConfig();

		// Sitemap makin' time. It seems wasteful to create a Sitemap just for the sake
		// of clearing it from the cache. It'll most likely be getting its items from the
		// cache.
		$config = $configHelper->getConfigByHandle( $handle );

		// If we got a config back from the handle, great. Make the sitemap and ask
		// it to clear all its caches. We'll hold on the index for now.
		if ( $config ) {
			$sitemap = SitemapFactory::create(
				$config['type'],
				$config['handle'],
				$config['alias'],
				1
			);
			$sitemap->clearAllCaches( false );
			
			return true;
		}

		return false;

	}

}
