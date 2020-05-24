<?php

namespace QueryWrangler;

use Kinglet\Registry\OptionRepository;

class QueryPostType {

	const SLUG = 'qw_query';

	static protected $initialized = FALSE;

    /**
     * @var OptionRepository
     */
	protected $settings;

    /**
     * QWQuery constructor.
     *
     * @param OptionRepository $settings
     */
	public function __construct( OptionRepository $settings ) {
	    $this->settings = $settings;

		if ( !self::$initialized ) {
			self::$initialized = TRUE;
			add_action( 'init', [ $this, 'registerPostType' ] );

			add_action( 'parse_query', function( $wp_query ) {
				$wp_query->query_wrangler_override = false;
				if ( $wp_query->is_main_query() && !empty( $_SERVER['REQUEST_URI'] ) ) {
					$post = $this->getQueryByPagePath( $_SERVER['REQUEST_URI'] );
					if ( $post ) {
						$wp_query->query_wrangler_override = true;
						$wp_query->query_wrangler_override_query_post = $post;
					}
				}
			} );
			add_action( 'pre_get_posts', function( $wp_query ) {
				if ( $wp_query->is_main_query() && $wp_query->query_wrangler_override ) {
					$tmp_query = new \WP_Query( [
						'post__in' => [ $wp_query->query_wrangler_override_query_post->ID ],
						'post_type' => [ $wp_query->query_wrangler_override_query_post->post_type ],
					] );
					$wp_query->query_vars = $tmp_query->query_vars;
				}
			}, -1000 );
		}
	}

	/**
	 * @param string $path
	 *
	 * @return bool|\WP_Post
	 */
	public function getQueryByPagePath( $path ) {
		$path = trim( $path, '/' );
		$path = explode( '/page/', $path )[0];
		$posts = get_posts( [
			'post_type' => self::SLUG,
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'meta_query' => [
				'page_path' => [
					// @todo - implement this meta_key
					'key' => 'query_page_path',
					'value' => $path,
				],
			],
		] );

		return !empty( $posts ) ? $posts[0] : false;
	}

	public function labels() {
		return [
			'name' => _x( 'Queries', 'post type general name', 'query-wrangler' ),
			'singular_name' => _x( 'Query', 'post type singular name', 'query-wrangler' ),
			'menu_name' => _x( 'Query Wrangler', 'admin menu', 'query-wrangler' ),
			'name_admin_bar' => _x( 'Query', 'add new on admin bar', 'query-wrangler' ),
			'add_new' => _x( 'Add New', 'query', 'query-wrangler' ),
			'add_new_item' => __( 'Add New Query', 'query-wrangler' ),
			'new_item' => __( 'New Query', 'query-wrangler' ),
			'edit_item' => __( 'Edit Query', 'query-wrangler' ),
			'view_item' => __( 'View Query', 'query-wrangler' ),
			'all_items' => __( 'All Queries', 'query-wrangler' ),
			'search_items' => __( 'Search Queries', 'query-wrangler' ),
			'parent_item_colon' => __( 'Parent Queries:', 'query-wrangler' ),
			'not_found' => __( 'No queries found.', 'query-wrangler' ),
			'not_found_in_trash' => __( 'No queries found in Trash.', 'query-wrangler' ),
		];
	}

	public function config() {
		return [
			'labels' => $this->labels(),
			'description' => __( 'Description.', 'query-wrangler' ),
			'public' => TRUE,
			'exclude_from_search' => TRUE,
			'publicly_queryable' => FALSE,
			'show_ui' => TRUE,
			'show_in_menu' => TRUE,
			'query_var' => FALSE,
			'rewrite' => FALSE,
			'capability_type' => 'page',
			'has_archive' => FALSE,
			'hierarchical' => FALSE,
			'menu_position' => NULL,
			'supports' => [
				'title',
				'custom-fields',
			],
		];
	}

	public function registerPostType() {
		register_post_type( self::SLUG, $this->config() );
	}

}
