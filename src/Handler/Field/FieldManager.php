<?php

namespace QueryWrangler\Handler\Field;

use QueryWrangler\Handler\HandlerManagerBase;

class FieldManager extends HandlerManagerBase {

	/**
	 * {@inheritDoc}
	 */
	public function type() {
		return 'field';
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
		foreach ($legacy as $type => $field) {
			$item = new LegacyField( $type, $field );
			$item->setInvoker( $this->invoker );
			$item->setRenderer( $this->renderer );
			$this->set( $type, $item );
		}
	}
}
