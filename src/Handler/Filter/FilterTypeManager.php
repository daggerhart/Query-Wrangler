<?php

namespace QueryWrangler\Handler\Filter;

use QueryWrangler\Handler\Field\LegacyField;
use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\Query\QwQuery;

class FilterTypeManager extends HandlerTypeManagerBase {

	/**
	 * {@inheritDoc}
	 * @return FilterInterface
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * {@inheritDoc}
	 */
	public function type() {
		return 'filter';
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
		$legacy = apply_filters( 'qw_filters', [] );
		foreach ($legacy as $type => $item) {
			$instance = new LegacyFilter( $type, $item );
			$instance->setInvoker( $this->invoker );
			$instance->setRenderer( $this->renderer );
			$this->set( $instance->type(), $instance );
		}

		$legacy = apply_filters( 'qw_basics', [] );
		foreach ($legacy as $type => $item) {
			if ( $item['option_type'] != 'args' ) {
				continue;
			}
			$instance = new LegacyField( $type, $item );
			$instance->setInvoker( $this->invoker );
			$instance->setRenderer( $this->renderer );
			$this->set( $instance->type(), $instance );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getDataFromQuery( QwQuery $query ) {
		return $query->getFilters();
	}

}
