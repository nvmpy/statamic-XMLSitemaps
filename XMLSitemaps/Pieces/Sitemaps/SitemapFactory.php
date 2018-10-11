<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\Sitemaps;


class SitemapFactory {

	/**
	 * Creates the correct sitemap object from a given
	 * type.
	 *
	 * @param string $type
	 * @param string $handle
	 * @param string $alias
	 * @param integer $page
	 **
	 *
	 * @return Taxonomy|Page|Collection|Sitemap
	 */
	public static function create( $type, $handle, $alias, $page) {

		switch ( $type ) {

			case 'page':
				$sitemap = ( new Page )->withAllPages()->withAlias( $alias )->paginated( $page );
				break;
			case 'taxonomy':
				$sitemap = ( new Taxonomy )->fromHandle( $handle )->withAlias( $alias )->paginated( $page );
				break;
			default:
				$sitemap = ( new Collection )->fromHandle( $handle )->withAlias( $alias )->paginated( $page );
				break;
		}

		return $sitemap;
	}

}