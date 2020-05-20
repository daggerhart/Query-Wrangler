<?php

namespace QueryWrangler\Handler\RowStyle\ItemType;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Handler\HandlerTypeManagerInterface;
use QueryWrangler\Handler\RowStyle\RowStyleInterface;
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
	public function render( QwQuery $qw_query, QueryInterface $entity_query, HandlerTypeManagerInterface $field_type_manager ) {
		// TODO: Implement render() method.
	}

}
