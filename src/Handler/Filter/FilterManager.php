<?php

namespace QueryWrangler\Handler\Filter;

use QueryWrangler\Handler\HandlerManagerBase;

class FilterManager extends HandlerManagerBase {

	/**
	 * {@inheritDoc}
	 */
	public function type() {
		return 'filter';
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
		$legacy = apply_filters( 'qw_filters', [] );
		foreach ($legacy as $type => $filter) {
			$item = new LegacyFilter( $type, $filter );
			$item->setInvoker( $this->invoker );
			$item->setRenderer( $this->renderer );
			$this->set( $type, $item );
		}
	}
}
