<?php

namespace QueryWrangler\Handler\Filter\ItemType;

use QueryWrangler\Handler\Filter\FilterInterface;

class IgnoreStickyPosts implements FilterInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'ignore_sticky_posts';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Ignore Sticky Posts', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Do not enforce stickiness in the resulting query.', 'query-wrangler' );
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
	public function process( array $query_args, array $filter_settings ) {
		$query_args['ignore_sticky_posts'] = 0;

		if ( isset( $filter_settings['value'] ) ) {
			$query_args['ignore_sticky_posts'] = intval( $filter_settings['ignore_sticky_posts'] );
		}
		return $query_args;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $filter_settings ) {
		// TODO: Implement settingsForm() method.
	}

}
