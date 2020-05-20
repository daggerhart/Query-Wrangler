<?php

namespace QueryWrangler\Handler\Filter;

use QueryWrangler\Handler\HandlerItemTypeDiscoverableRegistry;
use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\Query\QwQuery;

class FilterTypeManager extends HandlerTypeManagerBase {

	/**
	 * @var bool
	 */
	protected $typesRegistered = FALSE;

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
		$this->collectTypes();
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
	}

	/**
	 * Collect all new item types for this handler type.
	 */
	public function collectTypes() {
		if ( !$this->typesRegistered ) {
			$this->typesRegistered = TRUE;
			add_filter( "qw_handler_item_types--{$this->type()}", function( $sources ) {
				$sources['QueryWrangler\Handler\Filter\ItemType'] = QW_PLUGIN_DIR . '/src/Handler/Filter/ItemType';
				return $sources;
			} );
		}

		$items = new HandlerItemTypeDiscoverableRegistry(
			'QueryWrangler\Handler\Filter\FilterInterface',
			'type',
			"qw_handler_item_types--{$this->type()}"
		);

		foreach ( $items->all() as $type => $item ) {
			try {
				$instance = $items->getInstance( $type );
				$this->set( $instance->type(), $instance );
			}
			catch ( \ReflectionException $exception ) {}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getDataFromQuery( QwQuery $query ) {
		return $query->getFilters();
	}

}