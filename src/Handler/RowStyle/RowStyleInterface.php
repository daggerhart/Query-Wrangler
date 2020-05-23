<?php

namespace QueryWrangler\Handler\RowStyle;

use Kinglet\Entity\QueryInterface;
use Kinglet\Template\RendererInterface;
use QueryWrangler\Handler\HandlerItemTypeInterface;
use QueryWrangler\Handler\HandlerTypeManagerInterface;
use QueryWrangler\Query\QwQuery;

interface RowStyleInterface extends HandlerItemTypeInterface {

	/**
	 * Attach the file rendering service to the Row Style.
	 *
	 * @param RendererInterface $renderer
	 */
	public function setFileRenderer( RendererInterface $renderer );

	/**
	 * Attach the string rendering service to the Row Style.
	 *
	 * @param RendererInterface $renderer
	 */
	public function setStringRenderer( RendererInterface $renderer );

	/**
	 * Attach the callable rendering service to the Row Style.
	 *
	 * @param RendererInterface $renderer
	 */
	public function setCallableRenderer( RendererInterface $renderer );

	/**
	 * Entry point into the rendering of a query row for the row style.
	 *
	 * @param QwQuery $qw_query
	 *   Type entity. Contains all the configuration for the WordPress query.
	 * @param QueryInterface $entity_query
	 *   Query entity. Performs the WordPress query.
	 * @param HandlerTypeManagerInterface $field_type_manager
	 *
	 * @return array
	 */
	public function render(  QwQuery $qw_query, QueryInterface $entity_query, HandlerTypeManagerInterface $field_type_manager );

}
