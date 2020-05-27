<?php

namespace QueryWrangler\EventSubscriber;

use Kinglet\Admin\Messenger;
use Kinglet\Container\ContainerInterface;
use Kinglet\Form\FormFactory;
use Kinglet\Registry\RegistryRepositoryInterface;
use QueryWrangler\Admin\MetaBox\QueryDebug;
use QueryWrangler\Admin\MetaBox\QueryDetails;
use QueryWrangler\Admin\MetaBox\QueryEditor;
use QueryWrangler\Admin\MetaBox\QueryPreview;
use QueryWrangler\Admin\Page\Import;
use QueryWrangler\Admin\Page\Settings;
use QueryWrangler\QueryPostType;

class AdminEventSubscriber {

	/**
	 * @var RegistryRepositoryInterface
	 */
	protected $settings;

	/**
	 * @var FormFactory
	 */
	protected $formFactory;

	/**
	 * @var Messenger
	 */
	protected $messenger;

	/**
	 * AdminEventSubscriber constructor.
	 *
	 * @param RegistryRepositoryInterface $settings
	 * @param FormFactory $form_factory
	 * @param Messenger $messenger
	 */
	private function __construct( RegistryRepositoryInterface $settings, FormFactory $form_factory, Messenger $messenger ) {
		$this->settings = $settings;
		$this->formFactory = $form_factory;
		$this->messenger = $messenger;

		add_action( 'admin_init', [ $this, 'actionAdminInit' ] );
		add_action( 'admin_menu', [ $this, 'actionAdminMenu' ] );
	}

	/**
	 * @param ContainerInterface $container
	 */
	public static function subscribe( ContainerInterface $container ) {
		new static(
			$container->get( 'settings' ),
			$container->get( 'form.factory' ),
			$container->get( 'messenger' )
		);
	}

	/**
	 * Register all plugin admin menu items.
	 */
	public function actionAdminMenu() {
		$import = new Import( $this->formFactory, $this->messenger );
		$import->addToSubMenu( $import->parentSlug() );

		$settingsPage = new Settings( $this->settings, $this->formFactory, $this->messenger );
		$settingsPage->addToSubMenu( $settingsPage->parentSlug() );
	}

	/**
	 * Register all plugin meta boxes.
	 */
	public function actionAdminInit() {
		new QueryDetails( QueryPostType::SLUG, $this->settings, $this->formFactory );
		new QueryEditor( QueryPostType::SLUG, $this->settings, $this->formFactory );
		new QueryPreview( QueryPostType::SLUG, $this->settings, $this->formFactory );

		new QueryDebug( QueryPostType::SLUG );
	}

}
