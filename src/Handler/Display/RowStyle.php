<?php

namespace QueryWrangler\Handler\Display;

class RowStyle implements DisplayInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'row_style';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Row Style', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( '', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function order() {
		return 0;
	}

	/**
	 * @inheritDoc
	 */
	public function queryTypes() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $display, array $values ) {
		// TODO: Implement settingsForm() method.
	}

}
