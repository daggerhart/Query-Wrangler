<?php

namespace QueryWrangler\Handler\Display;

use QueryWrangler\Handler\HandlerItemTypeInterface;

interface DisplayInterface extends HandlerItemTypeInterface {

	/**
	 * The numeric order of where this item appears in the administration form.
	 *
	 * @return int
	 */
	public function order();

	/**
	 * @param array $display
	 *   The compiled query display options for further processing by this display type item.
	 * @param array $query_data
	 *   Full set of query data. Useful for display types that had data stored all over the
	 *   place in legacy QW.
	 *
	 * @return array
	 */
	public function process( array $display, array $query_data = [] );

	/**
	 * HTML form output for the administration configuration of this sort.
	 *
	 * @param array $display
	 * @param array $values
	 *
	 * @return string
	 */
	public function settingsForm( array $display, array $values );

}
