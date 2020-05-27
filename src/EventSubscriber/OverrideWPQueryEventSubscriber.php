<?php

namespace QueryWrangler\EventSubscriber;

use Kinglet\Container\ContainerInterface;
use QueryWrangler\Handler\Override\OverrideContext;
use QueryWrangler\Handler\Override\OverrideContextInterface;
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

		add_action( 'parse_query', [ $this, 'findOverrideEntity' ], 1000 );
		add_action( 'pre_get_posts', [ $this, 'executeQueryOverride' ], -1000 );
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
	public function findOverrideEntity( WP_Query $wp_query ) {
		if ( !$wp_query->is_main_query() ) {
			return;
		}

		$this->overrideTypeManager->collect();
		/**
		 * @var string $type
		 * @var OverrideInterface $override_type
		 */
		foreach ( $this->overrideTypeManager->all() as $type => $override_type ) {
			if ( !in_array( 'post', $override_type->queryTypes() ) ) {
				continue;
			}

			$entity = $override_type->findOverrideEntity( $wp_query );
			if ( $entity ) {
				$override_context = new OverrideContext();
				$override_context->setFoundEntity( $entity );
				$override_context->setOverrideType( $override_type );
				$override_context->setOriginalQueryVars( $wp_query->query_vars );
				$override_context->setOriginalQueriedObject( $wp_query->get_queried_object() );
				$wp_query->query_wrangler_override_context = $override_context;
				break;
			}
		}
	}

	/**
	 * Perform an override if one has been found at an earlier stage.
	 *
	 * @param WP_Query $wp_query
	 */
	public function executeQueryOverride( WP_Query $wp_query ) {
		if ( !$wp_query->is_main_query() ) {
			return;
		}

		if ( $wp_query->query_wrangler_override_context ) {
			/** @var OverrideContextInterface $override_context */
			$override_context = $wp_query->query_wrangler_override_context;
			/** @var OverrideInterface $override_type */
			$override_type = $override_context->getOverrideType();
			$override_type->overrideWPQuery( $wp_query, $override_context );
		}
	}

}
