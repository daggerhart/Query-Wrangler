<?php

namespace QueryWrangler\Handler\Override\ItemType;

use QueryWrangler\Handler\Override\OverrideInterface;
use QueryWrangler\QueryPostEntity;
use QueryWrangler\QueryPostType;
use WP_Query;
use WP_Term;

class TaxonomyArchive implements OverrideInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'taxonomies';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Taxonomy Archive', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Override term archive pages for an entire taxonomy.', 'query-wrangler' );
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
		if ( $wp_query->is_archive() && ( $wp_query->is_tag() || $wp_query->is_category() || $wp_query->is_tag() ) ) {
			/** @var WP_Term $term */
			$term = $wp_query->get_queried_object();
			$posts = get_posts( [
				'post_type' => QueryPostType::SLUG,
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'meta_query' => [
					$this->type() => [
						// @todo - implement this meta_key
						'key' => 'query_override_'. $this->type(),
						'value' => $term->taxonomy,
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
		// @todo - is this right? probably. override the entire archive page.
		$post = $entity->object();
		$tmp_query = new \WP_Query( [
			'post__in' => [ $post->ID ],
			'post_type' => [ $post->post_type ],
		] );
		$wp_query->query_vars = $tmp_query->query_vars;
	}
}
