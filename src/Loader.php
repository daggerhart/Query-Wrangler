<?php

namespace QueryWrangler;

use Kinglet\Container\ContainerInterface;
use Kinglet\FileSystem\Finder;
use Kinglet\Registry\OptionRepository;
use Kinglet\Template\FileRenderer;
use Kinglet\Template\StringRenderer;
use QueryWrangler\EventSubscriber\AdminEventSubscriber;
use QueryWrangler\EventSubscriber\QueryPostTypeEventSubscriber;
use QueryWrangler\EventSubscriber\OverrideWPQueryEventSubscriber;
use QueryWrangler\Handler\Field\FieldTypeManager;
use QueryWrangler\Handler\Filter\FilterTypeManager;
use QueryWrangler\Handler\HandlerManager;
use QueryWrangler\Handler\Override\OverrideTypeManager;
use QueryWrangler\Handler\PagerStyle\PagerStyleTypeManager;
use QueryWrangler\Handler\Paging\PagingTypeManager;
use QueryWrangler\Handler\RowStyle\RowStyleTypeManager;
use QueryWrangler\Handler\Sort\SortTypeManager;
use QueryWrangler\Handler\TemplateStyle\TemplateStyleTypeManager;
use QueryWrangler\Handler\WrapperStyle\WrapperStyleTypeManager;
use QueryWrangler\Service\QueryProcessor;
use QueryWrangler\Service\WordPressRegistry;

class Loader {

    /**
     * @var ContainerInterface
     */
	protected $container;

	public function __construct() {
		add_action( 'plugins_loaded', function() { $this->setupContainer(); }, -1000 );
		add_action( 'plugins_loaded', function() { $this->setupEventSubscribers(); } );
		add_action( 'init', function() { $this->registerShortcodes(); } );
	}

	/**
	 * Setup a DI container for the app.
	 */
	protected function setupContainer() {
		$container = \Kinglet\Loader::createContainer();
		$container->set( 'settings', function() {
			return new OptionRepository( 'qw_settings', [
				'widget_theme_compat' => 0,
				'live_preview' => 0,
				'show_silent_meta' => 0,
				'meta_value_field_handler' => 0,
				'shortcode_compat' => 0,
			] );
		} );
		$container->set( 'wp.registry', WordPressRegistry::class );

		$container->set( 'handler.field.manager', FieldTypeManager::class );
		$container->set( 'handler.filter.manager', FilterTypeManager::class );
		$container->set( 'handler.sort.manager', SortTypeManager::class );
		$container->set( 'handler.paging.manager', PagingTypeManager::class );
		$container->set( 'handler.row_style.manager', RowStyleTypeManager::class );
		$container->set( 'handler.pager_style.manager', PagerStyleTypeManager::class );
		$container->set( 'handler.template_style.manager', TemplateStyleTypeManager::class );
		$container->set( 'handler.wrapper_style.manager', WrapperStyleTypeManager::class );
		$container->set( 'handler.override.manager', OverrideTypeManager::class );
		$container->set( 'handler.manager', function ( ContainerInterface $container ) {
			// Setup the renderers before they are injected into other services.
			/** @var FileRenderer $fileRenderer */
			$fileRenderer = $container->get( 'renderer.file' );
			$fileRenderer->setOptions( [
				'paths' => [
					QW_PLUGIN_DIR . '/templates',
					QW_PLUGIN_DIR . '/templates/legacy',
				],
			] );
			$fileRenderer->setFinder( new Finder() );

			/** @var StringRenderer $stringRenderer */
			$stringRenderer = $container->get( 'renderer.string' );
			$stringRenderer->setOptions( [
				'prefix' => '{{',
				'suffix' => '}}',
			] );

			$field = $container->get( 'handler.field.manager' );
			$filter = $container->get( 'handler.filter.manager' );
			$sort = $container->get( 'handler.sort.manager' );
			$paging = $container->get( 'handler.paging.manager' );
			$row_style = $container->get( 'handler.row_style.manager' );
			$pager_style = $container->get( 'handler.pager_style.manager' );
			$template_style = $container->get( 'handler.template_style.manager' );
			$wrapper_style = $container->get( 'handler.wrapper_style.manager' );
			$override = $container->get( 'handler.override.manager' );

			return new HandlerManager( [
				$field->type() => $field,
				$filter->type() => $filter,
				$sort->type() => $sort,
				$paging->type() => $paging,
				$row_style->type() => $row_style,
				$pager_style->type() => $pager_style,
				$template_style->type() => $template_style,
				$wrapper_style->type() => $wrapper_style,
				$override->type() => $override,
			] );
		} );
		$container->set( 'query.processor', QueryProcessor::class );
		$container->set( 'query.shortcode', QueryShortcode::class );

		$this->container = $container;
	}

	/**
	 * Hook groups of functionality into WP.
	 */
	protected function setupEventSubscribers() {
		QueryPostTypeEventSubscriber::subscribe( $this->container );
		OverrideWPQueryEventSubscriber::subscribe( $this->container );
		AdminEventSubscriber::subscribe( $this->container );
	}

	/**
	 * Register all plugin shortcodes.
	 */
	protected function registerShortcodes() {
		$settings = $this->container->get( 'settings' );
		$query_shortcode = $this->container->get( 'query.shortcode' );
		$tag = $settings->get('shortcode_compat') ? 'qw_query' : 'query';
		add_shortcode( $tag, [ $query_shortcode, 'doShortcode' ] );
	}

	/**
	 * WARNING: This is not the right way to get the container.
	 * For legacy compatibility ONLY.
	 *
	 * @return ContainerInterface
	 */
	public function getContainer() {
		return $this->container;
	}

}
