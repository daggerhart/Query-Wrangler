<?php

namespace QueryWrangler\Handler\Filter;

use Kinglet\Invoker\InvokerInterface;
use Kinglet\Template\RendererInterface;

class LegacyFilter implements FilterInterface, FilterExposableInterface {

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
	public function exposable() {
		return !empty( $this->registration['exposed_form'] ) && is_callable( $this->registration['exposed_form'] );
	}

	/**
	 * Whether or not this Filter previous a "basic" in older version of QW.
	 *
	 * @return bool
	 */
	public function isLegacyBasic() {
		return isset( $this->registration['option_type'] );
	}

	/**
	 * @inheritDoc
	 */
	public function process( array $args, array $values ) {
		if ( empty( $values['values'] ) && isset( $values[ $values['type'] ] ) ) {
			$values['values'][ $values['type'] ] = $values[ $values['type'] ];
		}
		if ( $this->isLegacyBasic() ) {
			/*
			 * @todo - need to make sure to set $filter['values'], or change this.
			 * @todo - was hard-coded in QW 1.x - qw_generate_query_args()
			 */
			$args[ $this->type() ] = $values['values'][ $this->type() ];
		}

		if ( !empty( $this->registration['query_args_callback'] ) && is_callable( $this->registration['query_args_callback'] ) ) {
			call_user_func_array( $this->registration['query_args_callback'], [
				'args' => &$args,
				'filter' => $values,
			] );
		}

		return $args;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $filter ) {
		if ( !empty( $this->registration['form_callback'] ) && is_callable( $this->registration['form_callback'] ) ) {
			return $this->renderer->render( $this->registration['form_callback'], [
				'filter' => $filter,
			] );
		}
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function exposedForm( array $filter, array $values ) {
		if ( !empty( $this->registration['exposed_form'] ) && is_callable( $this->registration['exposed_form'] ) ) {
			return $this->renderer->render( $this->registration['exposed_form'], [
				'filter' => $filter,
				'values' => $values,
			] );
		}
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function exposedProcessValues( array $values ) {
		return $values;
	}

	/**
	 * @inheritDoc
	 */
	public function exposedProcess( array $args, array $filter, array $values ) {
		if ( !empty( $this->registration['exposed_process'] ) && is_callable( $this->registration['exposed_process'] ) ) {
			return $this->invoker->call( $this->registration['exposed_process'], [
				'args' => $args,
				'filter' => $filter,
				'values' => $values,
			] );
		}
		return $args;
	}

	/**
	 * @inheritDoc
	 */
	public function exposedSettingsForm( array $filter ) {
		if ( !empty( $this->registration['exposed_settings_form'] ) && is_callable( $this->registration['exposed_settings_form'] ) ) {
			return $this->renderer->render( $this->registration['exposed_settings_form'], [
				'filter' => $filter,
			] );
		}
		return '';
	}
}
