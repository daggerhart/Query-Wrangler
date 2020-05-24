<?php

/*
 * @todo - everything except exposed filters is done
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
				// @todo -
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

/**
 * Get a query's id by that is set to override a specific term_id
 *
 * @param $term_id
 *
 * @return bool
 */
function qw_get_query_by_override_term( $term_id ) {

	global $wpdb;
	$qw_table  = $wpdb->prefix . "query_wrangler";
	$qot_table = $wpdb->prefix . "query_override_terms";

	$sql = "SELECT qw.id FROM " . $qw_table . " as qw
              LEFT JOIN " . $qot_table . " as ot ON ot.query_id = qw.id
              WHERE qw.type = 'override' AND ot.term_id = %d
              LIMIT 1";

	$row = $wpdb->get_row( $wpdb->prepare( $sql, $term_id ) );

	if ( $row ) {
		return $row->id;
	}

	return FALSE;
}

/*
 * Support function for legacy, pre hook_keys discovery
 */
function qw_get_hook_key( $all, $single ) {
	// default to new custom_field (meta_value_new)
	$hook_key = 'custom_field';

	// see if hook key is set
	if ( ! empty( $single['hook_key'] ) && isset( $all[ $single['hook_key'] ] ) ) {
		$hook_key = $single['hook_key'];
	} // look for type as key
	else if ( ! empty( $single['type'] ) ) {
		foreach ( $all as $key => $item ) {
			if ( $single['type'] == $item['type'] ) {
				$hook_key = $item['hook_key'];
				break;
			} else if ( $single['type'] == $key ) {
				$hook_key = $key;
				break;
			}
		}
	}

	return $hook_key;
}

/*
 * Generate form prefixes for handlers
 *
 * @param string
 *    $type = sort, field, filter, override
 */
function qw_make_form_prefix( $type, $name ) {
	$handlers = qw_all_handlers();

	if ( isset( $handlers[ $type ]['form_prefix'] ) ) {
		$output = QW_FORM_PREFIX . $handlers[ $type ]['form_prefix'] . '[' . $name . ']';
	} else {
		$output = QW_FORM_PREFIX . "[" . $name . "]";
	}

	return $output;
}

/*
 * Replace contextual tokens within a string
 *
 * @params string $args - a query argument string
 *
 * @return string - query argument string with tokens replaced with values
 */
function qw_contextual_tokens_replace( $args ) {
	$matches = array();
	preg_match_all( '/{{([^}]*)}}/', $args, $matches );
	if ( isset( $matches[1] ) ) {
		global $post;

		foreach ( $matches[1] as $i => $context_token ) {
			if ( stripos( $context_token, ':' ) !== FALSE ) {
				$a = explode( ':', $context_token );
				if ( $a[0] == 'post' && isset( $post->{$a[1]} ) ) {
					$args = str_replace( $matches[0][ $i ],
						$post->{$a[1]},
						$args );
				} else if ( $a[0] == 'query_var' && $replace = get_query_var( $a[1] ) ) {
					$args = str_replace( $matches[0][ $i ], $replace, $args );
				}
			}
		}
	}

	return $args;
}
