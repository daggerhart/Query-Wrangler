<?php

use Kinglet\Container\ContainerInterface;
use QueryWrangler\QueryPostEntity;
use QueryWrangler\Service\WordPressRegistry;

/**
 * @return ContainerInterface
 */
function qw_get_container_service() {
	global $query_wrangler_loader;
	return $query_wrangler_loader->getContainer();
}

/**
 * Get array of public post types. Keyed by slug, value is label.
 *
 * @deprecated Will be removed in 3.x
 *
 * @return string[]
 */
function qw_all_post_types() {
	/** @var WordPressRegistry $wp_registry */
	$wp_registry = qw_get_container_service()->get( 'wp.registry' );
	$post_types = $wp_registry->getPublicPostTypes( 'names' );
	ksort( $post_types );
	return $post_types;
}

/**
 * Get all relevant post statuses.
 *
 * @deprecated Will be removed in 3.x
 *
 * @return string[]
 */
function qw_all_post_statuses() {
	/** @var WordPressRegistry $wp_registry */
	$wp_registry = qw_get_container_service()->get( 'wp.registry' );
	$post_statuses = $wp_registry->getPublicPostStatuses( 'names' );
	ksort($post_statuses );
	return $post_statuses;
}

/**
 * Get array of all post meta keys.
 *
 * @deprecated Will be removed in 3.x
 *
 * @return array
 */
function qw_get_meta_keys() {
	/** @var WordPressRegistry $wp_registry */
	$wp_registry = qw_get_container_service()->get( 'wp.registry' );
	return $wp_registry->getPostMetaKeys();
}

/**
 * Return default template file name. Relative to theme folder.
 *
 * @deprecated Will be removed in 3.x
 * @return string
 */
function qw_default_template_file() {
	return apply_filters( 'qw_default_template_file', 'index.php' );
}

/**
 * Get Query entity by its ID.
 *
 * @deprecated Will be removed in 3.x
 *
 * @param $id
 *
 * @return QueryPostEntity
 */
function qw_get_query( $id = null ) {
	return QueryPostEntity::load( $id );
}

/**
 * Get Query entity by its slug.
 *
 * @deprecated Will be removed in 3.x
 *
 * @param $slug
 *
 * @return false|QueryPostEntity
 */
function qw_get_query_by_slug( $slug ) {
	return QueryPostEntity::loadBySlug( $slug );
}

/**
 * Get all queries of the display type widget
 *
 * @return array
 */
function qw_get_all_widgets() {
	$posts = get_posts( [
		'post_type' => QueryWrangler\QueryPostType::SLUG,
		'post_status' => 'any',
		'number' => -1,
// @todo - implement this meta value
		'meta_key' => 'query_display_type',
		'meta_value' => 'widget',
		'fields' => 'ids',
	] );

	return array_map( function( $id ) {
		return QueryPostEntity::load( $id );
	}, $posts );
}

/**
 * Fix unserialize problem with quotation marks
 *
 * @param string $serial_str
 *
 * @return array
 */
function qw_unserialize( $serial_str ) {
	$data = maybe_unserialize( $serial_str );

	// if the string failed to unserialize, we may have a quotation problem
	if ( !is_array( $data ) ) {
		$serial_str = preg_replace_callback('!s:(\d+):"(.*?)";!s', 'qw_unserialize_extra_fix_callback', $serial_str);
		$data = maybe_unserialize( $serial_str );
	}

	if ( is_array( $data ) ) {
		// stripslashes twice for science
		$data = array_map( 'stripslashes_deep', $data );
		$data = array_map( 'stripslashes_deep', $data );

		return $data;
	}

	// if we're here the data wasn't unserialized properly.
	// return a modified version of the default query to prevent major failures.
	$default = qw_legacy_default_query_data();
	$default['display']['title'] = 'error unserializing query data';
	$default['args']['posts_per_page'] = 1;

	return $default;
}

/**
 * Attempt to fix issues with quotation marks in a serialized string.
 * This is a replacement for the previous preg_replace approach that used the
 * 'e' flag. The 'e' flag was removed in php 7.
 *
 * @param $matches
 *
 * @return string
 */
function qw_unserialize_extra_fix_callback($matches) {
	return 's:' . strlen($matches[2]) . ':"' . $matches[2] . '";';
}

/**
 * Default values for legacy (1.x) query
 *
 * @return array
 *   Default query settings
 */
function qw_legacy_default_query_data() {
	return array(
		'display' => array(
			'title'           => '',
			'style'           => 'unformatted',
			'row_style'       => 'posts',
			'post_settings'   => array(
				'size' => 'complete',
			),
			'header'          => '',
			'footer'          => '',
			'empty'           => '',
			'wrapper-classes' => '',
			'page'            => array(
				'pager' => array(
					'type'     => 'default',
					'previous' => '',
					'next'     => '',
				),
			),
		),
		'args'    => array(
			'posts_per_page' => '5',
			'offset'         => 0,
			'post_status'    => 'publish',
			'filters'        => array(
				'post_types' => array(
					'type'       => 'post_types',
					'hook_key'   => 'post_types',
					'name'       => 'post_types',
					'weight'     => '0',
					'post_types' =>
						array(
							'post' => 'post',
						),
				),
			),
			'sorts'          => array(
				'date' => array(
					'type'        => 'date',
					'hook_key'    => 'post_date',
					'name'        => 'date',
					'weight'      => '0',
					'order_value' => 'DESC',
				),
			),
		),
	);
}
