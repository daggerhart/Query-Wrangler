<?php

namespace QueryWrangler\Query;

use Kinglet\Container\ContainerInjectionInterface;
use Kinglet\Container\ContainerInterface;
use Kinglet\Entity\QueryInterface;
use Kinglet\Registry\ClassRegistryInterface;
use QueryWrangler\Handler\Field\FieldTypeManager;
use QueryWrangler\Handler\HandlerManager;
use QueryWrangler\Handler\RowStyle\RowStyleTypeManager;

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

		/**
		 * Paging Type does not allow for multiple instances of its items.
		 * Loop through _all_ paging types because any of them could affect the query args.
		 */
		$paging_manager = $this->handlerManager->get( 'paging' );
		$paging_manager->collect();
		$paging_data = $paging_manager->getDataFromQuery( $query );
		foreach ( $paging_manager->all() as $type => $paging_type ) {
			$args = $paging_type->process( $args, $paging_data );
		}

		/**
		 * Filter Type allow for multiple instances of its items.
		 * Loop through existing filters on the query.
		 *
		 * @todo - exposed forms processing
		 */
		$filter_manager = $this->handlerManager->get( 'filter' );
		$filter_manager->collect();
		foreach ( $filter_manager->getDataFromQuery( $query ) as $name => $item ) {
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
		foreach ( $sort_manager->getDataFromQuery( $query ) as $name => $item ) {
			if ( $sort_manager->has( $item['type'] ) ) {
				$sort_type = $sort_manager->get( $item['type'] );
				$args = $sort_type->process( $args, $item );
			}
		}

		/** @var QueryInterface $entity_query */
		$entity_query = $this->entityQueryManager->getInstance( $query->queryType() );
		$entity_query->setArguments( $args );

		// @todo - allow display types to affect rendering on their own.
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
		$row_style = $row_style_manager->getDataFromQuery( $query );
		$row_style_type = $row_style_manager->get( $row_style );
		$rows = $row_style_type->render( $query, $entity_query, $field_manager );

		// @todo - display manager handles simple stuff, but ultimately...

		// @todo
		//   - the TEMPLATE STYLE then renders into a template...
		//     it does this with the FileRenderer and creates template suggestions
		//     to render the $content, then passes it to...
		//   - the PAGER STYLE renders the desired pager using the results of the query
		//   - the WRAPPER STYLE, which converts everything into variables for the wrapper template

		/**
		 * Display Type does not allow for multiple instances of its items.
		 * Loop through _all_ paging types because any of them could affect the query args.
		 */
		$display_manager = $this->handlerManager->get( 'display' );
		$display_manager->collect();
		$display = [];
		foreach ( $display_manager->all() as $type => $display_type ) {
			$display = $display_type->process( $display, $options['data']['display'] );
		}
//		dump($display_manager->all());
//		dump($display_manager->getDataFromQuery( $query ));

		dump([
			'args' => $args,
			'display' => $display,
			'rows' => $rows,
		]);

		// return
		return '';//$themed;
	}

	public function preprocessQueryData( $data ) {
		return $data;
	}

}
