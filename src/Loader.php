<?php

namespace QueryWrangler;

use Kinglet\Admin\MetaBoxBase;
use Kinglet\Container\ContainerInterface;
use Kinglet\FileSystem\Finder;
use Kinglet\Registry\OptionRepository;
use Kinglet\Template\FileRenderer;
use Kinglet\Template\StringRenderer;
use QueryWrangler\Admin\MetaBox\QueryDebug;
use QueryWrangler\Admin\MetaBox\QueryDetails;
use QueryWrangler\Admin\MetaBox\QueryEditor;
use QueryWrangler\Admin\MetaBox\QueryPreview;
use QueryWrangler\Admin\Page\Import;
use QueryWrangler\Admin\Page\Settings;
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
use WP_Query;

class Loader {

    /**
     * @var ContainerInterface
     */
	protected $container;

    /**
     * @var array
     */
	protected $postTypes = [];

    /**
     * @var MetaBoxBase[]
     */
	protected $metaboxes = [];

	public function __construct() {
		$this->setupContainer();
		add_action( 'plugins_loaded', [ $this, 'registerPostTypes' ] );
		add_action( 'admin_init', [ $this, 'registerMetaBoxes' ] );
		add_action( 'init', [ $this, 'registerShortcodes' ] );
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
		add_action( 'parse_query', [ $this, 'overrideFind' ] );
		add_action( 'pre_get_posts', [ $this, 'overrideExecute' ], -1000 );
		add_action( 'the_title', [ $this, 'queryTheTitle' ], 100 );
		add_action( 'the_content', [ $this, 'queryTheContent' ],100 );
	}

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
	 * WARNING: This is not the right way to get the container.
	 * For legacy compatibility ONLY.
	 *
	 * @return ContainerInterface
	 */
	public function getContainer() {
		return $this->container;
	}

	/**
	 * Register all plugin post types.
	 */
	public function registerPostTypes() {
	    $settings = $this->container->get( 'settings' );
		$query = new QueryPostType( $settings );
		$this->postTypes[ $query::SLUG ] = $query;
	}

	/**
	 * Register all plugin shortcodes.
	 */
	public function registerShortcodes() {
		$settings = $this->container->get( 'settings' );
		$query_shortcode = $this->container->get( 'query.shortcode' );
		$tag = $settings->get('shortcode_compat') ? 'qw_query' : 'query';
		add_shortcode( $tag, [ $query_shortcode, 'doShortcode' ] );
	}

	/**
	 * Register all plugin admin menu items.
	 */
	public function adminMenu() {
        $settings = $this->container->get( 'settings' );
        $form_factory = $this->container->get( 'form.factory' );
		$messenger = $this->container->get( 'messenger' );

		$import = new Import( $form_factory, $messenger );
		$import->addToSubMenu( $import->parentSlug() );

		$settingsPage = new Settings( $settings, $form_factory, $messenger );
		$settingsPage->addToSubMenu( $settingsPage->parentSlug() );
	}

	/**
	 * Register all plugin meta boxes.
	 */
	public function registerMetaBoxes() {
		$settings = $this->container->get( 'settings' );
		$form_factory = $this->container->get( 'form.factory' );
		$details = new QueryDetails( QueryPostType::SLUG, $settings, $form_factory );
		$editor = new QueryEditor( QueryPostType::SLUG, $settings, $form_factory );
		$preview = new QueryPreview( QueryPostType::SLUG, $settings, $form_factory );
		$this->metaboxes[ $details->id() ] = $details;
		$this->metaboxes[ $preview->id() ] = $preview;
		$this->metaboxes[ $editor->id() ] = $editor;

		new QueryDebug( QueryPostType::SLUG, $this->container );
	}

	/**
	 * Hook into WP and attempt to resolve an override.
	 *
	 * @param WP_Query $wp_query
	 */
	public function overrideFind( WP_Query $wp_query) {
		/** @var OverrideTypeManager $override */
		$override = $this->container->get( 'handler.override.manager' );
		$override->findOverride( $wp_query );
	}

	/**
	 * Hook into WP and potentially perform an override.
	 *
	 * @param WP_Query $wp_query
	 */
	public function overrideExecute( WP_Query $wp_query ) {
		/** @var OverrideTypeManager $override */
		$override = $this->container->get( 'handler.override.manager' );
		$override->executeOverride( $wp_query );
	}

	/**
	 * @todo - This is dumb, do this somewhere else.
	 * @var QueryPostEntity
	 */
	protected $processedFrontEndQuery;

	protected function getProcessedFrontEndQuery() {
		if ( !$this->processedFrontEndQuery ) {
			/** @var QueryProcessor $processor */
			$processor = $this->container->get( 'query.processor' );
			$this->processedFrontEndQuery = QueryPostEntity::load( get_the_ID() );
			try {
				$processor->execute( $this->processedFrontEndQuery );
			}
			catch ( \Exception $exception ) {}
		}

		return $this->processedFrontEndQuery;
	}

	/**
	 * When displaying QueryPostType on the frontend, provide processed
	 * query as the_title() for the post.
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public function queryTheTitle( $title ) {
		if ( !is_admin() && get_post_type() == QueryPostType::SLUG ) {
			$title = $this->getProcessedFrontEndQuery()->getRenderedTitle();
		}

		return $title;
	}

	/**
	 * When displaying QueryPostType on the frontend, provide processed
	 * query as the_content() for the post.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function queryTheContent( $content ) {
		if ( !is_admin() && get_post_type() == QueryPostType::SLUG ) {
			$content = $this->getProcessedFrontEndQuery()->getRenderedContent();
		}

		return $content;
	}

}
