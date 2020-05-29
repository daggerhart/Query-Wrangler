<?php

namespace QueryWrangler\Handler\RowStyle\ItemType;

use Kinglet\Entity\QueryInterface;
use Kinglet\Entity\TypeInterface;
use QueryWrangler\Handler\HandlerTypeManagerInterface;
use QueryWrangler\Handler\RowStyle\RowStyleBase;
use QueryWrangler\QueryPostEntity;

class PostRows extends RowStyleBase {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'posts';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Posts', 'query-wrangler' );
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
	public function settingsForm() {
		// TODO: Implement settingsForm() method.
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
	public function render( QueryPostEntity $query_post_entity, QueryInterface $entity_query, array $settings, HandlerTypeManagerInterface $field_type_manager ) {
		$row_style_settings = $query_post_entity->getRowStyle();
		$rows = [];
		$current_post_id = get_the_ID();
		$i = 0;

		$entity_query->execute( function( $item ) use ( $query_post_entity, $row_style_settings, $current_post_id, &$rows, &$i ) {

			// There if no template context because this template uses template tags like the_title().
			$output = $this->fileRenderer->render( [
				"query-{$row_style_settings['size']}-{$query_post_entity->slug()}",
				"query-{$row_style_settings['size']}",
				"query-unformatted",
			] );

			$field_classes = [ 'query-post-wrapper' ];

			// add class for active menu trail
			if ( is_singular() && ( get_the_ID() === $current_post_id ) ) {
				$field_classes[] = 'active-item';
			}

			$rows[ $i ] = [
				'row_classes' => [],
				'fields' => [
					$i => [
						'classes' => implode( " ", $field_classes ),
						'output' => $output,
						'content' => $output,
					]
				],
			];
			$i += 1;
		} );

		$last_row = count( $rows ) -1;
		foreach ( $rows as $i => $row ) {
			$classes = array_merge( $rows[ $i ]['row_classes'], $this->rowClasses( $i, $last_row ) );
			$rows[ $i ]['row_classes'] = implode( ' ', $classes );
		}

		return $rows;
	}

}
