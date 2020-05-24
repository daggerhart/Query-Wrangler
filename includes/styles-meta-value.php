<?php

/**
 * default custom_field (meta_value_new) field display handlers
 */
// add default meta value handlers
add_filter( 'qw_meta_value_display_handlers',
	'qw_meta_value_display_handlers_default' );

/*
 * Default meta value handlers
 */
function qw_meta_value_display_handlers_default( $handlers ) {
	$handlers['none'] = array(
		'title'    => '-none-',
		'callback' => 'qw_get_post_meta',
	);
	// advanced custom fields: http://wordpress.org/plugins/advanced-custom-fields/
	if ( function_exists( 'get_field' ) ) {
		$handlers['acf_default'] = array(
			'title'    => 'Advanced Custom Fields: get_field',
			'callback' => 'qw_get_acf_field',
		);
	}
	// cctm: https://wordpress.org/plugins/custom-content-type-manager/
	if ( function_exists( 'get_custom_field' ) ) {
		$handlers['cctm_default'] = array(
			'title'    => 'CCTM: get_custom_field',
			'callback' => 'qw_get_cctm_field',
		);
	}

	return $handlers;
}

/*
 * return simple get_post_meta array
 */
function qw_get_post_meta( $post, $field ) {
	return get_post_meta( $post->ID, $field['meta_key'] );
}

/*
 * Advanced custom field generic handler
 */
function qw_get_acf_field( $post, $field ) {
	$output = '';
	if ( function_exists( 'get_field' ) ) {
		$output = get_field( $field['meta_key'], $post->ID );
	}

	return $output;
}

/*
 * Custom Content Type Manager generic field handler
 */
function qw_get_cctm_field( $post, $field ) {
	$output = '';
	if ( function_exists( 'get_custom_field' ) ) {
		$field_name = $field['meta_key'];
		if ( isset( $field['cctm_chaining'] ) && ! empty( $field['cctm_chaining'] ) ) {
			$field_name = $field_name . $field['cctm_chaining'];
		}

		$output = get_custom_field( $field_name );
	}

	return $output;
}
