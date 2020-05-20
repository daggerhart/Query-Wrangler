<?php

namespace QueryWrangler\Handler\Paging\ItemType;

use QueryWrangler\Handler\Paging\PagingInterface;

class Number implements PagingInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'number';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Number of Items', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Set the number of rows this query should display per page. Use -1 to display all results.', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function queryTypes() {
		return [ 'post', 'user', 'term', 'comment' ];
	}

	/**
	 * @inheritDoc
	 */
	public function process( array $args, array $values ) {
		$args['number'] = 5;

		if ( isset( $values['posts_per_page'] ) ) {
			$args['number'] = intval( $values['posts_per_page'] );
		}
		else if ( isset( $values['number'] ) ) {
			$args['number'] = intval( $values['number'] );
		}
		return $args;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $item ) {
		// TODO: Implement settingsForm() method.
	}
}
