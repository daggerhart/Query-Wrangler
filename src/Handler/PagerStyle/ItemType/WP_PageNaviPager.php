<?php

namespace QueryWrangler\Handler\PagerStyle\ItemType;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Handler\PagerStyle\PagerStyleInterface;

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
	public function render( array $settings, QueryInterface $query, int $page_number ) {
		if ( ! function_exists( 'wp_pagenavi' ) ) {
			return '';
		}

		$args = [
			'query' => $query->query(),
			'echo' => false,
		];

		if ( in_array( $query->type(), [ 'post', 'user' ] ) ) {
			$args['type'] = $query->type() .'s';
		}

		return wp_pagenavi( $args );
	}
}
