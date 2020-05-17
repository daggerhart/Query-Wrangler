<?php

namespace QueryWrangler\Handler\Field;

use Kinglet\Invoker\InvokerInterface;
use Kinglet\Template\RendererInterface;

class LegacyField implements FieldInterface {

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $hook_key;

	/**
	 * @var array
	 */
	protected $registration;

	/**
	 * @var InvokerInterface
	 */
	protected $invoker;

	/**
	 * @var RendererInterface
	 */
	protected $renderer;

	/**
	 * LegacyFilter constructor.
	 *
	 * @param string $type
	 * @param array $registration
	 */
	public function __construct( $type, array $registration ) {
		$this->registration = $registration;
		$this->type = !empty( $this->registration['type'] ) ? $this->type = $this->registration['type'] : $type;
		$this->hook_key = $type;
	}

	/**
	 * @param InvokerInterface $invoker
	 */
	public function setInvoker( InvokerInterface $invoker ) {
		$this->invoker = $invoker;
	}

	/**
	 * @param RendererInterface $renderer
	 */
	public function setRenderer( RendererInterface $renderer ) {
		$this->renderer = $renderer;
	}

	/**
	 * @inheritDoc
	 */
	public function type() {
		return $this->type;
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return $this->registration['title'];
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return $this->registration['description'];
	}

	/**
	 * @inheritDoc
	 */
	public function queryTypes() {
		return ['post'];
	}

	/**
	 * @inheritDoc
	 */
	public function processAsContent() {
		return !empty( $this->registration['content_options'] );
	}

	/**
	 * @inheritDoc
	 */
	public function render( array $context ) {
		if ( !empty( $this->registration['output_callback'] ) && is_callable( $this->registration['output_callback'] ) ) {
			return $this->renderer->render( $this->registration['output_callback'], $context );
		}
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $field ) {
		if ( !empty( $this->registration['form_callback'] ) && is_callable( $this->registration['form_callback'] ) ) {
			return $this->renderer->render( $this->registration['form_callback'], [
				'field' => $field,
			] );
		}
		return '';
	}

}
