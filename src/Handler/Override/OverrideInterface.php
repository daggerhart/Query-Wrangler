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
	public function findOverrideEntity( WP_Query $wp_query );

	/**
	 * Modify the given WP_Query so that the resolved query entity takes over
	 * the page.
	 *
	 * @param WP_Query $wp_query
	 * @param OverrideContextInterface $override_context
	 */
	public function overrideWPQuery( WP_Query $wp_query, OverrideContextInterface $override_context );

	/**
	 * Modify the given entity with values from
	 *
	 * @param QueryPostEntity $entity
	 * @param OverrideContextInterface $override_context
	 */
	public function overrideEntity( QueryPostEntity $entity, OverrideContextInterface $override_context );

}
