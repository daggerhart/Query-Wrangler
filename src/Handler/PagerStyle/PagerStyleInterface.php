<?php

namespace QueryWrangler\Handler\PagerStyle;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Handler\HandlerItemTypeInterface;

interface PagerStyleInterface extends HandlerItemTypeInterface {

	/**
	 * Render the output for the pager style.
	 *
	 * @param array $settings
	 *   Pager settings array.
	 * @param QueryInterface $query
	 *   Executed entity query.
	 * @param int $page_number
	 *   Current page number.
	 *
	 * @return string
	 */
	public function render( array $settings, QueryInterface $query, int $page_number );

}
