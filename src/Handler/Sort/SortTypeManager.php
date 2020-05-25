<?php

namespace QueryWrangler\Handler\Sort;

use QueryWrangler\Handler\HandlerItemTypeDiscoverableRegistry;
use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\QueryPostEntity;

class SortTypeManager extends HandlerTypeManagerBase {

	/**
	 * @var bool
	 */
	protected $typesRegistered = FALSE;

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
	 * @inheritDoc
	 */
	public function getDataFromQuery( QueryPostEntity $query ) {
		return $query->getSorts();
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
	protected function collectLegacy() {
		$legacy = apply_filters( 'qw_sort_options', [] );
		foreach ($legacy as $type => $item) {
			$instance = new LegacySort( $type, $item );
			$instance->setInvoker( $this->invoker );
			$instance->setRenderer( $this->callableRenderer );
			$this->set( $instance->type(), $instance );
		}
	}

	/**
	 * Collect all new item types for this handler type.
	 */
	protected function collectTypes() {
		if ( !$this->typesRegistered ) {
			$this->typesRegistered = TRUE;
			add_filter( "qw_handler_item_types--{$this->type()}", function( $sources ) {
				$sources['QueryWrangler\Handler\Sort\ItemType'] = QW_PLUGIN_DIR . '/src/Handler/Sort/ItemType';
				return $sources;
			} );
		}

		$items = new HandlerItemTypeDiscoverableRegistry(
			'QueryWrangler\Handler\Sort\SortInterface',
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

}
