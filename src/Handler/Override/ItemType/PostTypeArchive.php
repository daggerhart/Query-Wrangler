<?php

namespace QueryWrangler\Handler\Override\ItemType;

use QueryWrangler\Handler\Override\OverrideContextInterface;
use QueryWrangler\Handler\Override\OverrideTypeBase;
use QueryWrangler\QueryPostEntity;
use QueryWrangler\QueryPostType;
use WP_Query;

class PostTypeArchive extends OverrideTypeBase {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'post_type_archive';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Post Type Archive', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Override the archive page for the given post types.', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function findOverrideEntity( WP_Query $wp_query ) {
		if ( $wp_query->is_post_type_archive() ) {
			$posts = get_posts( [
				'post_type' => QueryPostType::SLUG,
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'meta_query' => [
					$this->metaKey() => [
						// @todo - implement this meta_key
						'key' => $this->metaKey(),
						'value' => $wp_query->query_vars['post_type'],
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
		$query_vars = $override_context->getOriginalQueryVars();

		$query_args['post_type'] = [ $query_vars['post_type'] ];

		return $query_args;
	}

}
