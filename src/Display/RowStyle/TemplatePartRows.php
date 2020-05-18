<?php

namespace QueryWrangler\Display\RowStyle;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Query\QwQuery;

class TemplatePartRows implements RowStyleInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'template_part';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Template Part', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Use get_template_part() to have a theme template output the query rows.', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm() {
		// TODO: Implement settingsForm() method.
	}

	/**
	 * @inheritDoc
	 */
	public function render( QwQuery $qw_query, QueryInterface $entity_query ) {
		// TODO: Implement render() method.
	}

}
