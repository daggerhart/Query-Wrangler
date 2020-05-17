<?php

namespace QueryWrangler\Handler\Display;

use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\Query\QwQuery;

class DisplayTypeManager extends HandlerTypeManagerBase {

	/**
	 * {@inheritDoc}
	 * @return DisplayInterface
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'display';
	}

	/**
	 * {@inheritDoc}
	 */
	public function multiple() {
		return FALSE;
	}

	/**
	 * @inheritDoc
	 */
	public function collect() {
		$this->collectLegacy();
	}

	/**
	 * Gather items registered with the old approach.
	 */
	public function collectLegacy() {
		$legacy = apply_filters( 'qw_basics', [] );
		foreach ($legacy as $type => $item) {
			if ( $item['option_type'] != 'display' ) {
				continue;
			}
			$instance = new LegacyDisplay( $type, $item );
			$instance->setInvoker( $this->invoker );
			$instance->setRenderer( $this->renderer );
			$this->set( $instance->type(), $instance );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getDataFromQuery( QwQuery $query ) {
		return $query->getDisplay();
	}
}
