<?php

namespace QueryWrangler\Handler\Override\ItemType;

use QueryWrangler\Handler\Override\OverrideInterface;
use QueryWrangler\QueryPostEntity;
use QueryWrangler\QueryPostType;
use WP_Query;

class PagePath implements OverrideInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( '', 'query-wrangler' );
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
	public function queryTypes() {
		return [ 'post' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getOverride( WP_Query $wp_query ) {
		if ( $wp_query->is_singular() ) {
			$path = trim( $_SERVER['REQUEST_URI'], '/' );
			$path = explode( '/page/', $path )[0];
			$posts = get_posts( [
				'post_type' => QueryPostType::SLUG,
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'meta_query' => [
					$this->type() => [
						// @todo - implement this meta_key
						'key' => 'query_page_path',
						'value' => $path,
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
		$post = $entity->object();
		$tmp_query = new \WP_Query( [
			'post__in' => [ $post->ID ],
			'post_type' => [ $post->post_type ],
		] );
		$wp_query->query_vars = $tmp_query->query_vars;
	}

}
