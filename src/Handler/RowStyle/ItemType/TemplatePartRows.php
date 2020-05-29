<?php

namespace QueryWrangler\Handler\RowStyle\ItemType;

use Kinglet\Entity\QueryInterface;
use Kinglet\Entity\TypeInterface;
use QueryWrangler\Handler\HandlerTypeManagerInterface;
use QueryWrangler\Handler\RowStyle\RowStyleBase;
use QueryWrangler\QueryPostEntity;

class TemplatePartRows extends RowStyleBase {

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
	public function queryTypes() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function render( QueryPostEntity $query_post_entity, QueryInterface $entity_query, array $settings, HandlerTypeManagerInterface $field_type_manager ) {
		$rows = [];
		$current_post_id = get_the_ID();
		$i = 0;

		$entity_query->execute( function( $item ) use ( $query_post_entity, $settings, $current_post_id, &$rows, &$i ) {

			ob_start();
				get_template_part( $settings['path'], $settings['name'] );
			$output = ob_get_clean();

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
