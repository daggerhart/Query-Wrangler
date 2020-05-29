<?php

namespace QueryWrangler\Handler\Filter;

use QueryWrangler\Handler\HandlerItemTypeInterface;

interface FilterInterface extends HandlerItemTypeInterface {

	/**
	 * Types of QW Query displays where this filter can be implemented.
	 *
	 * @return array
	 */
	public function displayTypes();

	/**
	 * Whether or not this filter can be exposed for public input.
	 *
	 * @return bool
	 */
	public function exposable();

	/**
	 * Modify the WP_Query args array.
	 *
	 * @param array $query_args
	 * @param array $filter_settings
	 *
	 * @return array
	 */
	public function process( array $query_args, array $filter_settings );

	/**
	 * HTML form output for the administration configuration of this filter.
	 *
	 * @param array $filter_settings
	 *
	 * @return string
	 */
	public function settingsForm( array $filter_settings );

}
