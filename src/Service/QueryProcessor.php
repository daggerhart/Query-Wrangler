<?php

namespace QueryWrangler\Service;

use Kinglet\Container\ContainerInjectionInterface;
use Kinglet\Container\ContainerInterface;
use Kinglet\Entity\QueryInterface;
use Kinglet\Registry\ClassRegistryInterface;
use QueryWrangler\Handler\Field\FieldTypeManager;
use QueryWrangler\Handler\Filter\FilterTypeManager;
use QueryWrangler\Handler\HandlerManager;
use QueryWrangler\Handler\Override\OverrideContextInterface;
use QueryWrangler\Handler\Override\OverrideInterface;
use QueryWrangler\Handler\Override\OverrideTypeManager;
use QueryWrangler\Handler\PagerStyle\PagerStyleTypeManager;
use QueryWrangler\Handler\Paging\PagingTypeManager;
use QueryWrangler\Handler\RowStyle\RowStyleTypeManager;
use QueryWrangler\Handler\Sort\SortTypeManager;
use QueryWrangler\Handler\TemplateStyle\TemplateStyleTypeManager;
use QueryWrangler\Handler\WrapperStyle\WrapperStyleTypeManager;
use QueryWrangler\QueryPostEntity;

class QueryProcessor implements ContainerInjectionInterface {

	/**
	 * @var HandlerManager
	 */
	protected $handlerManager;

	/**
	 * @var ClassRegistryInterface
	 */
	protected $entityQueryManager;

	/**
	 * QueryProcessor constructor.
	 *
	 * @param HandlerManager $handler_manager
	 * @param ClassRegistryInterface $entity_query_manager
	 */
	public function __construct( HandlerManager $handler_manager, ClassRegistryInterface $entity_query_manager ) {
		$this->handlerManager = $handler_manager;
		$this->entityQueryManager = $entity_query_manager;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function create( ContainerInterface $container ) {
		return new static(
			$container->get( 'handler.manager' ),
			$container->get( 'entity.query.manager')
		);
	}

	/**
	 * @param QueryPostEntity $query_post_entity
	 * @param array $query_data_overrides
	 * @param bool $full_override
	 *
	 * @return string
	 */
	public function execute( QueryPostEntity $query_post_entity, $query_data_overrides = [], $full_override = FALSE ) {
		/**
		 * Process options.
		 * @todo - consider new data struct
		 *
		 * Previously @see qw_generate_query_options()
		 */
		$query_post_entity->populate( [], $query_data_overrides );

//		// @todo - This doesn't do anything.
//		$options = $query_post_entity->meta( 'query_data' );
//		$options = $full_override ? $overrides : array_replace_recursive( (array) $options, $overrides );
//
//		// build query_details
//		$options['meta'] = array_replace( [
//			'id' => $query_post_entity->id(),
//			'slug' => $query_post_entity->slug(),
//			'name' => $query_post_entity->title(),
//			'type' => $query_post_entity->getDisplayType(),
//			'pagination' => isset( $options['display']['page']['pager']['active'] ) ? 1 : 0,
//			'header' => $options['display']['header'],
//			'footer' => $options['display']['footer'],
//			'empty' => $options['display']['empty'],
//		], (array) $options['meta'] );

		$render_context = $query_post_entity->getRenderContext();
		$render_context->set( 'rows', [] );
		$render_context->set( 'content', '' );
		$render_context->set( 'current_page_number', $this->getCurrentPageNumber() );

		/**
		 * Generate WP_Query args.
		 *
		 * Previously @see qw_generate_query_args()
		 */
		/** @var QueryInterface $entity_query */
		try {
			$entity_query = $this->entityQueryManager->getInstance( $query_post_entity->getQueryType() );
		}
		catch ( \ReflectionException $exception ) {
			return "Query Wrangler ERROR: {$exception->getMessage()}";
		}
		$query_args = [];

		/**
		 * Overrides can alter the query before processing. They do this by
		 * modifying the query entity directly.
		 *
		 * @todo - is there a better approach than accessing the global wp_query here?
		 */
		global $wp_query;
		if ( isset( $wp_query->query_wrangler_override_context ) ) {
			/** @var OverrideContextInterface $override_context */
			$override_context = $wp_query->query_wrangler_override_context;
			$override_type = $override_context->getOverrideType();
			if ( in_array( $entity_query->type(), $override_type->queryTypes() ) ) {
				$override_type->overrideEntity( $query_post_entity, $override_context );
			}
		}

		/**
		 * Paging Type does not allow for multiple instances of its items.
		 * Loop through _all_ paging types because any of them could affect the query args.
		 */
		/** @var PagingTypeManager $paging_manager */
		$paging_manager = $this->handlerManager->get( 'paging' );
		$paging_manager->collect();
		$paging_data = $paging_manager->getDataFromQuery( $query_post_entity );
		foreach ( $paging_manager->all() as $type => $paging_type ) {
			if ( !in_array( $entity_query->type(), $paging_type->queryTypes() ) ) {
				continue;
			}
			$query_args = $paging_type->process( $query_args, $paging_data, $render_context->get( 'current_page_number' ) );
		}

		/**
		 * Filter Type allow for multiple instances of its items.
		 * Loop through existing filters on the query.
		 *
		 * @todo - exposed forms processing
		 */
		/** @var FilterTypeManager $filter_manager */
		$filter_manager = $this->handlerManager->get( 'filter' );
		$filter_manager->collect();
		foreach ( $filter_manager->getDataFromQuery( $query_post_entity ) as $name => $item ) {
			if ( $filter_manager->has( $item['type'] ) ) {
				$filter_type = $filter_manager->get( $item['type'] );
				if ( !in_array( $entity_query->type(), $filter_type->queryTypes() ) ) {
					continue;
				}
				$query_args = $filter_type->process( $query_args, $item );
			}
		}

		/**
		 * Sort Type allow for multiple instances of its items.
		 * Loop through existing filters on the query.
		 */
		/** @var SortTypeManager $sort_manager */
		$sort_manager = $this->handlerManager->get( 'sort' );
		$sort_manager->collect();
		foreach ( $sort_manager->getDataFromQuery( $query_post_entity ) as $name => $item ) {
			if ( $sort_manager->has( $item['type'] ) ) {
				$sort_type = $sort_manager->get( $item['type'] );
				if ( !in_array( $entity_query->type(), $sort_type->queryTypes() ) ) {
					continue;
				}
				$query_args = $sort_type->process( $query_args, $item );
			}
		}

		/**
		 * Query args should be ready.
		 */
		$entity_query->setArguments( $query_args );

		/** @var FieldTypeManager $field_manager */
		$field_manager = $this->handlerManager->get( 'field' );
		$field_manager->collect();

		/** @var RowStyleTypeManager $row_style_manager */
		$row_style_manager = $this->handlerManager->get( 'row_style' );
		$row_style_manager->collect();
		$row_style = $row_style_manager->getDataFromQuery( $query_post_entity );
		$row_style_type = $row_style_manager->get( $row_style['type'] );
		$rows = $row_style_type->render( $query_post_entity, $entity_query, $row_style, $field_manager );
		$render_context->set( 'rows', $rows );

		if ( $query_post_entity->getPagerEnabled() ) {
			$render_context->set( 'query_page_number', $this->getQueryPageNumber( $entity_query ) );

			/** @var PagerStyleTypeManager $pager_style_manager */
			$pager_style_manager = $this->handlerManager->get( 'pager_style' );
			$pager_style_manager->collect();
			$pager_style = $pager_style_manager->getDataFromQuery( $query_post_entity );
			$pager_style_type = $pager_style_manager->get( $pager_style['type'] );
			$render_context->set(
				'pager',
				$pager_style_type->render(
					$query_post_entity,
					$entity_query,
					$pager_style,
					$render_context->get( 'query_page_number' )
				)
			);
		}

		if ( is_array( $rows ) && count( $rows ) ) {
			/** @var TemplateStyleTypeManager $template_style_manager */
			$template_style_manager = $this->handlerManager->get( 'template_style' );
			$template_style_manager->collect();
			$template_style = $template_style_manager->getDataFromQuery( $query_post_entity );
			$template_style_type = $template_style_manager->get( $template_style['type'] );
			$render_context->set(
				'content',
				$template_style_type->render(
					$query_post_entity,
					$entity_query,
					$template_style,
					$render_context->get( 'rows' )
				)
			);
		}

		/** @var WrapperStyleTypeManager $template_style_manager */
		$wrapper_style_manager= $this->handlerManager->get( 'wrapper_style' );
		$wrapper_style_manager->collect();
		$wrapper_style = $wrapper_style_manager->getDataFromQuery( $query_post_entity );
		$wrapper_style_type = $wrapper_style_manager->get( $wrapper_style['type'] );
		$render_context->set(
			'wrapper',
			$wrapper_style_type->render(
				$query_post_entity,
				$entity_query,
				$wrapper_style,
				$render_context->all()
			)
		);

		$dump = [
			'args' => $query_args,
			'qw_query' => $query_post_entity,
			'rendered' => $render_context->all(),
		];
		unset($dump['rendered']['content'], $dump['rendered']['wrapper']);
		dump( $dump );

		return $query_post_entity->getRendered( 'wrapper' );
	}

	/**
	 * Get the current page number based on WordPress context or URL.
	 *
	 * @param array $keys
	 *   Request parameter keys to look for page as page number value.
	 * @return int
	 */
	public function getCurrentPageNumber( $keys = [ 'page', 'paged' ] ) {
		// Default to page 1
		$page = 1;

		// Help figure out the current page.
		$path_array = explode( '/page/', $_SERVER['REQUEST_URI'] );

		// Global WP_Query context.
		if ( get_query_var( 'paged' ) ) {
			$page = get_query_var( 'paged' );
		}
		// Paging with URL.
		else if ( isset( $path_array[1] ) ) {
			$page = explode( '/', $path_array[1] );
			$page = $page[0];
		}
		// Paging with request query parameter.
		else {
			foreach ( $keys as $key ) {
				if ( isset( $_GET[ $key ] ) && is_numeric( $_GET[ $key ] ) ) {
					$page = $_GET[ $key ];
					break;
				}
			}
		}

		return intval( $page );
	}

	/**
	 * Look in query for page number.
	 *
	 * @param QueryInterface $query
	 * @param bool $fallback
	 *   Fallback to the global page number context.
	 *
	 * @return int
	 */
	public function getQueryPageNumber( QueryInterface $query, $fallback = true ) {
		$wp_query = $query->query();
		$page = 1;

		if ( ! is_null( $wp_query ) && isset( $wp_query->query_vars['paged'] ) ) {
			$page = $wp_query->query_vars['paged'];
		}
		// Fallback to global page context.
		else if ( $fallback ) {
			$page = $this->getCurrentPageNumber();
		}

		return intval( $page );
	}

}
