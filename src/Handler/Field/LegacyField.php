<?php

namespace QueryWrangler\Handler\Field;

use Kinglet\Entity\TypeInterface;
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
	public function render( TypeInterface $entity, array $settings, array $tokens = [] ) {
		if ( !empty( $this->registration['output_callback'] ) && is_callable( $this->registration['output_callback'] ) ) {
			$args = [];
			if ( !empty( $this->registration['output_arguments'] ) ) {
				$args = [
					$entity->object(),
					$settings,
					$tokens,
				];
			}

			$rendered = $this->renderer->render( $this->registration['output_callback'], $args );
			$rendered = $this->processAsContent() ?
				apply_filters( 'the_content', $rendered ) :
				do_shortcode( $rendered );

			return $rendered;
		}

		// If no callback, it expects the value to be a property on the object.
		$object = $entity->object();
		return $object->{$settings['type']} ?? '';
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
