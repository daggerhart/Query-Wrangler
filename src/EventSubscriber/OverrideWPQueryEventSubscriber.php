<?php

namespace QueryWrangler\EventSubscriber;

use Kinglet\Container\ContainerInterface;
use QueryWrangler\Handler\Override\OverrideInterface;
use QueryWrangler\Handler\Override\OverrideTypeManager;
use WP_Query;

class OverrideWPQueryEventSubscriber {

	/**
	 * @var OverrideTypeManager
	 */
	protected $overrideTypeManager;

	/**
	 * WPQueryEventSubscriber constructor.
	 *
	 * @param OverrideTypeManager $override_type_manager
	 */
	private function __construct( OverrideTypeManager $override_type_manager ) {
		$this->overrideTypeManager = $override_type_manager;

		add_action( 'parse_query', [ $this, 'findOverride' ] );
		add_action( 'pre_get_posts', [ $this, 'executeOverride' ], -1000 );
	}

	/**
	 * @param ContainerInterface $container
	 */
	public static function subscribe( ContainerInterface $container ) {
		new static(
			$container->get( 'handler.override.manager' )
		);
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

		$this->overrideTypeManager->collect();
		$wp_query->query_wrangler_override_type = false;
		$wp_query->query_wrangler_override_entity = false;
		/**
		 * @var string $type
		 * @var OverrideInterface $override_type
		 */
		foreach ( $this->overrideTypeManager->all() as $type => $override_type ) {
			if ( !in_array( 'post', $override_type->queryTypes() ) ) {
				continue;
			}
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
