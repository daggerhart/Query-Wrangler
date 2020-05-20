<?php

namespace QueryWrangler\Handler\RowStyle;

use QueryWrangler\Handler\HandlerItemTypeDiscoverableRegistry;
use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\Query\QwQuery;

class RowStyleTypeManager extends HandlerTypeManagerBase {

	/**
	 * @var bool
	 */
	protected $typesRegistered = FALSE;

	/**
	 * {@inheritDoc}
	 * @return RowStyleInterface
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * {@inheritDoc}
	 */
	public function type() {
		return 'row_style';
	}

	/**
	 * {@inheritDoc}
	 */
	public function multiple() {
		return FALSE;
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
	public function collectTypes() {
		if ( !$this->typesRegistered ) {
			$this->typesRegistered = TRUE;
			add_filter( "qw_handler_item_types--{$this->type()}", function( $sources ) {
				$sources['QueryWrangler\Handler\RowStyle\ItemType'] = QW_PLUGIN_DIR . '/src/Handler/RowStyle/ItemType';
				return $sources;
			} );
		}

		$items = new HandlerItemTypeDiscoverableRegistry(
			'QueryWrangler\Handler\RowStyle\RowStyleInterface',
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
		return $query->getRowStyle();
	}

}