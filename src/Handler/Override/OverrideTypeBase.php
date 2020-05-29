<?php

namespace QueryWrangler\Handler\Override;

use QueryWrangler\QueryPostEntity;
use WP_Query;

abstract class OverrideTypeBase implements OverrideInterface {

	/**
	 * {@inheritDoc}
	 */
	public function queryTypes() {
		return [ 'post' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function overrideWPQuery( WP_Query $wp_query, OverrideContextInterface $override_context ) {
		$post = $override_context->getFoundEntity()->object();
		$tmp_query = new \WP_Query( [
			'post__in' => [ $post->ID ],
			'post_type' => [ $post->post_type ],
		] );
		$wp_query->query_vars = $tmp_query->query_vars;
	}

	/**
	 * @inheritDoc
	 */
	public function process( array $query_args, QueryPostEntity $entity, OverrideContextInterface $override_context ): array {}

}
