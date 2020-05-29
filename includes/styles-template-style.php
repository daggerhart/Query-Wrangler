<?php

// add default template styles to the hook
add_filter( 'qw_styles', 'qw_template_styles_default' );

/*
 * Styles with settings
 */
function qw_basic_settings_style( $basics ) {
	$basics['style'] = array(
		'title'         => 'Template Style',
		'option_type'   => 'display',
		'description'   => 'How should this query be styled?',
		'form_callback' => 'qw_basic_display_style_form',
		'weight'        => 0,
	);

	return $basics;
}

/*
 * All Field Styles and settings
 *
 * @return array Field Styles
 */
function qw_template_styles_default( $styles ) {
	$styles['unformatted']    = array(
		'title'        => 'Unformatted',
		'template'     => 'query-unformatted',
		'default_path' => QW_PLUGIN_DIR, // do not include last slash
	);
	$styles['unordered_list'] = array(
		'title'        => 'Unordered List',
		'template'     => 'query-unordered_list',
		'default_path' => QW_PLUGIN_DIR, // do not include last slash
	);
	$styles['ordered_list']   = array(
		'title'        => 'Ordered List',
		'template'     => 'query-ordered_list',
		'default_path' => QW_PLUGIN_DIR, // do not include last slash
	);
	$styles['table']          = array(
		'title'        => 'Table',
		'template'     => 'query-table',
		'default_path' => QW_PLUGIN_DIR, // do not include last slash
	);

	return $styles;
}
