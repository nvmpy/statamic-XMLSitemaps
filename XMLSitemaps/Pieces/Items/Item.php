<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\Items;


use Statamic\Extend\Extensible;

abstract class Item {

	use Extensible;


	protected $data;

	/**
	 * Returns the config value for trailing slash use.
	 *
	 * @return mixed
	 */
	protected function useTrailingSlashes(){
		return $this->getConfig('trailing_slash_urls');
	}

	abstract public function with($data);
	abstract public function getLastModified();
	abstract public function getLocation();

}