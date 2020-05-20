<?php

namespace QueryWrangler\Handler\Display\ItemType;

use QueryWrangler\Handler\Display\DisplayInterface;

class WrapperStyle implements DisplayInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'wrapper_style';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Wrapper Style', 'query-wrangler' );
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
		// 1.x data placement
		$items = ['title', 'header', 'footer', 'empty', 'wrapper-classes'];
		foreach ( $items as $item ) {
			if ( isset( $query_data[ $item ] ) ) {
				$display[ $item ] =	$query_data[ $item ];
			}
		}

		return $display;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $display, array $values ) {
		// TODO: Implement settingsForm() method.
	}

}
