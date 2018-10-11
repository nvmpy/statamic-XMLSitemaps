<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\Sitemaps;


trait CachedParts {

	abstract public function getItemCacheKey();

	abstract public function clearItemCache();

	abstract public function clearViewCaches();

	abstract public function clearAllCaches( $index = true );

}
