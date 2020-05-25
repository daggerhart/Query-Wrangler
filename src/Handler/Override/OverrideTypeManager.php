<?php

namespace QueryWrangler\Handler\Override;

use QueryWrangler\Handler\HandlerItemTypeDiscoverableRegistry;
use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\QueryPostEntity;
use WP_Query;

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

	/**
	 * Let each override determine if it should take over the current route.
	 *
	 * @param WP_Query $wp_query
	 */
	public function findOverride( WP_Query $wp_query ) {
		if ( !$wp_query->is_main_query() ) {
			return;
		}

		$this->collect();
		$wp_query->query_wrangler_override_type = false;
		$wp_query->query_wrangler_override_entity = false;
		/**
		 * @var string $type
		 * @var OverrideInterface $override_type
		 */
		foreach ( $this->all() as $type => $override_type ) {
			$wp_query->query_wrangler_override_entity = $override_type->getOverride( $wp_query );
			if ( $wp_query->query_wrangler_override_entity ) {
				$wp_query->query_wrangler_override_type = $override_type;
				break;
			}
		}
	}

	/**
	 * Perform an override if one has been found at an earlier stage.
	 *
	 * @param WP_Query $wp_query
	 */
	public function executeOverride( WP_Query $wp_query ) {
		if ( !$wp_query->is_main_query() ) {
			return;
		}

		if ( $wp_query->query_wrangler_override_type && $wp_query->query_wrangler_override_entity ) {
			$wp_query->query_wrangler_override_type->doOverride( $wp_query, $wp_query->query_wrangler_override_entity );
		}
	}

}
