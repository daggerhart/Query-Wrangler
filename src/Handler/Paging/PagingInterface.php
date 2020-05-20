<?php

namespace QueryWrangler\Handler\Paging;

use QueryWrangler\Handler\HandlerItemTypeInterface;

interface PagingInterface extends HandlerItemTypeInterface {

	/**
	 * Modify the WP_Query args array.
	 *
	 * @param array $args
	 * @param array $values
	 *
	 * @return array
	 */
	public function process( array $args, array $values );

	/**
	 * HTML form output for the administration configuration of this sort.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function settingsForm( array $item );

}
