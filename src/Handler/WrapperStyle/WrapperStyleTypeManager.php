<?php

namespace QueryWrangler\Handler\WrapperStyle;

use QueryWrangler\Handler\HandlerItemTypeDiscoverableRegistry;
use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\Query\QwQuery;

class WrapperStyleTypeManager extends HandlerTypeManagerBase {

	/**
	 * @var bool
	 */
	protected $typesRegistered = FALSE;

	/**
	 * {@inheritDoc}
	 * @return WrapperStyleInterface
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * {@inheritDoc}
	 */
	public function type() {
		return 'wrapper_style';
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
		$this->collectLegacy();
		$this->collectTypes();
	}

	/**
	 * Create the legacy wrapper style.
	 */
	protected function collectLegacy() {
		$instance = new LegacyWrapperStyle();
		$instance->setFileRenderer( $this->fileRenderer );
		$this->set( $instance->type(), $instance );
	}

	/**
	 * Collect all new item types for this handler type.
	 */
	protected function collectTypes() {
		if ( !$this->typesRegistered ) {
			$this->typesRegistered = TRUE;
			add_filter( "qw_handler_item_types--{$this->type()}", function( $sources ) {
				$sources['QueryWrangler\Handler\WrapperStyle\ItemType'] = QW_PLUGIN_DIR . '/src/Handler/WrapperStyle/ItemType';
				return $sources;
			} );
		}

		$items = new HandlerItemTypeDiscoverableRegistry(
			'QueryWrangler\Handler\WrapperStyle\WrapperStyleInterface',
			'type',
			"qw_handler_item_types--{$this->type()}"
		);

		foreach ( $items->all() as $type => $item ) {
			try {
				/** @var WrapperStyleInterface $instance */
				$instance = $items->getInstance( $type );
				$instance->setFileRenderer( $this->fileRenderer );
				$this->set( $instance->type(), $instance );
			}
			catch ( \ReflectionException $exception ) {}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getDataFromQuery( QwQuery $query ) {
		return $query->getWrapperStyle();
	}

}
