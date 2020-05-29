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
	public function process( array $query_args, array $filter_settings ) {
		$query_args['post_status'] = [ 'publish' ];

		if ( isset( $filter_settings['value'] ) ) {
			$query_args['post_status'] = (array) $filter_settings['value'];
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
