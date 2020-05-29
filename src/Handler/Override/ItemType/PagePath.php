<?php

namespace QueryWrangler\Handler\Override\ItemType;

use QueryWrangler\Handler\Override\OverrideTypeBase;
use QueryWrangler\QueryPostEntity;
use QueryWrangler\QueryPostType;
use WP_Query;

class PagePath extends OverrideTypeBase {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'page_path';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Page Path', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Override the output of the given URL with this query.', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function findOverrideEntity( WP_Query $wp_query ) {
		if ( $wp_query->is_singular() ) {
			$path = trim( $_SERVER['REQUEST_URI'], '/' );
			$path = explode( '/page/', $path )[0];
			$posts = get_posts( [
				'post_type' => QueryPostType::SLUG,
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'meta_query' => [
					$this->metaKey() => [
						// @todo - implement this meta_key
						'key' => $this->metaKey(),
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

}
