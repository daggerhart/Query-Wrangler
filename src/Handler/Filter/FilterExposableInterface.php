<?php

namespace QueryWrangler\Handler\Filter;

interface FilterExposableInterface {

	/**
	 * HTML form output for the public filter configuration.
	 *
	 * @param array $filter
	 * @param array $values
	 *
	 * @return string
	 */
	public function exposedForm( array $filter, array $values );

	/**
	 * Modify the values array used to populate the exposed form.
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	public function exposedProcessValues( array $values );

	/**
	 * @param array $args
	 * @param array $filter
	 * @param array $values
	 *
	 * @return array
	 */
	public function exposedProcess( array $args, array $filter, array $values );

	/**
	 * HTML form output for the administration configuration of the exposedForm.
	 *
	 * @param array $filter
	 *
	 * @return string
	 */
	public function exposedSettingsForm( array $filter );

}
