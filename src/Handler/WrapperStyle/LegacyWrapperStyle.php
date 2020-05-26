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
		$render_context = $query_post_entity->getRenderContext();
		$render_context->set( 'slug', $query_post_entity->slug() );
		$render_context->set( 'style', $this->type() );
		$render_context->set( 'header', $settings['header'] ?: null );
		$render_context->set( 'footer', $settings['footer'] ?: null );
		$render_context->set( 'title', $settings['title'] );
		$render_context->set( 'empty', $settings['empty'] );
		$render_context->set( 'wrapper_classes', implode( ' ', [
			'query',
			"query-{$query_post_entity->slug()}-wrapper",
			$settings['wrapper_classes'],
		] ) );
		$render_context->set( 'pager_classes', implode( ' ', [
			'query-pager',
			"pager-{$pager_settings['type']}",
		] ) );
		$templates = [
			"query-wrapper-{$query_post_entity->slug()}",
			"query-wrapper",
		];

		if ( empty( $context['content'] ) ) {
			$render_context->set( 'content', "<div class='query-empty'>{$context['empty']}</div>" );
			$render_context->set( 'pager', null );
		}
		return $this->fileRenderer->render( $templates, $render_context->all() );
	}

}
