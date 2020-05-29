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
	protected $callableRenderer;

	/**
	 * @var RendererInterface
	 */
	protected $fileRenderer;

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

		// Need to record all of this to the registration array for the sake of passing into
		// legacy callbacks.
		$this->registration['type'] = $this->type;
		$this->registration['hook_key'] = $this->type;
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
	public function setFileRenderer( RendererInterface $renderer ) {
		$this->fileRenderer = $renderer;
	}

	/**
	 * @param RendererInterface $renderer
	 */
	public function setCallableRenderer( RendererInterface $renderer ) {
		$this->callableRenderer = $renderer;
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
	public function process( array $query_args, array $filter_settings ) {
		if ( empty( $filter_settings['values'] ) && isset( $filter_settings[ $filter_settings['type'] ] ) ) {
			$filter_settings['values'][ $filter_settings['type'] ] = $filter_settings[ $filter_settings['type'] ];
		}
		if ( $this->isLegacyBasic() ) {
			/*
			 * @todo - need to make sure to set $filter['values'], or change this.
			 * @todo - was hard-coded in QW 1.x - qw_generate_query_args()
			 */
			$query_args[ $this->type() ] = $filter_settings['values'][ $this->type() ];
		}

		if ( !empty( $this->registration['query_args_callback'] ) && is_callable( $this->registration['query_args_callback'] ) ) {
			call_user_func_array( $this->registration['query_args_callback'], [
				'args' => &$query_args,
				'filter' => $filter_settings,
			] );
		}

		// @todo - all this exposed stuff needs testing
		if ( $this->exposable() && $this->isExposed( $filter_settings ) ) {
			$submitted_values = $this->exposedGetSubmittedValues( $filter_settings );

			if ( !empty( $submitted_values ) ) {
				$query_args = $this->exposedProcess( $query_args, $filter_settings, $submitted_values );
			}
		}

		return $query_args;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $filter_settings ) {
		if ( !empty( $this->registration['form_callback'] ) && is_callable( $this->registration['form_callback'] ) ) {
			return $this->callableRenderer->render( $this->registration['form_callback'], [
				'filter' => $filter_settings,
			] );
		}
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function isExposed( array $filter_settings ) {
		// @todo - based on old way. need to test.
		return !!$filter_settings['values']['is_exposed'];
	}

	/**
	 * @inheritDoc
	 */
	public function exposedForm( array $filter_settings, array $form_values ) {
		if ( !empty( $this->registration['exposed_form'] ) && is_callable( $this->registration['exposed_form'] ) ) {
			$exposed_form = $this->callableRenderer->render( $this->registration['exposed_form'], [
				'filter' => $filter_settings,
				'values' => $form_values,
			] );

			$submitted = $this->exposedGetSubmittedValues( $filter_settings );

			return $this->fileRenderer->render( [ 'exposed-handler-wrapper' ], [
				'item' => $filter_settings,
				'values' => !empty( $submitted ) ? $submitted : $form_values,
				'exposed_form' => $exposed_form,
			] );
		}
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function exposedGetSubmittedValues( array $filter_settings ) {
		$exposed_key = $filter_settings['values']['exposed_key'] ?? 'exposed_' . $filter_settings['values']['name'];

		if ( !isset( $_REQUEST[ $exposed_key ] ) ) {
			return [];
		}

		$submitted = $_REQUEST[ $exposed_key ];
		if ( is_array( $submitted ) ) {
			array_walk_recursive( $submitted, 'sanitize_text_field' );
		}
		else {
			$submitted = sanitize_text_field( urldecode( $submitted ) );
		}

		return $submitted;
	}

	/**
	 * @inheritDoc
	 */
	public function exposedProcess( array $query_args, array $filter_settings, array $form_values ) {
		if ( !empty( $this->registration['exposed_process'] ) && is_callable( $this->registration['exposed_process'] ) ) {
			return $this->invoker->call( $this->registration['exposed_process'], [
				'args' => $query_args,
				'filter' => $this->registration,
				'values' => $form_values,
			] );
		}
		return $query_args;
	}

	/**
	 * @inheritDoc
	 */
	public function exposedSettingsForm( array $filter_settings ) {
		if ( !empty( $this->registration['exposed_settings_form'] ) && is_callable( $this->registration['exposed_settings_form'] ) ) {
			return $this->callableRenderer->render( $this->registration['exposed_settings_form'], [
				'filter' => $filter_settings,
			] );
		}
		return '';
	}
}
