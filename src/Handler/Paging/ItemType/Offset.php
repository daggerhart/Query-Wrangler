<?php

namespace QueryWrangler\Handler\Paging\ItemType;

use QueryWrangler\Handler\Paging\PagingInterface;

class Offset implements PagingInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'offset';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Offset', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Number of items to skip, or pass over. For example, if this field is 3, the first 3 items will be skipped and not displayed.', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function queryTypes() {
		return [ 'post', 'user', 'term', 'comment' ];
	}

	/**
	 * @inheritDoc
	 */
	public function process( array $args, array $values ) {
		// Handle normal pagination vs offset pagination.
		$paged = NULL;
		// Handle unique pager keys.
		// @todo - this may not be enabled in 1.x and can be removed here or reimplemented.
		if ( isset( $values['pager']['use_pager_key'], $values['pager']['pager_key'], $_GET[ $values['pager']['pager_key'] ] ) && is_numeric( $_GET[ $values['pager']['pager_key'] ] ) ) {
			$paged = $_GET[ $values['pager']['pager_key'] ];
		}

		$args['paged'] = ( $paged ) ? $paged : qw_get_page_number();
		$args['offset'] = $values['offset'] ?? 0;
		if ( $args['paged'] > 1 ) {
			if ( $args['offset'] > 0 && $args['posts_per_page'] > 0 ) {
				// Create offset pagination ourselves.
				$args['offset'] = (int) $args['offset'] + (($args['paged'] - 1) * $args['number']);
			}
			else {
				// WP_Query ignores 'paged' if 'offset' is provided.
				// Having any offset will break pagination.
				unset( $args['offset'] );
			}
		}

		return $args;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $item ) {
		// TODO: Implement settingsForm() method.
	}
}
