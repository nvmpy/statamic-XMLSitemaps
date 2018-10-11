<?php

namespace Statamic\Addons\XMLSitemaps\Pieces\Sitemaps;


trait Paginated {

	protected $perPage;

	protected $pageCount;

	protected $currentPage;

	abstract function getPaginatedItems();

	public function paginated( $page ) {

		$page = !$page ? 1 : $page;

		$this->currentPage = $page;

		$this->perPage = $this->getConfig( 'max_entires_per_sitemap' );

		$itemCount = isset( $this->items ) ? $this->items->count() : 0;

		$this->pageCount = $itemCount
			? (int) ceil( $itemCount / (float) $this->perPage )
			: 0;


		$items = $this->items;


		/* @var \Illuminate\Support\Collection $items */
		$this->paginatedItems = $items->forPage( $this->currentPage, $this->perPage );

		return $this;
	}


	public function hasMultiplePages() {
		return ( $this->pageCount > 1 );
	}

	public function getPageCount() {
		return $this->pageCount;
	}

	public function getOffset() {

		if ( $this->currentPage === 1 ) {
			return 0;
		}

		return ( $this->currentPage * $this->perPage ) - 1; // -1 cos indexes start at 0, baby.

	}

	public function getCurrentPage() {
		return $this->currentPage;
	}

}