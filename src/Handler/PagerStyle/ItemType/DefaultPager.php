<?php

namespace QueryWrangler\Handler\PagerStyle\ItemType;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Handler\PagerStyle\PagerStyleInterface;
use QueryWrangler\QueryPostEntity;

class DefaultPager implements PagerStyleInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'default';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Default', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Very simple "Next" and "Previous" style pager.', 'query-wrangler' );
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
		$wp_query = $entity_query->query();
		// help figure out the current page
		$exposed_path_array = explode( '?', $_SERVER['REQUEST_URI'] );
		$path_array  = explode( '/page/', $exposed_path_array[0] );

		$exposed_path = NULL;
		if ( isset( $exposed_path_array[1] ) ) {
			$exposed_path = $exposed_path_array[1];
		}

		$output = '';
		$settings['next'] = ( $settings['next'] ) ? $settings['next'] : __( 'Next Page &raquo;', 'query-wrangler' );
		$settings['previous'] = ( $settings['previous'] ) ? $settings['previous'] : __( '&laquo; Previous Page', 'query-wrangler' );

		$path = rtrim( $path_array[0], '/' );
		$wpurl = get_bloginfo( 'wpurl' );

		// previous link with page number
		if ( $page_number >= 3 ) {
			$url = $wpurl . $path . '/page/' . ( $page_number - 1 );
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}
			$output .= "<div class='query-prevpage'><a href='{$url}'>{$settings['previous']}</a></div>";
		} // previous link with no page number
		else if ( $page_number == 2 ) {
			$url = $wpurl . $path;
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}
			$output .= "<div class='query-prevpage'><a href='{$url}'>{$settings['previous']}</a></div>";
		}

		// next link
		if ( ( $page_number + 1 ) <= $wp_query->max_num_pages ) {
			$url = $wpurl . $path . '/page/' . ( $page_number + 1 );
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}
			$output .= "<div class='query-nextpage'><a href='{$url}'>{$settings['next']}</a></div>";
		}

		return $output;
	}

}
