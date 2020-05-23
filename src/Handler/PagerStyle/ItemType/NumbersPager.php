<?php

namespace QueryWrangler\Handler\PagerStyle\ItemType;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Handler\PagerStyle\PagerStyleInterface;

class NumbersPager implements PagerStyleInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'numbers';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Numbers', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Pager with individual numbered pages. Uses paginate_links().', 'query-wrangler' );
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
		$wp_query = $query->query();
		$big = intval( $wp_query->found_posts . '000' );
		$args = [
			'base'    => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, $page_number ),
			'total'   => $wp_query->max_num_pages
		];

		return paginate_links( $args );
	}
}
