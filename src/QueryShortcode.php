<?php

namespace QueryWrangler;

use Kinglet\Container\ContainerInjectionInterface;
use Kinglet\Container\ContainerInterface;
use QueryWrangler\Service\QueryProcessor;

class QueryShortcode implements ContainerInjectionInterface {

	/**
	 * @var QueryProcessor
	 */
	protected $processor;

	/**
	 * QueryShortcode constructor.
	 *
	 * @param QueryProcessor $processor
	 */
	public function __construct( QueryProcessor $processor ) {
		$this->processor = $processor;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function create( ContainerInterface $container ) {
		return new static(
			$container->get( 'query.processor' )
		);
	}

	/**
	 * Entry point for executing shortcodes.
	 *
	 * @param array $attributes
	 * @param string $content
	 *
	 * @return string
	 */
	public function doShortcode( $attributes = [], $content = '' ) {
		return $this->doLegacyShortcode( $attributes, $content );
	}

	/**
	 * Do shortcodes like QW 1.x.
	 *
	 * @param array $attributes
	 * @param string $content
	 *
	 * @return string
	 */
	public function doLegacyShortcode( $attributes = [], $content = '' ) {
		/**
		 * Allows for custom attributes to be registered for QW shortcodes.
		 *
		 * @param array
		 */
		$default_attributes = apply_filters( 'qw_shortcode_default_attributes', [
			'id' => '',
			'slug' => '',
		] );
		$attributes = shortcode_atts( $default_attributes, $attributes );

		/**
		 * Allows attributes to be altered after merging with defaults.
		 *
		 * @since 1.4
		 *
		 * @param array $attributes
		 * @param array $query_data_overrides
		 */
		$attributes = apply_filters( 'qw_shortcode_attributes', $attributes, [] );

		/**
		 * Allows Query options_override to be altered.
		 *
		 * @since 1.4
		 *
		 * @param array $query_data_overrides
		 * @param array $attributes
		 */
		$query_data_overrides = apply_filters( 'qw_shortcode_options', [], $attributes );

		$query_post_entity = false;
		if ( $attributes['id'] ) {
			$query_post_entity = QueryPostEntity::load( $attributes['id'] );
		}
		else if ( $attributes['slug'] ) {
			$query_post_entity = QueryPostEntity::loadBySlug( $attributes['slug'] );
		}

		if ( !$query_post_entity || !$query_post_entity->isLoaded() ) {
			return "<!-- Query Wrangler ERROR: Query not found: {$attributes['id']} - {$attributes['slug']} -->";
		}

		try {
			$output = $this->processor->execute( $query_post_entity, $query_data_overrides );
		}
		catch ( \Exception $exception ) {
			$output = "<!-- Query Wrangler ERROR: {$exception->getMessage()} -->";
		}

		return $output;
	}

}
