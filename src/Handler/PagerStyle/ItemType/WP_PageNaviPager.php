<?php

namespace QueryWrangler\Handler\PagerStyle\ItemType;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Handler\PagerStyle\PagerStyleInterface;
use QueryWrangler\QueryPostEntity;

class WP_PageNaviPager implements PagerStyleInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'pagenavi';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'WP PageNavi', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Use WP PageNavi plugin to render the pager.', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function queryTypes() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function render( QueryPostEntity $query_post_entity, QueryInterface $entity_query, array $settings, int $page_number ) {
		if ( ! function_exists( 'wp_pagenavi' ) ) {
			return '';
		}

		$args = [
			'query' => $entity_query->query(),
			'echo' => false,
		];

		if ( in_array( $entity_query->type(), [ 'post', 'user' ] ) ) {
			$args['type'] = $entity_query->type() . 's';
		}

		return wp_pagenavi( $args );
	}
}
