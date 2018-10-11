<?php

namespace Statamic\Addons\XMLSitemaps;

use Statamic\Extend\Extensible;

class XMLSitemapsConfig {

	use Extensible;

	/**
	 * The cache key used to keep the Sitemap Index.
	 */
	const CACHE_KEY_INDEX = 'sitemap-index-XMLSitemaps';

	/**
	 * The cache key template used to keep generated sitemap views.
	 */
	const CACHE_KEY_TEMPLATE_VIEW = '{alias}-{handle}-{page}-XMLSitemaps';

	/**
	 * The cache key template used hold sitemap items.
	 */
	const CACHE_KEY_TEMPLATE_ITEMS = '{handle}-{type}-XMLSitemaps';

	/**
	 * Holds the whole Addon config after instantiation.
	 *
	 * @var mixed
	 */
	protected $config;

	/**
	 * Holds the parsed sitemap config after instantiation.
	 *
	 * @var array
	 */
	protected $sitemapConfig;

	/**
	 * Holds the duration in minutes to cache stuff and things.
	 *
	 * @var integer $cacheDuration
	 */
	public $cacheDuration;

	/**
	 * FamiliarSitemapsConfig constructor.
	 */
	public function __construct() {
		$this->config        = $this->getConfig();
		$this->cacheDuration = $this->config['cache_length'];
		$this->sitemapConfig = $this->makeSitemapConfig();
	}

	/**
	 * Parses the Addon config looking for Sitemap configurations,
	 * returns complete configurations in an array.
	 *
	 * @return array
	 */
	public function makeSitemapConfig() {
		$config = $this->config;

		$sitemapConfig = [];

		// Add the page sitemap if enabled in config.
		if ( $config['show_page_sitemap'] ) {
			$alias       = $config['page_sitemap_alias'] ? $config['page_sitemap_alias'] : 'page';
			$entryConfig = [ 'type' => 'page', 'handle' => 'page', 'alias' => $alias ];
			array_push( $sitemapConfig, $entryConfig );
		}

		// For each of the allowed sitemap types, check their config grids and
		// push configs onto the sitemapConfig array as we find them.
		$types = [ 'collection', 'taxonomy' ];
		foreach ( $types as $type ) {

			$key = $type . '_sitemaps';
			$typeKeyExistsAndIsntEmpty = (array_key_exists( $key, $config ) && count( $config[ $key ] ) > 0);

			if ( $typeKeyExistsAndIsntEmpty ) {

				foreach ( $config[ $key ] as $sitemap ) {

					/*
					 * Example grid config for Collection type:
					 *
					 * collection_sitemaps:
					 *   -
					 *     collection: articles
					 *	   alias: post
					 *
					 */

					$handle = array_key_exists($type, $sitemap) ? $sitemap[ $type ] : null;

					// :O !! There's no handle. It's an empty entry, or they haven't
					// entered a Taxonomy handle. Skip it.
					if ( is_null( $handle ) ) {
						continue;
					}

					// Alias defaults to handle if not set in config.
					$alias  = array_key_exists( 'alias', $sitemap )
						? $sitemap['alias']
						: $handle;




					// All good. Pop that config on the array - lovely stuff.
					array_push(
						$sitemapConfig,
						[
							'type'   => $type,
							'handle' => $handle,
							'alias'  => $alias,
						]
					);

				}
			}
		}

		return $sitemapConfig;
	}

	/**
	 * Getter for the Sitemap config array.
	 *
	 * @return array
	 */
	public function getSitemapConfig() {
		return $this->sitemapConfig;
	}

	/**
	 * Returns the Sitemap config of any config
	 * that has the given alias, returns false if
	 * not found.
	 *
	 * @param string $alias
	 *
	 * @return array|boolean
	 */
	public function getConfigByAlias( $alias ) {
		return collect( $this->sitemapConfig )->first( function ( $key, $value ) use ( $alias ) {
			return $value['alias'] === $alias;
		}, false );
	}

	/**
	 * Returns the sitemap config of any config
	 * that has the given handle.
	 *
	 * @param string $handle
	 *
	 * @return array|boolean
	 */
	public function getConfigByHandle( $handle ) {
		return collect( $this->sitemapConfig )->first( function ( $key, $value ) use ( $handle ) {
			return $value['handle'] === $handle;
		}, false );
	}

	/**
	 * Bring me a valid alias, and in return, I shall
	 * give you the cache key for its page (or false,
	 * if you didn't listen to the valid part).
	 *
	 * @param string $alias
	 * @param int $page
	 *
	 * @return bool|string
	 */
	public function makeViewCacheKeyFromAlias( $alias, $page ){

		// Returns false if no config found for this alias.
		$config = $this->getConfigByAlias($alias);
		if (!$config){
			return false;
		}

		return strtr(
			self::CACHE_KEY_TEMPLATE_VIEW,
			[
				'{alias}' => $config['alias'],
				'{handle}' => $config['handle'],
				'{page}' => $page,
			]
		);

	}
}