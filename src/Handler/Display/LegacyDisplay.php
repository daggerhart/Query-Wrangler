<?php

namespace QueryWrangler\Handler\Display;

use Kinglet\Invoker\InvokerInterface;
use Kinglet\Template\RendererInterface;

class LegacyDisplay implements DisplayInterface {

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
	public function displayTypes() {
		return $this->registration['query_display_types'] ?? [];
	}

	/**
	 * @inheritDoc
	 */
	public function order() {
		return $this->registration['weight'] ?? 0;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $display, array $values ) {
		if ( !empty( $this->registration['form_callback'] ) && is_callable( $this->registration['form_callback'] ) ) {
			return $this->renderer->render( $this->registration['form_callback'], [
				$display,
				$values,
			] );
		}
		return '';
	}
}
