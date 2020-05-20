<?php

namespace QueryWrangler\Handler\Display\ItemType;

use QueryWrangler\Handler\Display\DisplayInterface;

class TemplateStyle implements DisplayInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'template_style';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Template Style', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( '', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function order() {
		return 0;
	}

	/**
	 * @inheritDoc
	 */
	public function queryTypes() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function process( array $display, array $query_data = [] ) {
		// 1.x
		$display['style'] = $query_data['style'];
		return $display;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $display, array $values ) {
		// TODO: Implement settingsForm() method.
	}

}
