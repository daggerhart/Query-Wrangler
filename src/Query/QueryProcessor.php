<?php

namespace QueryWrangler\Query;

use Kinglet\Container\ContainerInjectionInterface;
use Kinglet\Container\ContainerInterface;
use Kinglet\Entity\QueryInterface;
use Kinglet\Registry\ClassRegistryInterface;
use QueryWrangler\Handler\Field\FieldTypeManager;
use QueryWrangler\Handler\HandlerManager;
use QueryWrangler\Handler\PagerStyle\PagerStyleTypeManager;
use QueryWrangler\Handler\RowStyle\RowStyleTypeManager;
use QueryWrangler\Handler\TemplateStyle\TemplateStyleTypeManager;

class QueryProcessor implements ContainerInjectionInterface {

	/**
	 * @var HandlerManager
	 */
	protected $handlerManager;

	/**
	 * @var ClassRegistryInterface
	 */
	protected $entityTypeManager;

	/**
	 * @var ClassRegistryInterface
	 */
	protected $entityQueryManager;

	/**
	 * QueryProcessor constructor.
	 *
	 * @param HandlerManager $handler_manager
	 * @param ClassRegistryInterface $entity_type_manager
	 * @param ClassRegistryInterface $entity_query_manager
	 */
	public function __construct( HandlerManager $handler_manager, ClassRegistryInterface $entity_type_manager, ClassRegistryInterface $entity_query_manager ) {
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
	 * @param QwQuery $qw_query
	 * @param array $overrides
	 * @param bool $full_override
	 *
	 * @return false|string
	 */
	public function execute( QwQuery $qw_query, $overrides = [], $full_override = FALSE ) {
		/**
		 * Process options.
		 * @todo - consider new data struct
		 *
		 * Previously @see qw_generate_query_options()
		 */
		$options = $qw_query->meta( 'query_data' );
		$options = $full_override ? $overrides : array_replace_recursive( (array) $options, $overrides );

		// build query_details
		$options['meta'] = array_replace( [
			'id' => $qw_query->id(),
			'slug' => $qw_query->slug(),
			'name' => $qw_query->title(),
			'type' => $qw_query->getDisplayType(),
			'pagination' => isset( $options['display']['page']['pager']['active'] ) ? 1 : 0,
			'header' => $options['display']['header'],
			'footer' => $options['display']['footer'],
			'empty' => $options['display']['empty'],
		], (array) $options['meta'] );

		$wrapper = [
			'rows' => [],
			'content' => null,
			'pager' => null,
		];
		$current_page_number = $this->getCurrentPageNumber();

		/**
		 * Generate WP_Query args.
		 *
		 * Previously @see qw_generate_query_args()
		 */
		$args = [];

		/**
		 * Paging Type does not allow for multiple instances of its items.
		 * Loop through _all_ paging types because any of them could affect the query args.
		 */
		$paging_manager = $this->handlerManager->get( 'paging' );
		$paging_manager->collect();
		$paging_data = $paging_manager->getDataFromQuery( $qw_query );
		foreach ( $paging_manager->all() as $type => $paging_type ) {
			$args = $paging_type->process( $args, $paging_data, $current_page_number );
		}

		/**
		 * Filter Type allow for multiple instances of its items.
		 * Loop through existing filters on the query.
		 *
		 * @todo - exposed forms processing
		 */
		$filter_manager = $this->handlerManager->get( 'filter' );
		$filter_manager->collect();
		foreach ( $filter_manager->getDataFromQuery( $qw_query ) as $name => $item ) {
			if ( $filter_manager->has( $item['type'] ) ) {
				$filter_type = $filter_manager->get( $item['type'] );
				$args = $filter_type->process( $args, $item );
			}
		}

		/**
		 * Sort Type allow for multiple instances of its items.
		 * Loop through existing filters on the query.
		 *
		 * @todo - exposed forms processing
		 */
		$sort_manager = $this->handlerManager->get( 'sort' );
		$sort_manager->collect();
		foreach ( $sort_manager->getDataFromQuery( $qw_query ) as $name => $item ) {
			if ( $sort_manager->has( $item['type'] ) ) {
				$sort_type = $sort_manager->get( $item['type'] );
				$args = $sort_type->process( $args, $item );
			}
		}

		/** @var QueryInterface $entity_query */
		$entity_query = $this->entityQueryManager->getInstance( $qw_query->getQueryType() );
		$entity_query->setArguments( $args );

		/**
		 * Display handlers render and modify specific parts of the output.
		 * Some have their own sub-display implementations (like row styles).
		 *
		 * - Wrapper
		 *   - Header
		 *   - Empty
		 *   - Pager Style
		 *   - Template Style : Renders rows and row wrapping element. (table|unformatted|list)
	     *     - Row Style[]: EXECUTES QUERY - Collection of rendered Fields.
		 *       - Fields[]
		 *         Fields have settings which affect its own output
		 *   - Footer
		 */
		/** @var FieldTypeManager $field_manager */
		$field_manager = $this->handlerManager->get( 'field' );
		$field_manager->collect();

		/** @var RowStyleTypeManager $row_style_manager */
		$row_style_manager = $this->handlerManager->get( 'row_style' );
		$row_style_manager->collect();
		$row_style = $row_style_manager->getDataFromQuery( $qw_query );
		$row_style_type = $row_style_manager->get( $row_style['type'] );
		$wrapper['rows'] = $row_style_type->render( $qw_query, $entity_query, $field_manager );

		if ( $qw_query->getPagerEnabled() ) {
			$query_page_number = $this->getQueryPageNumber( $entity_query );

			/** @var PagerStyleTypeManager $pager_style_manager */
			$pager_style_manager = $this->handlerManager->get( 'pager_style' );
			$pager_style_manager->collect();
			$pager_style = $pager_style_manager->getDataFromQuery( $qw_query );
			$pager_style_type = $pager_style_manager->get( $pager_style['type'] );
			$wrapper['pager'] = $pager_style_type->render( $pager_style, $entity_query, $query_page_number );
		}

		if ( is_array( $wrapper['rows'] ) && count( $wrapper['rows'] ) ) {
			/** @var TemplateStyleTypeManager $template_style_manager */
			$template_style_manager = $this->handlerManager->get( 'template_style' );
			$template_style_manager->collect();
			$template_style = $template_style_manager->getDataFromQuery( $qw_query );
			$template_style_type = $template_style_manager->get( $template_style['type'] );
			$wrapper['content'] = $template_style_type->render( $qw_query, $wrapper['rows'] );
		}

		// @todo - display manager handles simple stuff, but ultimately...

		// @todo
		//   - the WRAPPER STYLE, which converts everything into variables for the wrapper template

		/**
		 * Display Type does not allow for multiple instances of its items.
		 * Loop through _all_ paging types because any of them could affect the query args.
		 */
		$display_manager = $this->handlerManager->get( 'display' );
		$display_manager->collect();
		$display = [];
		foreach ( $display_manager->all() as $type => $display_type ) {
			$display = $display_type->process( $display, $qw_query->getDisplay() );
		}
//		dump($display_manager->all());
//		dump($display_manager->getDataFromQuery( $query ));

		$dump = $wrapper + [
			'args' => $args,
			'display' => $display,
			'qw_query' => $qw_query,
		];
		unset($dump['content']);
		dump( $dump );

		// return
		return $wrapper['content'];
	}

	public function preprocessQueryData( $data ) {
		return $data;
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
