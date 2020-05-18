<?php

namespace QueryWrangler\Handler\Display;

class PagerStyle implements DisplayInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'pager';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Pager', 'query-wrangler' );
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
