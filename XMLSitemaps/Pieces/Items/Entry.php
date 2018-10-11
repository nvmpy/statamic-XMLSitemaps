<?php


namespace Statamic\Addons\XMLSitemaps\Pieces\Items;


class Entry extends Item {


	/**
	 * @var \Statamic\Data\Entries\Entry $data
	 */
	protected $data;

	/**
	 * @param \Statamic\Data\Entries\Entry $data
	 *
	 * @return $this
	 */
	public function with( $data ) {
		$this->data = $data;

		return $this;
	}

	/**
	 * Returns the last modified date of this Entry.
	 *
	 * @return string
	 */
	public function getLastModified() {
		return $this->data->lastModified();
	}

	/**
	 * Returns the URL of the entry with a trailing
	 * slash enforced if required.
	 *
	 * @return string
	 */
	public function getLocation() {

		$url           = $this->data->absoluteUrl();
		$endsWithSlash = ( mb_substr( $url, - 1 ) === '/' );

		if ( ! $endsWithSlash && $this->useTrailingSlashes() ) {
			$url .= '/';
		}

		return $url;

	}
}