<?php

namespace QueryWrangler\Handler\Filter;

interface FilterExposableInterface {

	/**
	 * Whether or not the filter instance is exposed for user input.
	 *
	 * @param array $filter_settings
	 *
	 * @return bool
	 */
	public function isExposed( array $filter_settings );

	/**
	 * HTML form output for the public filter configuration.
	 *
	 * @param array $filter_settings
	 * @param array $form_values
	 *
	 * @return string
	 */
	public function exposedForm( array $filter_settings, array $form_values );

	/**
	 * Modify the values array used to populate the exposed form.
	 *
	 * @param array $filter_settings
	 *
	 * @return array
	 */
	public function exposedGetSubmittedValues( array $filter_settings );

	/**
	 * @param array $query_args
	 * @param array $filter_settings
	 * @param array $form_values
	 *
	 * @return array
	 */
	public function exposedProcess( array $query_args, array $filter_settings, array $form_values );

	/**
	 * HTML form output for the administration configuration of the exposedForm.
	 *
	 * @param array $filter_settings
	 *
	 * @return string
	 */
	public function exposedSettingsForm( array $filter_settings );

}
