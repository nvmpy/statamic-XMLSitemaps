<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\Items;

use Carbon\Carbon;

class Term extends Item {


	/**
	 * @var \Statamic\Data\Taxonomies\Term $data
	 */
	protected $data;


	protected $lastModified;

	/**
	 * @param \Statamic\Data\Taxonomies\Term $data
	 *
	 * @return $this
	 */
	public function with( $data ) {
		$this->data = $data;

		// This is done ahead of time here, since you'll be calling it
		// to sort based on entries that belong to this term.
		$this->lastModified = $this->calcLastModified();

		return $this;
	}

	/**
	 * Returns the last modified date of this term or
	 * the last modified date of an entry that belongs to this
	 * them if it is closer to now.
	 *
	 * @return Carbon
	 */
	public function calcLastModified() {

		$termLastModified = $this->data->lastModified();

		$termCollection = $this->data->collection();

		if ( $termCollection->count() === 0 ) {
			return $termLastModified;
		}

		$termEntries = $termCollection->entries()
		                              ->removeUnpublished();

		$mostRecentlyModifiedEntry = $termEntries->multisort( 'date:desc' )
		                                         ->limit( 1 )
		                                         ->reduce( function ( $carry, $entry ) {

			                                         /* @var $entry \Statamic\Data\Entries\Entry */
			                                         return $entry->lastModified();
		                                         } );

		if ( is_null( $mostRecentlyModifiedEntry ) ) {
			return $termLastModified;
		}

		$lastModified = Carbon::now()->closest( $mostRecentlyModifiedEntry, $termLastModified );

		return $lastModified;
	}

	public function getLastModified() {
		return $this->lastModified;
	}

	/**
	 * Returns the URL of the term with a trailing
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