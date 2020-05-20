<?php

namespace QueryWrangler\Handler\Filter\ItemType;

use QueryWrangler\Handler\Filter\FilterInterface;

class PostStatus implements FilterInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'post_status';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Post Status', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'The post status of the items displayed.', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function queryTypes() {
		return [ 'post' ];
	}

	/**
	 * @inheritDoc
	 */
	public function displayTypes() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function exposable() {
		return FALSE;
	}

	/**
	 * @inheritDoc
	 */
	public function process( array $args, array $values ) {
		$args['post_status'] = [ 'publish' ];

		if ( isset( $values['value'] ) ) {
			$args['post_status'] = (array) $values['value'];
		}
		return $args;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $filter ) {
		// TODO: Implement settingsForm() method.
	}

}
