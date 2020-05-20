<?php

namespace QueryWrangler\Handler\Display;

use QueryWrangler\Handler\HandlerItemTypeDiscoverableRegistry;
use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\Query\QwQuery;

class DisplayTypeManager extends HandlerTypeManagerBase {

	/**
	 * @var bool
	 */
	protected $typesRegistered = FALSE;

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
		$this->collectTypes();
		//$this->collectLegacy();
	}

	/**
	 * Collect all new item types for this handler type.
	 */
	public function collectTypes() {
		if ( !$this->typesRegistered ) {
			$this->typesRegistered = TRUE;
			add_filter( "qw_handler_item_types--{$this->type()}", function( $sources ) {
				$sources['QueryWrangler\Handler\Display\ItemType'] = QW_PLUGIN_DIR . '/src/Handler/Display/ItemType';
				return $sources;
			} );
		}

		$items = new HandlerItemTypeDiscoverableRegistry(
			'QueryWrangler\Handler\Display\DisplayInterface',
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

			// Legacy items can't replace newer types.
			if ( !$this->has( $instance->type() ) ) {
				$this->set( $instance->type(), $instance );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getDataFromQuery( QwQuery $query ) {
		return $query->getDisplay();
	}
}
