<?php

namespace QueryWrangler\Handler\TemplateStyle;

use Kinglet\Entity\QueryInterface;
use Kinglet\Template\RendererInterface;
use QueryWrangler\QueryPostEntity;

class LegacyTemplateStyle implements TemplateStyleInterface {

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
		return [ 'post' ];
	}

	/**
	 * @inheritDoc
	 */
	public function setFileRenderer( RendererInterface $renderer ) {
		$this->fileRenderer = $renderer;
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
	public function render( QueryPostEntity $query_post_entity, QueryInterface $entity_query, array $settings, array $rows ) {
		$templates = [
			"{$this->registration['template']}-{$query_post_entity->slug()}",
			"{$this->registration['template']}",
		];
		$context = [
			'template' => 'query-' . $this->type(),
			'slug' => $query_post_entity->slug(),
			'style' => $this->type(),
			'rows' => $rows,
		];
		return $this->fileRenderer->render( $templates, $context );
	}

}
