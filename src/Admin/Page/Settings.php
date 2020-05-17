<?php

namespace QueryWrangler\Admin\Page;

use Kinglet\Admin\Messenger;
use Kinglet\Admin\PageBase;
use Kinglet\Form\Form;
use Kinglet\Form\FormFactory;
use Kinglet\Registry\OptionRepository;

class Settings extends PageBase {

    /**
     * @var OptionRepository
     */
	protected $settings;

    /**
     * @var FormFactory
     */
	protected $formFactory;

    /**
     * Settings constructor.
     *
     * @param OptionRepository $settings
     * @param FormFactory $form_factory
     * @param Messenger $messenger
     */
	public function __construct( OptionRepository $settings, FormFactory $form_factory, Messenger $messenger  ) {
		$this->settings = $settings;
		$this->formFactory = $form_factory;
		$this->messenger = $messenger;
		parent::__construct();
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Settings', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Query Wrangler settings.', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function slug() {
		return 'qw-settings';
	}

	/**
	 * @inheritDoc
	 */
	public function actions() {
		return [
			'save_settings' => [ $this, 'saveSettings' ],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function parentSlug() {
		return 'edit.php?post_type=qw_query';
	}

	/**
	 * @return Form
	 */
	protected function form() {
		$settings = $this->settings;
		return $this->formFactory->create( [
			'action' => $this->actionPath( 'save_settings' ),
			'form_prefix' => $this->slug(),
			'style' => 'table',
			'fields' => [
				'widget_theme_compat' => [
					'title' => __( 'Widget Theme Compatibility', 'query-wrangler' ),
					'description' => __( 'If you\'re having trouble with the way Query Wrangler Widgets appear in your sidebar, select this option.', 'query-wrangler' ),
					'type' => 'checkbox',
					'value' => $settings->get('widget_theme_compat') ?? 0,
				],
				'live_preview' => [
					'title' => __( 'Live Preview', 'query-wrangler' ),
					'description' => __( 'Default setting for live preview during query editing.', 'query-wrangler' ),
					'type' => 'checkbox',
					'value' => $settings->get('live_preview') ?? 0,
				],
				'show_silent_meta' => [
					'title' => __( 'Show Silent Meta fields', 'query-wrangler' ),
					'description' => __( 'Show custom meta fields that are normally hidden.', 'query-wrangler' ),
					'type' => 'checkbox',
					'value' => $settings->get('show_silent_meta') ?? 0,
				],
				'meta_value_field_handler' => [
					'title' => __( 'Meta Value field handler', 'query-wrangler' ),
					'description' => __( 'Choose the way meta_value fields are handled.', 'query-wrangler' ),
					'type' => 'select',
					'value' => $settings->get('meta_value_field_handler') ?? 0,
					'options' => [
						0 => __( 'Default handler', 'query-wrangler' ),
						1 => __( 'New handler (beta)', 'query-wrangler' ),
					],
				],
				'meta_value_help' => [
					'type' => 'list_items',
					'items' => [
						__( 'Default - each meta_key is treated as a unique field in the UI.', 'query-wrangler' ),
						__( 'New - a generic "Custom field" is available in the UI, and you must provide it the meta key.', 'query-wrangler' ),
					],
				],
				'shortcode_compat' => [
					'title' => __( 'Shortcode compatibility', 'query-wrangler' ),
					'description' => __( 'Changes the shortcode keyword from <code>query</code> to <code>qw_query</code>, to avoid conflicts with other plugins.', 'query-wrangler' ),
					'type' => 'checkbox',
					'value' => $settings->get('shortcode_compat') ?? 0,
				],
				'shortcode_compat_help' => [
					'type' => 'list_items',
					'items' => [
						__( 'Shortcode compatibility Disabled', 'query-wrangler' ) . ' - <code>[query slug="my-test"]</code>',
						__( 'Shortcode compatibility Enabled', 'query-wrangler' ) . ' - <code>[qw_query slug="my-test"]</code>',
					],
				],
				'submit' => [
					'type' => 'submit',
					'value' => __( 'Save Settings', 'query-wrangler' ),
					'class' => [ 'button', 'button-primary' ],
				]
			]
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function content() {
        print $this->form()->render();
	}

	/**
	 * @return array
	 */
	public function saveSettings() {
		$this->validateAction();

		$form = $this->form();
		$submitted = $form->getSubmittedValues();
		foreach ($submitted as $key => $value) {
			if ( $this->settings->has( $key ) ) {
				$this->settings->set( $key, $value );
			}
		}
		$this->settings->save();

		return $this->result( __('Settings saved.', 'query-wrangler' ) );
	}

}
