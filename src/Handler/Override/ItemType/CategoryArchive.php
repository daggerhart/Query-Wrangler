<?php

namespace QueryWrangler\Handler\Override\ItemType;

use QueryWrangler\Handler\Override\OverrideContextInterface;
use QueryWrangler\Handler\Override\OverrideTypeBase;
use QueryWrangler\QueryPostEntity;
use QueryWrangler\QueryPostType;
use WP_Query;
use WP_Term;

class CategoryArchive extends OverrideTypeBase {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'categories';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Category Archive', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Override term archive pages for individual categories.', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function findOverrideEntity( WP_Query $wp_query ) {
		if ( $wp_query->is_archive() && $wp_query->is_category() ) {
			/** @var WP_Term $term */
			$term = $wp_query->get_queried_object();
			$posts = get_posts( [
				'post_type' => QueryPostType::SLUG,
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'meta_query' => [
					'query_override_'. $this->type() => [
						// @todo - implement this meta_key
						'key' => 'query_override_'. $this->type(),
						'value' => $term->term_id,
					],
				],
			] );

			if ( count( $posts ) ) {
				return QueryPostEntity::load( $posts[0] );
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function process( array $query_args, QueryPostEntity $entity, OverrideContextInterface $override_context ): array {
		/** @var WP_Term $term */
		$term = $override_context->getOriginalQueriedObject();
		$entity->setRendered( 'title', single_term_title( '', false ) );

		$query_args['cat'] = $term->term_id;
		return $query_args;
	}

}
