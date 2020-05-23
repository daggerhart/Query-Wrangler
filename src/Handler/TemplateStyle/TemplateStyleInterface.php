<?php

namespace QueryWrangler\Handler\TemplateStyle;

use Kinglet\Template\RendererInterface;
use QueryWrangler\Handler\HandlerItemTypeInterface;
use QueryWrangler\Query\QwQuery;

interface TemplateStyleInterface extends HandlerItemTypeInterface {

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
	 * @param QwQuery $qw_query
	 * @param array $rows
	 *
	 * @return string
	 */
	public function render( QwQuery $qw_query, array $rows );

}
