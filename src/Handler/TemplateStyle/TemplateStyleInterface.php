<?php

namespace QueryWrangler\Handler\TemplateStyle;

use Kinglet\Entity\QueryInterface;
use Kinglet\Template\RendererInterface;
use QueryWrangler\Handler\HandlerItemTypeInterface;
use QueryWrangler\QueryPostEntity;

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
	 * @param QueryPostEntity $query_post_entity
	 * @param QueryInterface $entity_query
	 * @param array $settings
	 * @param array $rows
	 *
	 * @return string
	 */
	public function render( QueryPostEntity $query_post_entity, QueryInterface $entity_query, array $settings, array $rows );

}
