<?php

namespace QueryWrangler\Handler\Sort;

use QueryWrangler\Handler\HandlerManagerBase;

class SortManager extends HandlerManagerBase {

	/**
	 * {@inheritDoc}
	 */
	public function type() {
		return 'sort';
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
		foreach ($legacy as $type => $sort) {
			$item = new LegacySort( $type, $sort );
			$item->setInvoker( $this->invoker );
			$item->setRenderer( $this->renderer );
			$this->set( $type, $item );
		}
	}
}
