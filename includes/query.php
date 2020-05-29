<?php

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
