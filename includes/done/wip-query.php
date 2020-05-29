<?php

/*
 * @todo - DONE - except custom hooks
 * Primary function for building and displaying a query
 *
 * @param int $query_id Id for the query
 * @param array $options_override an array for changing or adding query data options
 * @param bool $reset_post_data Reset the $wp_query after execution
 * @return string Can return a string of html based on parameter $return
 */
function qw_execute_query( $query_id, $options_override = array(), $reset_post_data = TRUE ) {
	// get the query options
	$options = qw_generate_query_options( $query_id, $options_override );

	// get formatted query arguments
	$args = qw_generate_query_args( $options );

	// pre_query hook
	$args = apply_filters( 'qw_pre_query', $args, $options );

	// set the new query
	$qw_query = new WP_Query( $args );

	// pre_render hook
	$options = apply_filters( 'qw_pre_render', $options );

	// get the themed content
	$themed = qw_template_query( $qw_query, $options );

	// Reset Post Data
	if ( $reset_post_data ) {
		wp_reset_postdata();
	}

	return $themed;
}

/*
 * @todo - DONE
 *
 * Generate the WP query itself
 *
 * @param array $options Query data
 * @return array Query Arguments
 */
function qw_generate_query_args( $options = array() ) {
	$handlers = qw_preprocess_handlers( $options );

	// standard arguments
	// @todo - DONE as new Filter types
	$args['post_status']         = $options['args']['post_status'];
	$args['ignore_sticky_posts'] = isset( $options['args']['ignore_sticky_posts'] ) ? $options['args']['ignore_sticky_posts'] : 0;

	// @todo - DONE as new Paging type
	$args['posts_per_page']      = ( $options['args']['posts_per_page'] ) ? $options['args']['posts_per_page'] : 5;

	// @todo - DONE as new Paging type
	// Handle normal pagination vs offset pagination.
	$paged = NULL;
	// if pager_key is enabled, trick qw_get_page_number
	if ( isset( $options['display']['page']['pager']['use_pager_key'] ) &&
	     isset( $options['display']['page']['pager']['pager_key'] ) &&
	     isset( $_GET[ $options['display']['page']['pager']['pager_key'] ] ) &&
	     is_numeric( $_GET[ $options['display']['page']['pager']['pager_key'] ] )
	) {
		$paged = $_GET[ $options['display']['page']['pager']['pager_key'] ];
	}

	$args['paged']               = ( $paged ) ? $paged : qw_get_page_number();
	$args['offset']              = ( $options['args']['offset'] ) ? $options['args']['offset'] : 0;
	if ( $args['paged'] > 1 ) {
		if ( $args['offset'] > 0 && $args['posts_per_page'] > 0 ) {
			// Create offset pagination ourselves.
			$args['offset'] = $args['offset'] + (($args['paged'] - 1) * $args['posts_per_page']);
		}
		else {
			// WP_Query ignores 'paged' if 'offset' is provided.
			// Having any offset will break pagination.
			unset( $args['offset'] );
		}
	}

	// @todo -
	$submitted_data = qw_exposed_submitted_data();

	foreach ( $handlers as $handler_type => $handler ) {
		if ( is_array( $handler['items'] ) ) {
			foreach ( $handler['items'] as $name => $item ) {
				// Exposed items
				// @todo - DONE
				if ( isset( $item['exposed_form'] ) ) {
					if ( ! empty( $item['values']['exposed_key'] ) ) {
						// override exposed key
						$item['exposed_key'] = $item['values']['exposed_key'];
					} else {
						// default exposed key
						$item['exposed_key'] = 'exposed_' . $item['values']['name'];
					}
				}
				// */

				// @todo - DONE
				// Alter the query args
				// look for callback, and run it
				if ( isset( $item['query_args_callback'] ) && function_exists( $item['query_args_callback'] ) ) {
					$item['query_args_callback']( $args, $item );
				} else if ( isset( $item['orderby_key'] ) && isset( $item['order_key'] ) ) {
					// else, default to type as WP_Query argument key
					// arguments passed to query
					$args[ $item['orderby_key'] ] = $item['type'];
					$args[ $item['order_key'] ]   = isset( $item['values']['order_value'] ) ? $item['values']['order_value'] : 'ASC';
				}

				// @todo - DONE
				// Process submitted exposed values
				// exposed items
				if ( isset( $item['values']['is_exposed'], $submitted_data[ $item['exposed_key'] ] ) && function_exists( $item['exposed_process'] ) ) {
					$value = $submitted_data[ $item['exposed_key'] ];
					$item['exposed_process']( $args, $item, $value );
				}
				//*/
			}
		}
	}

	return $args;
}

/*
 * @todo - DONE
 * Get the Query, and set $options to defaults
 *
 * @param int
 *   $query_id - The unique ID of the query
 * @param array
 *   $options_override - An array of values to override in the retrieved set
 * @param array
 *   $full_override - force the options_override as all options
 *
 * @return array
 *   Query options
 */
function qw_generate_query_options( $query_id, $options_override = array(), $full_override = FALSE ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "query_wrangler";
	$sql        = "SELECT id,name,type,slug,data FROM " . $table_name . " WHERE id = %d";

	$rows = $wpdb->get_results( $wpdb->prepare( $sql, $query_id ) );

	if ( empty( $rows ) ) {
		return array();
	}

	// unserialize the stored data
	$options = qw_unserialize( $rows[0]->data );

	// override options
	if ( $full_override ) {
		// force a full override
		$options = $options_override;
	} else {
		// combine options
		$options = array_replace_recursive( (array) $options, $options_override );
	}

	// build query_details
	$options['meta']               = array();
	$options['meta']['id']         = $rows[0]->id;
	$options['meta']['slug']       = $rows[0]->slug;
	$options['meta']['name']       = $rows[0]->name;
	$options['meta']['type']       = $rows[0]->type;
	$options['meta']['pagination'] = ( isset( $options['display']['page']['pager']['active'] ) ) ? 1 : 0;
	$options['meta']['header']     = $options['display']['header'];
	$options['meta']['footer']     = $options['display']['footer'];
	$options['meta']['empty']      = $options['display']['empty'];

	return $options;
}

/*
 * @todo - DONE
 * Helper function: Get the current page number
 * @param object $qw_query - the query being displayed
 *
 * @return
 *    int - the currentpage number
 */
function qw_get_page_number( $qw_query = NULL ) {
	// help figure out the current page
	$path_array = explode( '/page/', $_SERVER['REQUEST_URI'] );

	// look for WP paging first
	if ( ! is_null( $qw_query ) && isset( $qw_query->query_vars['paged'] ) ) {
		$page = $qw_query->query_vars['paged'];
	} // try wordpress method
	else if ( ! is_null( $qw_query ) && get_query_var( 'paged' ) ) {
		$page = get_query_var( 'paged' );
	} // paging with slashes
	else if ( isset( $path_array[1] ) ) {
		$page = explode( '/', $path_array[1] );
		$page = $page[0];
	} // paging with get variable
	else if ( isset( $_GET['page'] ) ) {
		$page = $_GET['page'];
	} // paging with a different get variable
	else if ( isset( $_GET['paged'] ) ) {
		$page = $_GET['paged'];
	} else {
		$page = 1;
	}

	return $page;
}

/*
 * @todo - SKIPPED - unused
 * Get full term data
 *
 * @param $term
 *   - either term id or term slug based on 2nd parameter
 * @param $by
 *   - either 'id' or 'slug': what you want the get term by
 * @param $return_type
 *  - OBJECT, ARRAY_A, ARRAY_N
 *
 * @return
 *  - term, format depending on 3rd parameter
 *  - false if not found
 */
function qw_get_term( $term, $by = 'id', $return_type = OBJECT ) {
	global $wpdb;
	switch ( $by ) {
		case 'id':
			$where = 't.term_id = %d';
			break;

		case 'slug':
			$where = 't.slug = %s';
			break;
	}

	$sql = "SELECT
            t.term_id,t.name,t.slug,t.term_group,tax.taxonomy,tax.description,tax.parent,tax.count
          FROM {$wpdb->terms} as t
          LEFT JOIN {$wpdb->term_taxonomy} as tax ON t.term_id = tax.term_id
          WHERE {$where}";

	$term = $wpdb->get_row( $wpdb->prepare($sql, $term), $return_type );

	if ( $term->term_id ) {
		// http://web.archiveorange.com/archive/v/XZYvyS8D7kDM3sQgrvJF
		$term->link = get_term_link( (int) $term->term_id, $term->taxonomy );

		// return term if found
		return $term;
	}
	else {
		return FALSE;
	}
}

/*
 * @todo - DONE - refactored to maybe_serialize()
 * Serialize wrapper functions for future changes.
 */
function qw_serialize( $array ) {
	return serialize( $array );
}

/*
 * @todo - DONE - refactored away
 * Trim each item in an array w/ array_walk
 *   eg: array_walk($fruit, 'qw_trim');
 */
function qw_trim( &$value ) {
	$value = trim( $value );
}

/*
 * @todo - DONE - compatibilty
 * Get an existing query as QW_Query object
 *
 * @param $id
 *
 * @return null|QW_Query
 */
function qw_get_query( $id ) {
	if ( ! empty( $id ) ) {
		$query = new QW_Query( $id );

		if ( $query && is_a( $query, 'QW_Query' ) && ! $query->is_new ) {
			return $query;
		}
	}

	return NULL;
}

/*
 * @todo - DONE - compatibilty
 * Get a query's id by using its slug
 */
function qw_get_query_by_slug( $slug ) {
	global $wpdb;

	return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . $wpdb->prefix . "query_wrangler WHERE `slug` = '%s'",
		$slug ) );
}


/*
 * @todo - SKIPPED
 * Get an unserialized query row from the database, using the query's id
 *
 * @param $id
 *
 * @return bool|mixed
 */
function qw_get_query_by_id( $id ) {
	global $wpdb;
	$table = $wpdb->prefix . 'query_wrangler';
	$sql   = "SELECT * FROM $table WHERE id = %d LIMIT 1";
	$query = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );

	if ( $query ) {
		$query->data = qw_unserialize( $query->data );

		return $query;
	}

	return FALSE;
}

/*
 * @todo - DONE - compatibility
 * Get all queries of the type widget
 *
 * @return array of query widgets with key as query id
 */
function qw_get_all_widgets() {
	global $wpdb;
	$table_name = $wpdb->prefix . "query_wrangler";
	$sql        = "SELECT id,name FROM " . $table_name . " WHERE type = 'widget'";
	$rows       = $wpdb->get_results( $sql );

	if ( is_array( $rows ) ) {
		$widgets = array();
		foreach ( $rows as $row ) {
			$widgets[ $row->id ] = $row->name;
		}

		return $widgets;
	}
}

/*
 * @todo - DONE - refactored away
 * usort callback for comparing items with a property named 'weight'
 *
 * @param $a
 * @param $b
 *
 * @return int
 */
function qw_cmp( $a, $b ) {
	if ( $a['weight'] == $b['weight'] ) {
		return 0;
	}

	return ( $a['weight'] < $b['weight'] ) ? - 1 : 1;
}

/*
 * @todo - SKIPPED - unused
 * Get all query pages
 *
 * @return array Query pages in WP post format
 */
function qw_get_all_pages() {
	global $wpdb;
	$table_name = $wpdb->prefix . "query_wrangler";
	$sql        = "SELECT id,name,path FROM " . $table_name . " WHERE type = 'page'";
	$rows       = $wpdb->get_results( $sql );

	if ( is_array( $rows ) ) {
		$pages    = array();
		$blog_url = get_bloginfo( 'wpurl' );

		$i = 0;
		foreach ( $rows as $row ) {
			$pages[ $i ]             = new stdClass();
			$pages[ $i ]->ID         = $row->id;
			$pages[ $i ]->title      = $row - name;
			$pages[ $i ]->post_title = $row->name;
			$pages[ $i ]->guid       = $blog_url . $row->path;
			$pages[ $i ]->post_type  = 'page';
		}

		return $pages;
	}
}

/*
 * @todo - DONE - compatilibity
 * Function for grabbing meta keys
 *
 * @return array All meta keys in WP
 */
function qw_get_meta_keys() {
	global $wpdb;

	$keys = $wpdb->get_col( "
			SELECT meta_key
			FROM $wpdb->postmeta
			GROUP BY meta_key
			ORDER BY meta_key" );

	return $keys;
}

/*
 * @todo - SKIPPED - unused
 * Create a new empty QW_Query
 */
function qw_create_query() {
	return new QW_Query();
}

