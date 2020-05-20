<?php

namespace QueryWrangler\Handler\RowStyle;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Handler\HandlerTypeManagerInterface;
use QueryWrangler\Query\QwQuery;

interface RowStyleInterface {

	/**
	 * @return string
	 */
	public function type();

	/**
	 * @return string
	 */
	public function title();

	/**
	 * @return string
	 */
	public function description();

	/**
	 * @return string
	 */
	public function settingsForm();

	/**
	 * Entry point into the rendering of a query row for the row style.
	 *
	 * @param QwQuery $qw_query
	 *   Type entity. Contains all the configuration for the WordPress query.
	 * @param QueryInterface $entity_query
	 *   Query entity. Performs the WordPress query.
	 * @param HandlerTypeManagerInterface $field_type_manager
	 *
	 * @return string
	 */
	public function render( QwQuery $qw_query, QueryInterface $entity_query, HandlerTypeManagerInterface $field_type_manager );

}
