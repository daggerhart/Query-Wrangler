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
		$render_context->set( 'wrapper_classes', implode( ' ', [
			'query',
			"query-{$query_post_entity->slug()}-wrapper",
			$settings['wrapper_classes'],
		] ) );
		$render_context->set( 'pager_classes', implode( ' ', [
			'query-pager',
			"pager-{$pager_settings['type']}",
		] ) );

		if ( empty( $context['content'] ) ) {
			$render_context->set( 'content', "<div class='query-empty'>{$context['empty']}</div>" );
			$render_context->set( 'pager', null );
		}

		// Don't replace values that have already been set for some wrapper items.
		foreach ( [ 'header', 'footer', 'title', 'empty' ] as $item ) {
			if ( !$render_context->has( $item ) ) {
				$render_context->set( $item, $settings[ $item ] ?: null );
			}
		}

		$templates = [
			"query-wrapper-{$query_post_entity->slug()}",
			"query-wrapper",
		];

		return $this->fileRenderer->render( $templates, $render_context->all() );
	}

}
