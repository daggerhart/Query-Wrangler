<?php

namespace QueryWrangler\Handler\WrapperStyle;

use Kinglet\Entity\QueryInterface;
use Kinglet\Template\RendererInterface;
use QueryWrangler\QueryPostEntity;

class LegacyWrapperStyle implements WrapperStyleInterface {

	/**
	 * @var RendererInterface
	 */
	protected $fileRenderer;

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'legacy';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Legacy', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'The wrapper style used by Query Wrangler version 1.x.', 'query-wrangler' );
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
	public function setFileRenderer( RendererInterface $renderer ) {
		$this->fileRenderer = $renderer;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm() {

	}

	/**
	 * @inheritDoc
	 */
	public function render( QueryPostEntity $query_post_entity, QueryInterface $entity_query, array $settings, array $context ) {
		$pager_settings = $query_post_entity->getPagerStyle();
		$templates = [
			"query-wrapper-{$query_post_entity->slug()}",
			"query-wrapper",
		];
		$context += [
			'slug' => $query_post_entity->slug(),
			'style' => $this->type(),
			'header' => $settings['header'] ?: null,
			'footer' => $settings['footer'] ?: null,
			'title' => $settings['title'],
			'empty' => $settings['empty'],
			'wrapper_classes' => implode( ' ', [
				'query',
				"query-{$query_post_entity->slug()}-wrapper",
				$settings['wrapper_classes'],
			] ),
			'pager_classes' => implode( ' ', [
				'query-pager',
				"pager-{$pager_settings['type']}",
			] ),
		];
		if ( empty( $context['content'] ) ) {
			$context['content'] = "<div class='query-empty'>{$context['empty']}</div>";
			$context['pager'] = null;
		}
		return $this->fileRenderer->render( $templates, $context );
	}

}
