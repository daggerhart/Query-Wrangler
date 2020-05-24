<?php

namespace QueryWrangler\Admin\MetaBox;

use Kinglet\Admin\MetaBoxBase;
use Kinglet\Form\FormFactory;
use Kinglet\Registry\OptionRepository;
use QueryWrangler\QueryPostEntity;

class QueryEditor extends MetaBoxBase {

	/**
	 * @var OptionRepository
     */
	protected $settings;

	/**
	 * @var QueryPostEntity
	 */
	protected $query;

    /**
     * @var FormFactory
     */
	protected $formFactory;

    /**
     * Preview constructor.
     *
     * @param string|string[] $post_types
     * @param OptionRepository $settings
     * @param FormFactory $form_factory
     */
	public function __construct( $post_types, OptionRepository $settings, FormFactory $form_factory ) {
		parent::__construct( $post_types );
		$this->settings = $settings;
		$this->formFactory = $form_factory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function id() {
		return 'query-editor';
	}

	/**
	 * {@inheritdoc}
	 */
	public function title() {
		return __( 'Editor', 'query-wrangler' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( $post ) {
		$is_new = empty( $post->post_title );
		$this->query = new QueryPostEntity( $post );
		print $this->form()->render();
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( $post_id, $post, $updated ) {
		$this->query = new QueryPostEntity( $post );
		$form = $this->form();
		$submitted = $form->getSubmittedValues();
		foreach ( $form->getFields() as $field_key => $field ) {
			if ( isset( $submitted[ $field_key ] ) ) {
				$this->query->metaUpdate( $field_key, $submitted[ $field_key ] );
			}
		}
	}

    /**
     * @return \Kinglet\Form\Form
     */
	public function form() {
	    $data = $this->query->meta( 'query_data' );
	    if ( $data && is_array( $data ) ) {
		    $data = json_encode( $this->query->meta( 'query_data' ), JSON_PRETTY_PRINT );
        }

		return $this->formFactory->create( [
			'form_element' => FALSE,
			'form_prefix' => $this->id(),
			'fields' => [
				'query_data' => [
					'title' => __( 'Query Data' ),
					'type' => 'code_editor',
					'value' => $data,
                    'editor_settings' => [
                        'type' => 'application/json',
                    ],
				]
			]
		] );
	}

}
