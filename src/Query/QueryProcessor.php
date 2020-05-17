<?php

namespace QueryWrangler\Query;

use Kinglet\Container\ContainerInjectionInterface;
use Kinglet\Container\ContainerInterface;
use Kinglet\Entity\QueryInterface;
use Kinglet\Entity\TypeInterface;
use Kinglet\Registry\RegistryClassInterface;
use QueryWrangler\Handler\HandlerManager;

class QueryProcessor implements ContainerInjectionInterface {

	/**
	 * @var HandlerManager
	 */
	protected $handlerManager;

	/**
	 * @var RegistryClassInterface
	 */
	protected $entityTypeManager;

	/**
	 * @var RegistryClassInterface
	 */
	protected $entityQueryManager;

	/**
	 * QueryProcessor constructor.
	 *
	 * @param HandlerManager $handler_manager
	 * @param RegistryClassInterface $entity_type_manager
	 * @param RegistryClassInterface $entity_query_manager
	 */
	public function __construct( HandlerManager $handler_manager, RegistryClassInterface $entity_type_manager, RegistryClassInterface $entity_query_manager ) {
		$this->handlerManager = $handler_manager;
		$this->entityTypeManager = $entity_type_manager;
		$this->entityQueryManager = $entity_query_manager;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function create( ContainerInterface $container ) {
		return new static(
			$container->get( 'handler.manager' ),
			$container->get( 'entity.type.manager' ),
			$container->get( 'entity.query.manager')
		);
	}

	/**
	 * @param QwQuery $query
	 * @param array $overrides
	 * @param bool $full_override
	 *
	 * @return false|string
	 */
	public function execute( QwQuery $query, $overrides = [], $full_override = FALSE ) {
		/**
		 * Process options.
		 * @todo - consider new data struct
		 *
		 * Previously @see qw_generate_query_options()
		 */
		$options = $query->meta( 'query_data' );
		$options = $full_override ? $overrides : array_replace_recursive( (array) $options, $overrides );
		// build query_details
		$options['meta'] = array_replace( [
			'id' => $query->id(),
			'slug' => $query->slug(),
			'name' => $query->title(),
			'type' => $query->displayType(),
			'pagination' => isset( $options['display']['page']['pager']['active'] ) ? 1 : 0,
			'header' => $options['display']['header'],
			'footer' => $options['display']['footer'],
			'empty' => $options['display']['empty'],
		], (array) $options['meta'] );

		/**
		 * Generate WP_Query args.
		 *
		 * Previously @see qw_generate_query_args()
		 */
		$args = [];
		$filter_manager = $this->handlerManager->get('filter');
		$filter_manager->collect();
		foreach ( $filter_manager->getDataFromQuery( $query ) as $name => $item ) {
			if ( $filter_manager->has( $item['type'] ) ) {
				$filter_type = $filter_manager->get( $item['type'] );
				$args = $filter_type->process( $args, $item );
			}
		}

		$sort_manager = $this->handlerManager->get('sort');
		$sort_manager->collect();
		foreach ( $sort_manager->getDataFromQuery( $query ) as $name => $item ) {
			if ( $sort_manager->has( $item['type'] ) ) {
				$sort_type = $sort_manager->get( $item['type'] );
				$args = $sort_type->process( $args, $item );
			}
		}
		// @todo - exposed forms look
		// @todo - pagination special handling

		/**
		 * Perform the query.
		 */
		/** @var QueryInterface $entity_query */
		try {
			$entity_query = $this->entityQueryManager->getInstance( $query->queryType(), $args );
		}
		catch ( \ReflectionException $exception ) {
			return "<!-- Query Wrangler Error: Could not find Entity Query of type {$query->queryType()}";
		}

		// theme output
		// return
		ob_start();
		$entity_query->execute( function( $item ) {
			/** @var TypeInterface $item */
			echo "{$item->id()} - ".get_the_title()."<hr>";
		} );
		$themed = ob_get_clean();
		return $themed;
	}

	public function preprocessQueryData( $data ) {
		return $data;
	}

}
