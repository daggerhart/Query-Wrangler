<?php

namespace QueryWrangler\Handler\PagerStyle;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Handler\HandlerItemTypeInterface;
use QueryWrangler\QueryPostEntity;

interface PagerStyleInterface extends HandlerItemTypeInterface {

	/**
	 * Render the output for the pager style.
	 *
	 * @param QueryPostEntity $query_post_entity
	 * @param QueryInterface $entity_query
	 *   Executed entity query.
	 * @param array $settings
	 *   Pager settings array.
	 * @param int $page_number
	 *   Current page number.
	 *
	 * @return string
	 */
	public function render( QueryPostEntity $query_post_entity, QueryInterface $entity_query, array $settings, int $page_number );

}
