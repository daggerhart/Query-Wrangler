<?php

namespace QueryWrangler\Handler\Override\ItemType;

use QueryWrangler\Handler\Override\OverrideInterface;
use QueryWrangler\QueryPostEntity;
use QueryWrangler\QueryPostType;
use WP_Query;
use WP_Term;

class PostTypeArchive implements OverrideInterface {

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
	public function queryTypes() {
		return [ 'post' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getOverride( WP_Query $wp_query ) {
		if ( $wp_query->is_post_type_archive() ) {
			$posts = get_posts( [
				'post_type' => QueryPostType::SLUG,
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'meta_query' => [
					$this->type() => [
						// @todo - implement this meta_key
						'key' => 'query_override_'. $this->type(),
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
	public function doOverride( WP_Query $wp_query, QueryPostEntity $entity ) {
		// @todo - Need to alter the QW Query to filter by the current post type
		$post = $entity->object();
		$tmp_query = new \WP_Query( [
			'post__in' => [ $post->ID ],
			'post_type' => [ $post->post_type ],
		] );
		$wp_query->query_vars = $tmp_query->query_vars;
	}
}
