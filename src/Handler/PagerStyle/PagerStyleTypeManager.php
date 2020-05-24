<?php

namespace QueryWrangler\Handler\PagerStyle;

use QueryWrangler\Handler\HandlerItemTypeDiscoverableRegistry;
use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\Query\QueryPostEntity;

class PagerStyleTypeManager extends HandlerTypeManagerBase {

	/**
	 * @var bool
	 */
	protected $typesRegistered = FALSE;

	/**
	 * {@inheritDoc}
	 * @return PagerStyleInterface
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * {@inheritDoc}
	 */
	public function type() {
		return 'pager_style';
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
	protected function collectTypes() {
		if ( !$this->typesRegistered ) {
			$this->typesRegistered = TRUE;
			add_filter( "qw_handler_item_types--{$this->type()}", function( $sources ) {
				$sources['QueryWrangler\Handler\PagerStyle\ItemType'] = QW_PLUGIN_DIR . '/src/Handler/PagerStyle/ItemType';
				return $sources;
			} );
		}

		$items = new HandlerItemTypeDiscoverableRegistry(
			'QueryWrangler\Handler\PagerStyle\PagerStyleInterface',
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
	public function getDataFromQuery( QueryPostEntity $query ) {
		return $query->getPagerStyle();
	}
}
