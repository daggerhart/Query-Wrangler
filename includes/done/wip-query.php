<?php

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
