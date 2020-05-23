<?php
/*
 * Template Wrangler templates
 *
 * @param $templates array Passed from the filter hook from WP
 *
 * @return array All template arrays filtered so far by Wordpress' filter hook
 */
function qw_templates( $templates ) {

	// display queries style wrapper
	$templates['query_display_wrapper'] = array(
		'files'        => array(
			'query-wrapper-[slug].php',
			'query-wrapper.php',
			'templates/query-wrapper.php',
		),
		'default_path' => QW_PLUGIN_DIR,
		'arguments'    => array(
			'slug'    => '',
			'options' => array(),
		)
	);
	// full and field styles
	$templates['query_display_rows'] = array(
		'files'        => array(
			'[template]-[slug].php',
			'[template].php',
			'templates/[template].php',
		),
		'default_path' => QW_PLUGIN_DIR,
		'arguments'    => array(
			'template' => 'query-unformatted',
			'slug'     => 'not-found',
			'style'    => 'unformatted',
			'rows'     => array(),
		)
	);

	return $templates;
}

// tw hook
add_filter( 'tw_templates', 'qw_templates' );

/*
 * Preprocess query_display_rows to allow field styles to define their own default path
 */
function theme_query_display_rows_preprocess( $template ) {
	// make sure we know what style to use
	if ( isset( $template['arguments']['style'] ) ) {
		// get the specific style
		$all_styles = qw_all_styles();

		// set this template's default path to the style's default path
		if ( isset( $all_styles[ $template['arguments']['style'] ] ) ) {
			$style                    = $all_styles[ $template['arguments']['style'] ];
			$template['default_path'] = $style['default_path'];
		}

		//if(isset($all_styles[$template['preprocess_callback']])){
		//  $template['preprocess_callback'] = $all_styles[$template['preprocess_callback']];
		//}
	}

	return $template;
}

/*
 * Preprocess query_display_syle to allow field styles to define their own default path
 */
function theme_query_display_style_preprocess( $template ) {
	$all_styles = qw_all_styles();
	// make sure we know what style to use
	if ( isset( $all_styles[ $template['arguments']['style'] ] ) ) {
		// get the specific style
		$style = $all_styles[ $template['arguments']['style'] ];
		// set this template's default path to the style's default path
		if ( ! empty( $style['default_path'] ) ) {
			$template['default_path'] = $style['default_path'];
		}
	}

	return $template;
}

/*
 * Template the entire query
 *
 * @param object
 *   $qw_query Wordpress query object
 * @param array
 *   $options the query options
 *
 * @return string HTML for themed/templated query
 */
function qw_template_query( &$qw_query, $options ) {
	$results_count                    = count( $qw_query->posts );
	$options['meta']['results_count'] = $results_count;

	// start building theme arguments
	$wrapper_args = array(
		'slug'    => $options['meta']['slug'],
		'options' => $options,
	);

	// look for empty results
	if ( $results_count > 0 ) {
		$all_styles = qw_all_styles();

		$style = $all_styles[ $options['display']['style'] ];

		// setup row template arguments
		$template_args = array(
			'template' => 'query-' . $style['hook_key'],
			'slug'     => $options['meta']['slug'],
			'style'    => $style['hook_key'],
			'options'  => $options,
		);

		if ( isset( $options['display'][ $style['settings_key'] ] ) ) {
			$template_args['style_settings'] = $options['display'][ $style['settings_key'] ];
		}

		// the content of the widget is the result of the query
		if ( $options['display']['row_style'] == "posts" ) {
			$template_args['rows'] = qw_make_posts_rows( $qw_query, $options );
		}
		// setup row template arguments
		else if ( $options['display']['row_style'] == "fields" ) {
			$template_args['rows'] = qw_make_fields_rows( $qw_query, $options );
		}
		// template_part rows
		else if ( $options['display']['row_style'] == "template_part" ) {
			$template_args['rows'] = qw_make_template_part_rows( $qw_query, $options );
		}

		// template the query rows
		$wrapper_args['content'] = theme( 'query_display_rows',
			$template_args );
	} // empty results
	else {
		// no pagination
		$options['meta']['pagination'] = FALSE;
		// show empty text
		$wrapper_args['content'] = '<div class="query-empty">' . $options['meta']['empty'] . '</div>';
	}

	$wrapper_classes                 = array();
	$wrapper_classes[]               = 'query';
	$wrapper_classes[]               = 'query-' . $options['meta']['slug'] . '-wrapper';
	$wrapper_classes[]               = $options['display']['wrapper-classes'];
	$wrapper_args['wrapper_classes'] = implode( " ", $wrapper_classes );

	// header
	if ( $options['meta']['header'] != '' ) {
		$wrapper_args['header'] = $options['meta']['header'];
	}
	// footer
	if ( $options['meta']['footer'] != '' ) {
		$wrapper_args['footer'] = $options['meta']['footer'];
	}

	// pagination
	if ( $options['meta']['pagination'] && isset( $options['display']['page']['pager']['active'] ) ) {
		$pager_classes                 = array();
		$pager_classes[]               = 'query-pager';
		$pager_classes[]               = 'pager-' . $options['display']['page']['pager']['type'];
		$wrapper_args['pager_classes'] = implode( " ", $pager_classes );
		// pager
		$wrapper_args['pager'] = qw_make_pager( $options['display']['page']['pager'],
			$qw_query );
	}

	// exposed filters
	$exposed = qw_generate_exposed_handlers( $options );
	if ( ! empty( $exposed ) ) {
		$wrapper_args['exposed'] = $exposed;
	}

	// template with wrapper
	return theme( 'query_display_wrapper', $wrapper_args );
}
