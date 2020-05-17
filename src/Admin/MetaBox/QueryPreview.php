<?php

namespace QueryWrangler\Admin\MetaBox;

use Kinglet\Admin\MetaBoxBase;
use Kinglet\Form\FormFactory;
use Kinglet\Repository\RepositoryInterface;
use QueryWrangler\Query\Query;

class QueryPreview extends MetaBoxBase {

	/**
	 * @var RepositoryInterface
	 */
	protected $settings;

    /**
     * @var Query
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
     * @param RepositoryInterface $settings
     * @param FormFactory $form_factory
     */
    public function __construct( $post_types, RepositoryInterface $settings, FormFactory $form_factory ) {
        parent::__construct( $post_types );
        $this->settings = $settings;
        $this->formFactory = $form_factory;
    }

	/**
	 * {@inheritdoc}
	 */
	public function id() {
		return 'query-preview';
	}

	/**
	 * {@inheritdoc}
	 */
	public function title() {
		return __( 'Preview', 'query-wrangler' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( $post ) {
        $is_new = empty( $post->post_title );
        $this->query = new Query( $post );
		?>
		Preview goes here.
		<?php
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( $post_id, $post, $updated ) {
        $this->query = new Query( $post );
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
		return $this->formFactory->create( [
			'form_element' => FALSE,
			'form_prefix' => $this->id(),
		] );
	}

}
