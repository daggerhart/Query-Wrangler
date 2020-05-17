<?php

namespace QueryWrangler\Handler\Sort;

use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\Query\QwQuery;

class SortTypeManager extends HandlerTypeManagerBase {

	/**
	 * {@inheritDoc}
	 * @return SortInterface
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * {@inheritDoc}
	 */
	public function type() {
		return 'sort';
	}

	/**
	 * {@inheritDoc}
	 */
	public function multiple() {
		return TRUE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function collect() {
		$this->collectLegacy();
	}

	/**
	 * Gather items registered with the old approach.
	 */
	public function collectLegacy() {
		$legacy = apply_filters( 'qw_sort_options', [] );
		foreach ($legacy as $type => $item) {
			$instance = new LegacySort( $type, $item );
			$instance->setInvoker( $this->invoker );
			$instance->setRenderer( $this->renderer );
			$this->set( $instance->type(), $instance );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getDataFromQuery( QwQuery $query ) {
		return $query->getSorts();
	}
}
