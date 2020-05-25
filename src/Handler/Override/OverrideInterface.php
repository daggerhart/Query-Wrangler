<?php

namespace QueryWrangler\Handler\Override;

use QueryWrangler\Handler\HandlerItemTypeInterface;
use QueryWrangler\QueryPostEntity;
use WP_Query;

interface OverrideInterface extends HandlerItemTypeInterface {

	/**
	 * Determine which qw_query should take affect given the WP_Query context.
	 * _Resolve_ the qw_query and return its QueryPostEntity.
	 * Return false if this override should not take affect.
	 *
	 * @param WP_Query $wp_query
	 *
	 * @return false|QueryPostEntity
	 */
	public function getOverride( WP_Query $wp_query );

	/**
	 * Modify the given WP_Query so that the resolved query entity takes over
	 * the page.
	 *
	 * @param WP_Query $wp_query
	 * @param QueryPostEntity $entity
	 */
	public function doOverride( WP_Query $wp_query, QueryPostEntity $entity );

}
