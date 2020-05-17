<?php

namespace QueryWrangler\Handler\Field;

use QueryWrangler\Handler\HandlerTypeManagerBase;

class FieldTypeManager extends HandlerTypeManagerBase {

	/**
	 * {@inheritDoc}
	 */
	public function type() {
		return 'field';
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
		$legacy = apply_filters( 'qw_fields', [] );
		foreach ($legacy as $type => $item) {
			$instance = new LegacyField( $type, $item );
			$instance->setInvoker( $this->invoker );
			$instance->setRenderer( $this->renderer );
			$this->set( $type, $instance );
		}
	}
}
