<?php

namespace QueryWrangler\Handler\Override;

use QueryWrangler\Handler\HandlerItemTypeDiscoverableRegistry;
use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\QueryPostEntity;

class OverrideTypeManager extends HandlerTypeManagerBase {

	/**
	 * @var bool
	 */
	protected $typesRegistered = FALSE;

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'override';
	}

	/**
	 * @inheritDoc
	 */
	public function multiple() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getDataFromQuery( QueryPostEntity $query ) {
		return $query->getOverrides();
	}

	/**
	 * {@inheritDoc}
	 */
	public function collect() {
		$this->collectTypes();
	}

	/**
	 * Collect all new item types for this handler type.
	 */
	protected function collectTypes() {
		if ( !$this->typesRegistered ) {
			$this->typesRegistered = TRUE;
			add_filter( "qw_handler_item_types--{$this->type()}", function( $sources ) {
				$sources['QueryWrangler\Handler\Override\ItemType'] = QW_PLUGIN_DIR . '/src/Handler/Override/ItemType';
				return $sources;
			} );
		}

		$items = new HandlerItemTypeDiscoverableRegistry(
			'QueryWrangler\Handler\Override\OverrideInterface',
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
