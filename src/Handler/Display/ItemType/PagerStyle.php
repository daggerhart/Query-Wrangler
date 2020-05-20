<?php

namespace QueryWrangler\Handler\Display\ItemType;

use QueryWrangler\Handler\Display\DisplayInterface;

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
	public function process( array $display, array $query_data = [] ) {
		// 1.x
		if ( !empty( $query_data['page']['pager'] ) ) {
			$pager_settings = $query_data['page']['pager'];
		}
		$display['pager_classes'] = '';
		$display['pager'] = '';
		return $display;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $display, array $values ) {
		// TODO: Implement settingsForm() method.
	}

}
