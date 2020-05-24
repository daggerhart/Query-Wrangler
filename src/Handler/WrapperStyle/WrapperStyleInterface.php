<?php

namespace QueryWrangler\Handler\WrapperStyle;

use Kinglet\Template\RendererInterface;
use QueryWrangler\Handler\HandlerItemTypeInterface;
use QueryWrangler\Query\QueryPostEntity;

interface WrapperStyleInterface extends HandlerItemTypeInterface {

	/**
	 * Attach the file rendering service to the Row Style.
	 *
	 * @param RendererInterface $renderer
	 */
	public function setFileRenderer( RendererInterface $renderer );

	/**
	 * @return string
	 */
	public function settingsForm();

	/**
	 * Entry point into the rendering of a query row for the template style.
	 *
	 * @param QueryPostEntity $qw_query
	 * @param array $settings
	 * @param array $context
	 *
	 * @return string
	 */
	public function render( QueryPostEntity $qw_query, array $settings, array $context );

}
