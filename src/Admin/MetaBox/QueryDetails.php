<?php

namespace QueryWrangler\Admin\MetaBox;

use Kinglet\Admin\MetaBoxBase;
use Kinglet\Form\FormFactory;
use Kinglet\Registry\RegistryRepositoryInterface;
use QueryWrangler\Query\QwQuery;

class QueryDetails extends MetaBoxBase {

	/**
	 * @var RegistryRepositoryInterface
	 */
	protected $settings;

	/**
	 * @var QwQuery
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
     * @param RegistryRepositoryInterface $settings
     * @param FormFactory $form_factory
     */
    public function __construct( $post_types, RegistryRepositoryInterface $settings, FormFactory $form_factory ) {
        parent::__construct( $post_types );
        $this->settings = $settings;
        $this->formFactory = $form_factory;
    }

	/**
	 * {@inheritdoc}
	 */
	public function id() {
		return 'query-details';
	}

	/**
	 * {@inheritdoc}
	 */
	public function title() {
		return __( 'Details', 'query-wrangler' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( $post ) {
        $is_new = empty( $post->post_title );
        $this->query = new QwQuery( $post );
        $this->d($this->query);
        ?>
		<code><?php echo $this->query->slug(); ?></code>
		<?php
	}

}
