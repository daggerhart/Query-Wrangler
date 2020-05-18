<?php

namespace QueryWrangler\Display\RowStyle;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Query\QwQuery;

class PostRows implements RowStyleInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'posts';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Posts', 'query-wrangler' );
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
	public function settingsForm() {
		// TODO: Implement settingsForm() method.
	}

	/**
	 * @inheritDoc
	 */
	public function render( QwQuery $qw_query, QueryInterface $entity_query ) {
		// TODO: Implement render() method.
	}

}
