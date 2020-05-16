<?php

namespace QueryWrangler\Admin\MetaBox;

use Kinglet\Admin\MetaBoxBase;
use Kinglet\Form\Form;

class Preview extends MetaBoxBase {

	/**
	 * @var \Kinglet\Storage\OptionRepository
	 */
	protected $settings;

	/**
	 * Preview constructor.
	 *
	 * @param string|string[] $post_types
	 * @param \Kinglet\Storage\OptionRepository $settings
	 */
	public function __construct( $post_types, $settings ) {
		parent::__construct( $post_types );
		$this->settings = $settings;
	}

	/**
	 * {@inheritdoc}
	 */
	public function id() {
		return 'qw-preview';
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
		$new = empty( $post->post_title );
		?>
		Preview goes here.
		<?php
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( $post_id, $post, $updated ) {

	}

	/**
	 * @return \Kinglet\Form\Form
	 */
	public function form() {
		return Form::create( [
			'form_element' => FALSE,
			'form_prefix' => 'qw-preview',
		] );
	}

}
