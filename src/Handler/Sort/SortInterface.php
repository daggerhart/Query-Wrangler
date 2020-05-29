<?php

namespace QueryWrangler\Handler\Sort;

use QueryWrangler\Handler\HandlerItemTypeInterface;

interface SortInterface extends HandlerItemTypeInterface {

	/**
	 * WP_Query argument key equivalent to WP_Query's 'orderby'.
	 *
	 * @return string
	 */
	public function orderByKey();

	/**
	 * WP_Query argument key equivalent to WP_Query's 'order'
	 *
	 * @return string
	 */
	public function orderKey();

	/**
	 * Order options provided in a select menu.
	 *
	 * @return array
	 */
	public function orderOptions();

	/**
	 * Modify the WP_Query args array.
	 *
	 * @param array $query_args
	 * @param array $sort_settings
	 *
	 * @return array
	 */
	public function process( array $query_args, array $sort_settings );

	/**
	 * HTML form output for the administration configuration of this sort.
	 *
	 * @param array $sort
	 *
	 * @return string
	 */
	public function settingsForm( array $sort );

}
