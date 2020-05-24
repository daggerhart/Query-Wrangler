<?php
/*
 * overrides Hook
 */
function qw_all_overrides() {
	$overrides = apply_filters( 'qw_overrides', array() );

	foreach ( $overrides as $type => $override ) {
		// set override's type as a value if not provided by override
		if ( empty( $override['type'] ) ) {
			$overrides[ $type ]['type'] = $type;
		}

		// maintain the hook's key
		$overrides[ $type ]['hook_key'] = $type;
	}

	// sort them by title
	$titles = array();
	foreach ( $overrides as $key => $override ) {
		$titles[ $key ] = $override['title'];
	}
	array_multisort( $titles, SORT_ASC, $overrides );

	return $overrides;
}
