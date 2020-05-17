<?php

namespace QueryWrangler;

use Kinglet\Admin\MetaBoxBase;
use Kinglet\Container\ContainerInterface;
use Kinglet\Repository\OptionRepository;
use QueryWrangler\Admin\MetaBox\QueryDetails;
use QueryWrangler\Admin\MetaBox\QueryEditor;
use QueryWrangler\Admin\MetaBox\QueryPreview;
use QueryWrangler\Admin\Page\Import;
use QueryWrangler\Admin\Page\Settings;
use QueryWrangler\Handler\Display\DisplayTypeManager;
use QueryWrangler\Handler\Field\FieldTypeManager;
use QueryWrangler\Handler\Filter\FilterTypeManager;
use QueryWrangler\Handler\Sort\SortTypeManager;
use QueryWrangler\PostType\Query;
use QueryWrangler\Query\QueryProcessor;

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
		$container->set( 'handler.display.manager', DisplayTypeManager::class );
		$container->set( 'handler.field.manager', FieldTypeManager::class );
		$container->set( 'handler.filter.manager', FilterTypeManager::class );
		$container->set( 'handler.sort.manager', SortTypeManager::class );
		$container->set( 'query.processor', QueryProcessor::class );
		$this->container = $container;

		add_action( 'plugins_loaded', [ $this, 'registerPostTypes' ] );
		add_action( 'admin_init', [ $this, 'registerMetaBoxes' ] );
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
	}

	public function registerPostTypes() {
	    $settings = $this->container->get( 'settings' );
		$query = new Query( $settings );
		$this->postTypes[ $query::SLUG ] = $query;
	}

	public function registerMetaBoxes() {
	    $settings = $this->container->get( 'settings' );
	    $form_factory = $this->container->get( 'form.factory' );
		$details = new QueryDetails( Query::SLUG, $settings, $form_factory );
		$editor = new QueryEditor( Query::SLUG, $settings, $form_factory );
		$preview = new QueryPreview( Query::SLUG, $settings, $form_factory );
		$this->metaboxes[ $details->id() ] = $details;
		$this->metaboxes[ $preview->id() ] = $preview;
		$this->metaboxes[ $editor->id() ] = $editor;
	}

	/**
	 * WordPress admin_menu hook.
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
}
