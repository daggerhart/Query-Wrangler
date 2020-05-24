<?php

namespace QueryWrangler\Service;

use Kinglet\Registry\Registry;
use WP_Post_Type;

class WordPressRegistry extends Registry {

	/**
	 * [@inheritDoc}
	 */
	public function __construct( array $items = [] ) {
		parent::__construct( $items );
	}

	/**
	 * Get merged array of all public post types.
	 *
	 * @param string $output
	 *
	 * @return string[]|WP_Post_Type[]
	 */
	public function getPublicPostTypes( $output = 'objects' ) {
		if ( $this->has( 'post_types.' . $output ) ) {
			return $this->get( 'post_types.' . $output );
		}

		$post_types = get_post_types( [ 'public'   => true, '_builtin' => true ], $output, 'and' );
		$post_types+= get_post_types( [ 'public'   => true, '_builtin' => false ], $output, 'and' );

		$this->set(  'post_types.' . $output , $post_types );
		return $post_types;
	}

	/**
	 * Get merged array of all public post statuses.
	 *
	 * @param string $output
	 *
	 * @return string[]|\stdClass[]
	 */
	public function getPublicPostStatuses( $output = 'objects' ) {
		if ( $this->has( 'post_statuses.' . $output ) ) {
			return $this->get( 'post_statuses.' . $output );
		}

		$post_stati = get_post_stati( [ 'show_in_admin_status_list' => true ], $output, 'and' );

		$this->set(  'post_statuses.' . $output , $post_stati );
		return $post_stati;
	}

	/**
	 * Get list of all distinct post meta keys.
	 *
	 * @param bool $include_silent
	 *
	 * @return array
	 */
	public function getPostMetaKeys( $include_silent = true ) {
		global $wpdb;

		if ( $include_silent ) {
			return $wpdb->get_col( "SELECT DISTINCT(meta_key) FROM {$wpdb->postmeta} ORDER BY meta_key" );
		}

		return $wpdb->get_col( "SELECT DISTINCT(meta_key) FROM {$wpdb->postmeta} WHERE meta_key NOT LIKE '\_%' ORDER BY meta_key" );
	}

}
